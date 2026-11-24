/* Shared by the IT browser and the Windows desktop helper's WebView2. */
(function (root) {
    'use strict';
    class TaskFlowTransport {
        constructor(options) {
            this.o = options; this.closed = false; this.pendingCandidates = [];
            this.ticket = options.ticket; this.ws = null; this.channel = null;
            this.frameId = 0; this.parts = null; this.transport = null;
            this.pc = new RTCPeerConnection({iceServers: options.iceServers || []});
            this.pc.onicecandidate = event => { if (event.candidate) this.signal('CANDIDATE', event.candidate.toJSON()); };
            this.pc.ondatachannel = event => this.attach(event.channel);
            this.pc.onconnectionstatechange = () => {
                if (['failed', 'disconnected'].includes(this.pc.connectionState)) this.relay();
                else if (this.pc.connectionState === 'connected' && this.channel?.readyState === 'open') this.ready('p2p');
            };
            this.fallback = setTimeout(() => this.relay(), 8000);
            this.heartbeat = setInterval(() => {
                if (this.ws?.readyState === WebSocket.OPEN && this.ticket) this.ws.send(JSON.stringify({kind:'auth',token:this.ticket}));
                if (this.channel?.readyState === 'open') this.channel.send(JSON.stringify({kind:'ping'}));
            }, 10000);
        }
        signal(type, payload) { Promise.resolve(this.o.onSignal(type,payload)).catch(e => this.fail(e.message)); }
        async offer() {
            this.attach(this.pc.createDataChannel('taskflow', {ordered:true}));
            await this.pc.setLocalDescription(await this.pc.createOffer());
            await this.o.onSignal('OFFER', this.pc.localDescription.toJSON());
        }
        attach(channel) {
            this.channel = channel;
            channel.onopen = () => this.ready('p2p');
            channel.onclose = () => { if (!this.closed) this.relay(); };
            channel.onerror = () => { if (!this.closed) this.relay(); };
            channel.onmessage = event => this.receive(event.data);
        }
        async acceptSignal(type, payload) {
            if (this.closed) return;
            if (type === 'CANDIDATE') {
                if (!this.pc.remoteDescription) this.pendingCandidates.push(payload);
                else await this.pc.addIceCandidate(payload);
                return;
            }
            if (type === 'OFFER' || type === 'ANSWER') {
                await this.pc.setRemoteDescription(payload);
                for (const candidate of this.pendingCandidates.splice(0)) await this.pc.addIceCandidate(candidate);
                if (type === 'OFFER') {
                    await this.pc.setLocalDescription(await this.pc.createAnswer());
                    await this.o.onSignal('ANSWER', this.pc.localDescription.toJSON());
                }
            }
        }
        updateTicket(ticket) { this.ticket = ticket; }
        directReady() { return this.channel?.readyState === 'open' && !['failed','disconnected','closed'].includes(this.pc.connectionState); }
        ready(transport) {
            if (this.closed) return;
            if (transport === 'relay' && this.directReady()) return;
            if (this.transport !== transport) { this.transport = transport; this.o.onReady?.(transport); }
        }
        relay() {
            if (this.closed || this.directReady()) return;
            if (this.ws) { if (this.ws.readyState === WebSocket.OPEN) this.ready('relay'); return; }
            if (!this.ticket) { this.fail('Relay authentication unavailable.'); return; }
            this.ws = new WebSocket(this.o.relayUrl);
            this.ws.binaryType = 'arraybuffer';
            this.ws.onopen = () => this.ws.send(JSON.stringify({kind:'auth',token:this.ticket}));
            this.ws.onmessage = event => {
                if (typeof event.data === 'string') {
                    const msg = JSON.parse(event.data);
                    if (msg.kind === 'ready') { this.ready('relay'); return; }
                    if (msg.kind === 'error') { this.fail(msg.message || 'Relay authentication rejected.'); return; }
                    if (msg.kind === 'peer_left') {
                        if (!this.directReady()) this.fail('The other participant disconnected.');
                        return;
                    }
                    if (msg.kind === 'authenticated') return;
                }
                this.receive(event.data);
            };
            this.ws.onclose = () => { this.ws = null; if (!this.closed && !this.directReady()) this.fail('Relay disconnected.'); };
            this.ws.onerror = () => { if (!this.closed && !this.directReady()) this.fail('Cannot reach the relay server.'); };
        }
        receive(data) {
            if (this.closed) return;
            if (typeof data !== 'string') { if (this.o.role === 'viewer') this.o.onFrame?.(new Blob([data],{type:'image/jpeg'})); return; }
            let msg; try { msg = JSON.parse(data); } catch { return; }
            if (msg.kind === 'ping') return;
            if (msg.kind === 'input' && this.o.role === 'agent') this.o.onInput?.(msg);
            if (msg.kind === 'frame' && this.o.role === 'viewer') {
                if (!Number.isInteger(msg.total) || msg.total < 1 || msg.total > 256 || typeof msg.data !== 'string' || msg.data.length > 12000) return;
                if (!this.parts || this.parts.id !== msg.id) this.parts = {id:msg.id,total:msg.total,data:[]};
                if (msg.part !== this.parts.data.length || msg.total !== this.parts.total) { this.parts = null; return; }
                this.parts.data.push(msg.data);
                if (this.parts.data.length === msg.total) {
                    const bytes = Uint8Array.from(atob(this.parts.data.join('')), c => c.charCodeAt(0));
                    this.parts = null; this.o.onFrame?.(new Blob([bytes],{type:'image/jpeg'}));
                }
            }
        }
        sendInput(input) {
            if (this.closed || this.o.role !== 'viewer') return;
            const payload = JSON.stringify({kind:'input',...input});
            if (this.directReady()) this.channel.send(payload);
            else if (this.transport === 'relay' && this.ws?.readyState === WebSocket.OPEN) this.ws.send(payload);
        }
        sendFrame(base64) {
            if (this.closed || this.o.role !== 'agent' || base64.length > 2700000) return;
            if (this.directReady()) {
                if (this.channel.bufferedAmount > 262144) return;
                const total = Math.ceil(base64.length / 12000), id = ++this.frameId;
                for (let part=0;part<total;part++) this.channel.send(JSON.stringify({kind:'frame',id,part,total,data:base64.slice(part*12000,(part+1)*12000)}));
            } else if (this.transport === 'relay' && this.ws?.readyState === WebSocket.OPEN && this.ws.bufferedAmount < 262144) {
                this.ws.send(Uint8Array.from(atob(base64), c => c.charCodeAt(0)));
            }
        }
        fail(message) { if (this.closed) return; this.close(); this.o.onError?.(message); }
        close() {
            if (this.closed) return;
            this.closed = true; clearTimeout(this.fallback); clearInterval(this.heartbeat);
            this.channel?.close(); this.pc.close(); this.ws?.close(); this.parts = null;
        }
    }
    root.TaskFlowTransport = TaskFlowTransport;
    if (typeof module !== 'undefined') module.exports = TaskFlowTransport;
})(typeof globalThis !== 'undefined' ? globalThis : window);
