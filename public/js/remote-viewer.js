window.TaskFlowViewer = {start(options) {
    const image=document.getElementById('remoteImage'), screen=document.getElementById('remoteScreen'), status=document.getElementById('remoteStatus');
    let transport=null, after=0, stopped=false, objectUrl=null, lastSuccess=Date.now(), lastMove=0;
    const setStatus=(text,active=false)=>{status.textContent=text;status.className=active?'badge bg-danger':'badge bg-warning text-dark';};
    async function api(path,body) {
        const response=await fetch(options.baseUrl+path,{cache:'no-store',headers:{'Accept':'application/json','Content-Type':'application/json'},
            ...(body ? {method:'POST',body:JSON.stringify({...body,_csrf_token:options.csrf})} : {})});
        const data=await response.json();if(!response.ok || !data.success)throw new Error(data.message||'Connection failed');return data;
    }
    function close() {stopped=true;transport?.close(); if(objectUrl)URL.revokeObjectURL(objectUrl); image.removeAttribute('src');}
    async function end() {close();try{await api('/remote-support/end',{session_code:options.code});}catch{} }
    async function poll() {
        if(stopped)return;
        try {
            const data=await api('/remote-support/api/viewer/'+encodeURIComponent(options.code)+'?after_id='+after);lastSuccess=Date.now();
            if(!['WAITING','ACCEPTED','CONNECTED'].includes(data.session.status)){close();setStatus(data.session.status);return;}
            if(data.session.status==='WAITING'){setStatus('Đang chờ người dùng chấp nhận trên máy');return;}
            if(!transport) {
                transport=new TaskFlowTransport({...options.config,role:'viewer',ticket:data.relay_ticket,
                    onSignal:(signal_type,payload)=>api('/remote-support/api/signal',{session_code:options.code,signal_type,payload}),
                    onReady:mode=>setStatus(mode==='p2p'?'Đang kết nối trực tiếp':'Đang kết nối qua relay'),
                    onFrame:blob=>{const url=URL.createObjectURL(blob);if(objectUrl)URL.revokeObjectURL(objectUrl);objectUrl=url;image.src=url;setStatus('ĐANG CHIA SẺ MÀN HÌNH',true);},
                    onError:message=>{setStatus(message);void end();}});
                await transport.offer();
            }
            transport.updateTicket(data.relay_ticket);
            for(const signal of data.signals){await transport.acceptSignal(signal.signal_type,signal.payload);after=Math.max(after,Number(signal.id));}
        } catch(error) {setStatus(error.message);if(Date.now()-lastSuccess>10000){void end();}}
        finally {if(!stopped)setTimeout(poll,1000);}
    }
    function point(event){const r=image.getBoundingClientRect();return{x:Math.max(0,Math.min(1,(event.clientX-r.left)/r.width)),y:Math.max(0,Math.min(1,(event.clientY-r.top)/r.height))};}
    image.addEventListener('pointermove',e=>{if(Date.now()-lastMove<30)return;lastMove=Date.now();transport?.sendInput({action:'move',...point(e)});});
    image.addEventListener('pointerdown',e=>{e.preventDefault();screen.focus();image.setPointerCapture(e.pointerId);transport?.sendInput({action:'down',button:e.button,...point(e)});});
    image.addEventListener('pointerup',e=>{e.preventDefault();transport?.sendInput({action:'up',button:e.button,...point(e)});});
    image.addEventListener('contextmenu',e=>e.preventDefault());
    image.addEventListener('wheel',e=>{e.preventDefault();transport?.sendInput({action:'wheel',delta:e.deltaY>0?-120:120});},{passive:false});
    const pressed=new Set();
    screen.addEventListener('keydown',e=>{e.preventDefault();pressed.add(e.keyCode);transport?.sendInput({action:'keyDown',key:e.keyCode});});
    screen.addEventListener('keyup',e=>{e.preventDefault();pressed.delete(e.keyCode);transport?.sendInput({action:'keyUp',key:e.keyCode});});
    screen.addEventListener('blur',()=>{for(const key of pressed)transport?.sendInput({action:'keyUp',key});pressed.clear();});
    document.getElementById('remoteEnd').onclick=async()=>{await end();setStatus('ENDED');};
    document.getElementById('remoteFullscreen').onclick=()=>screen.requestFullscreen().catch(()=>{});
    window.addEventListener('pagehide',()=>{close();navigator.sendBeacon(options.baseUrl+'/remote-support/end',new Blob([JSON.stringify({session_code:options.code,_csrf_token:options.csrf})],{type:'application/json'}));});
    void poll();
}};
