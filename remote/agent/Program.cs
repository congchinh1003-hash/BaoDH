using System;
using System.IO;
using System.Linq;
using System.Collections.Generic;
using System.Drawing;
using System.Drawing.Imaging;
using System.Net;
using System.Net.Http;
using System.Runtime.InteropServices;
using System.Security.Cryptography;
using System.Security.Principal;
using System.ServiceProcess;
using System.Text;
using System.Threading.Tasks;
using System.Web.Script.Serialization;
using System.Windows.Forms;
using Microsoft.Web.WebView2.Core;
using Microsoft.Web.WebView2.WinForms;
using Microsoft.Win32;

namespace TaskFlowAgent
{
    static class Program
    {
        [STAThread] static int Main(string[] args)
        {
            if (args.Contains("--runtime-check")) {
                try { CoreWebView2Environment.GetAvailableBrowserVersionString(); return 0; } catch { return 2; }
            }
            if (args.Contains("--service")) { ServiceBase.Run(new AgentService()); return 0; }
            if (args.Contains("--self-test")) return SelfTest.Run();
            if (args.Contains("--webview-test")) return SelfTest.WebView();
            Application.EnableVisualStyles(); Application.SetCompatibleTextRenderingDefault(false);
            bool created;
            using (var mutex = new System.Threading.Mutex(true, "Local\\TaskFlowAgentDesktop", out created)) {
                if (!created) return 0;
                try { Application.Run(new DesktopAgent()); }
                catch (Exception e) { LocalStore.Log("agent_error", e.GetType().Name); return 1; }
            }
            return 0;
        }
    }
    static class LocalStore
    {
        public static readonly JavaScriptSerializer Json = new JavaScriptSerializer { MaxJsonLength = 4000000 };
        public static readonly string DirectoryPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "TaskFlowAgent");
        public static Dictionary<string, object> Read(string file)
        {
            var path = Path.Combine(DirectoryPath, file);
            if (!File.Exists(path)) return new Dictionary<string, object>();
            var bytes = ProtectedData.Unprotect(File.ReadAllBytes(path), null, DataProtectionScope.CurrentUser);
            return Json.Deserialize<Dictionary<string, object>>(Encoding.UTF8.GetString(bytes));
        }
        public static Dictionary<string, object> Credentials()
        {
            var path=Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData),"TaskFlowAgent","credentials.dat");
            var bytes=ProtectedData.Unprotect(File.ReadAllBytes(path),null,DataProtectionScope.LocalMachine);
            return Json.Deserialize<Dictionary<string,object>>(Encoding.UTF8.GetString(bytes));
        }
        public static void Write(string file, object data)
        {
            Directory.CreateDirectory(DirectoryPath);
            var target = Path.Combine(DirectoryPath, file);
            var bytes = ProtectedData.Protect(Encoding.UTF8.GetBytes(Json.Serialize(data)), null, DataProtectionScope.CurrentUser);
            File.WriteAllBytes(target + ".tmp", bytes);
            if (File.Exists(target)) File.Replace(target + ".tmp", target, null); else File.Move(target + ".tmp", target);
        }
        public static void Log(string action, string details)
        {
            Directory.CreateDirectory(DirectoryPath);
            File.AppendAllText(Path.Combine(DirectoryPath, "sessions.jsonl"), Json.Serialize(new { utc = DateTime.UtcNow.ToString("o"), action, details }) + Environment.NewLine);
        }
    }
    sealed class DesktopAgent : Form
    {
        readonly WebView2 browser = new WebView2 { Dock = DockStyle.Fill };
        readonly NotifyIcon tray = new NotifyIcon { Visible = true, Icon = SystemIcons.Information, Text = "TaskFlow: waiting for IT support" };
        readonly Timer pollTimer = new Timer { Interval = 1000 }, frameTimer = new Timer { Interval = 250 };
        readonly HttpClient http = new HttpClient { Timeout = TimeSpan.FromSeconds(8) };
        readonly HashSet<string> grants = new HashSet<string>();
        readonly HashSet<string> pendingEnds = new HashSet<string>();
        string url, code, identity; int after, consentGeneration;
        bool pendingRevoke, polling, framing, ready, locked, initialized, stopping;
        DateTime lastSuccess = DateTime.UtcNow, started;
        Form consentDialog;
        public DesktopAgent()
        {
            Text = "TaskFlow Agent"; ShowInTaskbar = false; Width = 1; Height = 1; Opacity = 0;
            Controls.Add(browser);
            var credentials = LocalStore.Credentials();
            if ((string)credentials["windows_sid"]!=WindowsIdentity.GetCurrent().User.Value) throw new InvalidOperationException("Wrong Windows account");
            url = (string)credentials["url"];
            if (new Uri(url).Scheme != "https") throw new InvalidOperationException("HTTPS required");
            http.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", (string)credentials["token"]);
            ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
            var state = LocalStore.Read("consent.dat");
            LoadSet(state, "grants", grants); LoadSet(state, "pendingEnds", pendingEnds);
            pendingRevoke = state.ContainsKey("pendingRevoke") && Convert.ToBoolean(state["pendingRevoke"]);
            var menu = new ContextMenuStrip();
            menu.Items.Add("Disconnect", null, async (s, e) => await Disconnect());
            menu.Items.Add("Revoke access", null, async (s, e) => {
                // Persist local revocation before attempting any network operation.
                consentGeneration++; pendingRevoke = true; grants.Clear(); SaveState(); consentDialog?.Close(); StopSharing();
                await FlushPending();
                tray.ShowBalloonTip(5000, "TaskFlow", "Access revoked. Future connections require your consent.", ToolTipIcon.Info);
            });
            tray.ContextMenuStrip = menu;
            tray.DoubleClick += (s, e) => tray.ShowBalloonTip(5000, "TaskFlow", ready ? "IT is accessing your entire desktop. Use Disconnect or Revoke access." : "Agent is running. First access requires your consent.", ToolTipIcon.Info);
            SystemEvents.SessionSwitch += SessionSwitch;
            pollTimer.Tick += async (s, e) => await Poll();
            frameTimer.Tick += async (s, e) => await Frame();
            Shown += async (s, e) => {
                try {
                    var env = await CoreWebView2Environment.CreateAsync(null, Path.Combine(LocalStore.DirectoryPath, "WebView2"));
                    await browser.EnsureCoreWebView2Async(env);
                    browser.CoreWebView2.Settings.AreDevToolsEnabled = false;
                    browser.CoreWebView2.Settings.AreDefaultContextMenusEnabled = false;
                    browser.CoreWebView2.SetVirtualHostNameToFolderMapping("agent.taskflow.invalid", Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "web"), CoreWebView2HostResourceAccessKind.DenyCors);
                    browser.CoreWebView2.WebMessageReceived += async (sender, args) => await Bridge(args);
                    browser.CoreWebView2.NavigationStarting += (sender,args) => { if (!args.Uri.StartsWith("https://agent.taskflow.invalid/", StringComparison.Ordinal)) args.Cancel = true; };
                    browser.CoreWebView2.Navigate("https://agent.taskflow.invalid/agent.html");
                    pollTimer.Start(); frameTimer.Start();
                } catch (Exception ex) { LocalStore.Log("startup_error", ex.GetType().Name); tray.ShowBalloonTip(5000,"TaskFlow","Agent could not start. Repair the installation.",ToolTipIcon.Error); }
            };
        }
        static void LoadSet(Dictionary<string, object> state, string key, HashSet<string> target)
        {
            if (state.TryGetValue(key, out var values)) foreach (var value in (System.Collections.IEnumerable)values) target.Add(value.ToString());
        }
        void SaveState() { LocalStore.Write("consent.dat", new { grants = grants.ToArray(), pendingEnds = pendingEnds.ToArray(), pendingRevoke }); }
        async Task<Dictionary<string, object>> Api(string path, object body = null)
        {
            HttpResponseMessage response;
            if (body == null) response = await http.GetAsync(url + path);
            else response = await http.PostAsync(url + path, new StringContent(LocalStore.Json.Serialize(body), Encoding.UTF8, "application/json"));
            using (response) {
                var data = LocalStore.Json.Deserialize<Dictionary<string, object>>(await response.Content.ReadAsStringAsync());
                if (!response.IsSuccessStatusCode || !Convert.ToBoolean(data["success"])) throw new InvalidOperationException("Agent request rejected");
                lastSuccess = DateTime.UtcNow; return data;
            }
        }
        async Task Action(string action, string sessionCode = null, object payload = null, string signalType = null, string transport = null)
        {
            await Api("/remote-support/agent/action", new { action, session_code = sessionCode ?? code, payload, signal_type = signalType, transport });
        }
        async Task FlushPending()
        {
            try {
                if (pendingRevoke) { await Action("revoke"); pendingRevoke = false; SaveState(); }
                foreach (var sessionCode in pendingEnds.ToArray()) { await Action("end",sessionCode); pendingEnds.Remove(sessionCode); SaveState(); }
            } catch { /* Local stop remains effective when offline. Retry before processing new requests. */ }
        }
        async Task Poll()
        {
            if (polling || !initialized || stopping) return;
            polling = true;
            try {
                await FlushPending();
                if (pendingRevoke || pendingEnds.Count > 0) return;
                locked=!NativeInput.DesktopAvailable();
                var data = await Api("/remote-support/agent/poll?after_id=" + after);
                var session = data["session"] as Dictionary<string, object>;
                if (session == null || locked) { if (code != null) await Disconnect(); return; }
                var nextCode = (string)session["session_code"];
                if (code != null && code != nextCode) StopSharing();
                if (code == null) {
                    // Never resume an approved session after a helper restart; the old peer is gone.
                    if ((string)session["status"] != "WAITING") { pendingEnds.Add(nextCode); SaveState(); await FlushPending(); return; }
                    int generation=consentGeneration;
                    identity = session["staff_id"] + ":" + session["operator_device"];
                    bool accepted = grants.Contains(identity) && Convert.ToBoolean(session["trusted"]);
                    if (!accepted) accepted = AskConsent((string)session["staff_name"], (string)session["operator_device"]);
                    if (locked || stopping || generation!=consentGeneration) accepted = false;
                    await Action(accepted ? "accept" : "deny", nextCode);
                    if (generation!=consentGeneration) { pendingEnds.Add(nextCode); SaveState(); StopSharing(); await FlushPending(); return; }
                    if (!accepted) { LocalStore.Log("denied", identity); return; }
                    grants.Add(identity); SaveState(); code = nextCode; after = 0;
                    tray.Icon = SystemIcons.Warning; tray.Text = "TaskFlow: IT connection starting";
                    tray.ShowBalloonTip(5000,"Remote support",session["staff_name"] + " is connecting to your entire screen.",ToolTipIcon.Warning);
                    await browser.ExecuteScriptAsync("agentStart(" + LocalStore.Json.Serialize(new { sessionCode=code, config=data["config"] }) + ")");
                    return; // Next poll supplies the ticket minted after consent.
                }
                await browser.ExecuteScriptAsync("agentPoll(" + LocalStore.Json.Serialize(data) + ")");
                if (data["signals"] is System.Collections.IEnumerable signals)
                    foreach (Dictionary<string, object> signal in signals) after = Math.Max(after, Convert.ToInt32(signal["id"]));
            } catch (Exception e) {
                if (code != null && DateTime.UtcNow-lastSuccess>TimeSpan.FromSeconds(10)) await Disconnect();
                LocalStore.Log("network_error", e.GetType().Name);
            } finally { polling = false; }
        }
        bool AskConsent(string name, string device)
        {
            using (var dialog = new Form { Text="TaskFlow — Remote support consent", Width=500, Height=240, TopMost=true,
                StartPosition=FormStartPosition.CenterScreen, FormBorderStyle=FormBorderStyle.FixedDialog, MaximizeBox=false, MinimizeBox=false }) {
                var label = new Label { Left=20,Top=20,Width=450,Height=110,Text=name+" (IT device "+device.Substring(0,8)+") wants to view and control your entire screen.\r\n\r\nAccept remembers this IT account/device for future sessions. You can disconnect or revoke access from the TaskFlow tray icon at any time." };
                var accept = new Button { Text="Accept",Left=260,Top=145,Width=100,DialogResult=DialogResult.Yes };
                var deny = new Button { Text="Deny",Left=370,Top=145,Width=100,DialogResult=DialogResult.No };
                dialog.Controls.AddRange(new Control[]{label,accept,deny}); dialog.CancelButton=deny; dialog.AcceptButton=deny;
                using (var timeout = new Timer { Interval=290000 }) {
                    timeout.Tick += (s,e)=>{dialog.DialogResult=DialogResult.No;dialog.Close();};timeout.Start();
                    consentDialog=dialog;
                    try { return dialog.ShowDialog()==DialogResult.Yes; } finally { consentDialog=null; }
                }
            }
        }
        async Task Bridge(CoreWebView2WebMessageReceivedEventArgs args)
        {
            if (!args.Source.StartsWith("https://agent.taskflow.invalid/", StringComparison.Ordinal)) return;
            try {
                var msg = LocalStore.Json.Deserialize<Dictionary<string, object>>(args.WebMessageAsJson);
                var kind = (string)msg["kind"];
                if (kind == "initialized") { initialized=true; Hide(); return; }
                if (code == null || locked || pendingRevoke || !msg.ContainsKey("sessionCode") || (string)msg["sessionCode"]!=code) return;
                if (kind == "signal") await Action("signal",payload:msg["payload"],signalType:(string)msg["type"]);
                if (kind == "ready") {
                    var readyCode=code;
                    await Action("connected",transport:(string)msg["transport"]);
                    if (code != readyCode || locked || pendingRevoke) return;
                    if (!ready) { started=DateTime.UtcNow; LocalStore.Log("started",code+" "+identity+" "+msg["transport"]); }
                    ready=true;tray.Icon=SystemIcons.Error;tray.Text="TaskFlow: REMOTE SESSION ACTIVE";
                    tray.ShowBalloonTip(5000,"REMOTE SESSION ACTIVE","IT can see and control your entire screen. Disconnect / Revoke access in the tray menu.",ToolTipIcon.Warning);
                }
                if (kind == "input" && ready) NativeInput.Apply(msg);
                if (kind == "error") await Disconnect();
            } catch (Exception e) { LocalStore.Log("bridge_error",e.GetType().Name); await Disconnect(); }
        }
        async Task Frame()
        {
            if (!ready || locked || framing || code == null || pendingRevoke) return;
            framing=true;
            try {
                if (!NativeInput.DesktopAvailable()) { locked=true;await Disconnect();return; }
                if (DateTime.UtcNow-lastSuccess>TimeSpan.FromSeconds(10)) { await Disconnect(); return; }
                var bounds=SystemInformation.VirtualScreen;
                using(var bitmap=new Bitmap(bounds.Width,bounds.Height)) {
                    using(var graphics=Graphics.FromImage(bitmap)) graphics.CopyFromScreen(bounds.Location,Point.Empty,bounds.Size);
                    int width=Math.Min(1920,bounds.Width),height=Math.Max(1,bounds.Height*width/bounds.Width);
                    using(var scaled=new Bitmap(bitmap,new Size(width,height)))
                    using(var stream=new MemoryStream())
                    using(var parameters=new EncoderParameters(1)) {
                        parameters.Param[0]=new EncoderParameter(System.Drawing.Imaging.Encoder.Quality,65L);
                        scaled.Save(stream,ImageCodecInfo.GetImageEncoders().First(c=>c.MimeType=="image/jpeg"),parameters);
                        await browser.ExecuteScriptAsync("agentFrame("+LocalStore.Json.Serialize(Convert.ToBase64String(stream.ToArray()))+")");
                    }
                }
            } catch { await Disconnect(); } finally { framing=false; }
        }
        void StopSharing()
        {
            ready=false;NativeInput.ReleaseAll();
            if (initialized) _ = browser.ExecuteScriptAsync("agentStop()");
            if (code!=null) LocalStore.Log("ended",code+" "+identity+" duration_seconds="+(started==default(DateTime)?0:(DateTime.UtcNow-started).TotalSeconds).ToString("F0"));
            started=default(DateTime);
            code=null;identity=null;after=0;tray.Icon=SystemIcons.Information;tray.Text="TaskFlow: waiting for IT support";
        }
        async Task Disconnect()
        {
            consentGeneration++;
            if (code!=null) { pendingEnds.Add(code); SaveState(); }
            consentDialog?.Close(); StopSharing(); await FlushPending();
        }
        void SessionSwitch(object sender,SessionSwitchEventArgs e)
        {
            if (IsDisposed) return;
            BeginInvoke(new Action(async ()=>{
                if(e.Reason==SessionSwitchReason.SessionLock || e.Reason==SessionSwitchReason.SessionLogoff) {locked=true;await Disconnect();}
                if(e.Reason==SessionSwitchReason.SessionUnlock) locked=false;
            }));
        }
        protected override void OnFormClosing(FormClosingEventArgs e)
        {
            stopping=true;pollTimer.Stop();frameTimer.Stop();
            if(code!=null) {pendingEnds.Add(code);SaveState();}
            StopSharing();SystemEvents.SessionSwitch-=SessionSwitch;tray.Visible=false;tray.Dispose();http.Dispose();base.OnFormClosing(e);
        }
    }
    static class NativeInput
    {
        [DllImport("user32.dll",SetLastError=true)] static extern IntPtr OpenInputDesktop(uint flags,bool inherit,uint access);
        [DllImport("user32.dll",CharSet=CharSet.Unicode,SetLastError=true)] static extern bool GetUserObjectInformation(IntPtr handle,int index,StringBuilder information,uint length,out uint needed);
        [DllImport("user32.dll")] static extern bool CloseDesktop(IntPtr desktop);
        [DllImport("user32.dll")] static extern bool SetCursorPos(int x,int y);
        [DllImport("user32.dll")] static extern void mouse_event(uint flags,uint dx,uint dy,uint data,UIntPtr extra);
        [DllImport("user32.dll")] static extern void keybd_event(byte key,byte scan,uint flags,UIntPtr extra);
        static readonly HashSet<byte> keys=new HashSet<byte>();
        static readonly HashSet<int> buttons=new HashSet<int>();
        public static bool DesktopAvailable()
        {
            var desktop=OpenInputDesktop(0,false,1);
            if(desktop==IntPtr.Zero)return false;
            try {var name=new StringBuilder(256);return GetUserObjectInformation(desktop,2,name,512,out var needed)&&string.Equals(name.ToString(),"Default",StringComparison.OrdinalIgnoreCase);}
            finally {CloseDesktop(desktop);}
        }
        public static void Apply(Dictionary<string,object> msg)
        {
            if(!DesktopAvailable())return;
            string action=(string)msg["action"];
            if(action=="move"||action=="down"||action=="up") {
                double x=Convert.ToDouble(msg["x"]),y=Convert.ToDouble(msg["y"]);
                if(double.IsNaN(x)||double.IsNaN(y)||x<0||x>1||y<0||y>1)return;
                var bounds=SystemInformation.VirtualScreen;
                SetCursorPos(bounds.Left+(int)Math.Round(x*(bounds.Width-1)),bounds.Top+(int)Math.Round(y*(bounds.Height-1)));
                if(action!="move") {
                    int button=Convert.ToInt32(msg["button"]);if(button<0||button>2)return;
                    uint[] down={2,32,8},up={4,64,16};
                    if(action=="down")buttons.Add(button);else buttons.Remove(button);
                    mouse_event(action=="down"?down[button]:up[button],0,0,0,UIntPtr.Zero);
                }
            } else if(action=="wheel") {
                int delta=Convert.ToInt32(msg["delta"]);if(Math.Abs((long)delta)>1200)return;
                mouse_event(2048,0,0,unchecked((uint)delta),UIntPtr.Zero);
            } else if(action=="keyDown"||action=="keyUp") {
                int key=Convert.ToInt32(msg["key"]);if(key<1||key>254)return;
                if(action=="keyDown")keys.Add((byte)key);else keys.Remove((byte)key);
                keybd_event((byte)key,0,action=="keyUp"?2u:0u,UIntPtr.Zero);
            }
        }
        public static void ReleaseAll()
        {
            foreach(var key in keys)keybd_event(key,0,2,UIntPtr.Zero);keys.Clear();
            uint[] up={4,64,16};foreach(var button in buttons)mouse_event(up[button],0,0,0,UIntPtr.Zero);buttons.Clear();
        }
    }
    sealed class AgentService : ServiceBase
    {
        System.Threading.Timer timer; System.Diagnostics.Process child; uint childSession=uint.MaxValue;
        public AgentService() {ServiceName="TaskFlowRemoteAgent";CanStop=true;AutoLog=true;}
        protected override void OnStart(string[] args) {timer=new System.Threading.Timer(_=>Tick(),null,0,5000);}
        void Tick()
        {
            lock(this) {
                try {
                    uint session=DesktopLauncher.WTSGetActiveConsoleSessionId();
                    if(child!=null && (session!=childSession || child.HasExited)) {if(!child.HasExited)child.Kill();child.Dispose();child=null;}
                    if(session==uint.MaxValue||child!=null)return;
                    var owner=File.ReadAllText(Path.Combine(AppDomain.CurrentDomain.BaseDirectory,"owner.sid")).Trim();
                    int pid=DesktopLauncher.Start(session,owner);
                    if(pid!=0){child=System.Diagnostics.Process.GetProcessById(pid);childSession=session;}
                } catch(Exception ex) {EventLog.WriteEntry("TaskFlow helper start failed: "+ex.GetType().Name,System.Diagnostics.EventLogEntryType.Error);}
            }
        }
        protected override void OnStop() {timer?.Dispose();lock(this){if(child!=null&&!child.HasExited)child.Kill();child?.Dispose();child=null;}}
    }
    static class DesktopLauncher
    {
        [DllImport("kernel32.dll")] public static extern uint WTSGetActiveConsoleSessionId();
        [DllImport("wtsapi32.dll",SetLastError=true)] static extern bool WTSQueryUserToken(uint session,out IntPtr token);
        [DllImport("advapi32.dll",SetLastError=true)] static extern bool DuplicateTokenEx(IntPtr token,uint access,IntPtr attrs,int level,int type,out IntPtr primary);
        [DllImport("userenv.dll",SetLastError=true)] static extern bool CreateEnvironmentBlock(out IntPtr environment,IntPtr token,bool inherit);
        [DllImport("userenv.dll")] static extern bool DestroyEnvironmentBlock(IntPtr environment);
        [DllImport("kernel32.dll")] static extern bool CloseHandle(IntPtr handle);
        [DllImport("advapi32.dll",CharSet=CharSet.Unicode,SetLastError=true)] static extern bool CreateProcessAsUser(IntPtr token,string application,StringBuilder command,IntPtr processAttrs,IntPtr threadAttrs,bool inherit,uint flags,IntPtr env,string directory,ref StartupInfo startup,out ProcessInfo process);
        [StructLayout(LayoutKind.Sequential,CharSet=CharSet.Unicode)] struct StartupInfo {public int cb;public string reserved,desktop,title;public int x,y,xSize,ySize,xCount,yCount,fill,flags;public short show,reserved2;public IntPtr reservedPtr,input,output,error;}
        [StructLayout(LayoutKind.Sequential)] struct ProcessInfo {public IntPtr process,thread;public int processId,threadId;}
        public static int Start(uint session,string owner)
        {
            IntPtr token=IntPtr.Zero,primary=IntPtr.Zero,env=IntPtr.Zero;
            try {
                if(!WTSQueryUserToken(session,out token))return 0;
                using(var identity=new WindowsIdentity(token))if(identity.User.Value!=owner)return 0;
                if(!DuplicateTokenEx(token,0x02000000,IntPtr.Zero,2,1,out primary))throw new System.ComponentModel.Win32Exception();
                if(!CreateEnvironmentBlock(out env,primary,false))throw new System.ComponentModel.Win32Exception();
                string exe=Path.Combine(AppDomain.CurrentDomain.BaseDirectory,"TaskFlowAgent.exe");
                var startup=new StartupInfo {cb=Marshal.SizeOf(typeof(StartupInfo)),desktop="winsta0\\default"};
                if(!CreateProcessAsUser(primary,exe,new StringBuilder("\""+exe+"\" --helper"),IntPtr.Zero,IntPtr.Zero,false,0x400,env,AppDomain.CurrentDomain.BaseDirectory,ref startup,out var process))throw new System.ComponentModel.Win32Exception();
                CloseHandle(process.process);CloseHandle(process.thread);return process.processId;
            } finally {if(env!=IntPtr.Zero)DestroyEnvironmentBlock(env);if(primary!=IntPtr.Zero)CloseHandle(primary);if(token!=IntPtr.Zero)CloseHandle(token);}
        }
    }
    static class SelfTest
    {
        public static int WebView()
        {
            Application.EnableVisualStyles();
            bool passed=false;
            using(var form=new Form {Width=1,Height=1,Opacity=0,ShowInTaskbar=false})
            using(var view=new WebView2 {Dock=DockStyle.Fill})
            using(var timeout=new Timer {Interval=20000}) {
                form.Controls.Add(view);timeout.Tick+=(s,e)=>form.Close();timeout.Start();
                form.Shown+=async(s,e)=>{
                    try {
                        var data=Path.Combine(Path.GetTempPath(),"TaskFlow-WebView-Test-"+Guid.NewGuid().ToString("N"));
                        var environment=await CoreWebView2Environment.CreateAsync(null,data);
                        await view.EnsureCoreWebView2Async(environment);
                        view.CoreWebView2.WebMessageReceived+=async(sender,args)=>{
                            if(args.TryGetWebMessageAsString()!="ready")return;
                            form.Hide();
                            passed=await view.ExecuteScriptAsync("40+2")=="42";
                            form.Close();
                        };
                        view.CoreWebView2.NavigateToString("<html><script>chrome.webview.postMessage('ready')</script></html>");
                    } catch {form.Close();}
                };
                Application.Run(form);
            }
            return passed?0:1;
        }
        public static int Run()
        {
            var original=new {grants=new[]{"7:operator-device"},pendingRevoke=true};
            var bytes=ProtectedData.Protect(Encoding.UTF8.GetBytes(LocalStore.Json.Serialize(original)),null,DataProtectionScope.CurrentUser);
            var decoded=LocalStore.Json.Deserialize<Dictionary<string,object>>(Encoding.UTF8.GetString(ProtectedData.Unprotect(bytes,null,DataProtectionScope.CurrentUser)));
            var machine=ProtectedData.Protect(Encoding.UTF8.GetBytes("test-agent-credential"),null,DataProtectionScope.LocalMachine);
            var machineDecoded=Encoding.UTF8.GetString(ProtectedData.Unprotect(machine,null,DataProtectionScope.LocalMachine));
            return machineDecoded=="test-agent-credential" && Convert.ToBoolean(decoded["pendingRevoke"]) && ((System.Collections.IEnumerable)decoded["grants"]).Cast<object>().Single().ToString()=="7:operator-device" ? 0 : 1;
        }
    }
}
