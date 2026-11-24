<?php
namespace App\Controllers;

use App\Core\{Auth,Controller,Request,Response};
use App\Models\{RemoteSession,Ticket,User};
use App\Services\RemoteSecurity;
use DomainException;

class RemoteSupportController extends Controller
{
    private RemoteSession $model;
    public function __construct(?RemoteSession $model = null) { $this->model = $model ?? new RemoteSession(); }

    private function api(Response $response, callable $action): void
    {
        header('Cache-Control: no-store');
        try { $response->json(['success'=>true] + $action()); }
        catch (DomainException $e) { $response->json(['success'=>false,'message'=>$e->getMessage()],403); }
        catch (\RuntimeException $e) { $response->json(['success'=>false,'message'=>$e->getMessage()],503); }
    }
    private function agent(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($header,'Bearer ')) throw new DomainException('Agent authentication required.');
        return $this->model->authenticate(substr($header,7));
    }
    private function authorized(string $code, bool $agent = false): array
    {
        $this->model->expireStale();
        $s = $this->model->findByCode($code);
        if (!$s || !$s['device_id']) throw new DomainException('Session not found.');
        if ($agent) {
            if ((int)$this->agent()['id'] !== (int)$s['device_id']) throw new DomainException('This session belongs to another PC.');
        } elseif (Auth::id() !== (int)$s['client_id'] &&
            !(Auth::isStaff() && Auth::id()===(int)$s['staff_id'] && hash_equals($s['operator_device'],RemoteSecurity::operatorDevice(Auth::id())))) {
            throw new DomainException('Only this user and the requesting IT device can access the session.');
        }
        return $s;
    }
    private function viewer(string $code): array
    {
        $s=$this->authorized($code);
        if (!Auth::isStaff() || Auth::id()!==(int)$s['staff_id'] || !hash_equals($s['operator_device'],RemoteSecurity::operatorDevice(Auth::id()))) throw new DomainException('Requesting IT device required.');
        return $s;
    }
    private function sessionData(array $s, string $role, int $after): array
    {
        $active = in_array($s['status'],['ACCEPTED','CONNECTED'],true) && $this->model->hasGrant($s);
        return ['session'=>$s,'signals'=>$this->model->signals($s,$role==='agent'?'CLIENT':'STAFF',$after),
            'relay_ticket'=>$active ? RemoteSecurity::relayTicket($s,$role) : null];
    }
    public function index(Request $request, Response $response): string
    {
        $staff=Auth::isStaff(); $configurationError=null; $installationError=null;
        try { RemoteSecurity::config(); } catch (\RuntimeException $e) { $configurationError=$e->getMessage(); }
        try { RemoteSecurity::installationConfig(); } catch (\RuntimeException $e) { $installationError=$e->getMessage(); }
        $users=$staff ? (new User())->getAllUsers() : [];
        foreach ($users as &$user) $user['remote_devices']=$this->model->devices((int)$user['id']);
        return $this->render('remote/index',['pageTitle'=>'Hỗ trợ từ xa','isStaff'=>$staff,
            'devices'=>$this->model->devices(Auth::id()),'allUsers'=>$users,
            'sessions'=>$this->model->recent(Auth::id(),$staff),'configurationError'=>$configurationError,'installationError'=>$installationError]);
    }
    public function createQuick(Request $request, Response $response): void
    {
        try {
            if (!Auth::isStaff()) throw new DomainException('IT operator required.');
            RemoteSecurity::config();
            $s=$this->model->createSession(Auth::id(),(int)$request->input('client_id'),null,
                mb_substr((string)$request->input('notes','Hỗ trợ kỹ thuật'),0,2000),RemoteSecurity::operatorDevice(Auth::id()),(int)$request->input('device_id'));
            $response->redirect('/remote-support/session/'.$s['session_code']);
        } catch (\RuntimeException $e) { $this->setFlash('danger',$e->getMessage()); $response->redirect('/remote-support'); }
    }
    public function requestFromTicket(Request $request, Response $response, string $ticketId): void
    {
        try {
            if (!Auth::isStaff()) throw new DomainException('IT operator required.');
            RemoteSecurity::config();
            $ticket=(new Ticket())->findById((int)$ticketId);
            if (!$ticket) throw new DomainException('Ticket not found.');
            $existing=$this->model->findActiveForTicket((int)$ticketId);
            $s=$existing ?: $this->model->createSession(Auth::id(),(int)$ticket['requester_id'],(int)$ticketId,
                $ticket['title'],RemoteSecurity::operatorDevice(Auth::id()));
            $this->viewer($s['session_code']);
            $response->redirect('/remote-support/session/'.$s['session_code']);
        } catch (\RuntimeException $e) { $this->setFlash('danger',$e->getMessage()); $response->redirect('/remote-support'); }
    }
    public function session(Request $request, Response $response, string $code): string
    {
        try {
            $s=$this->authorized($code); $config=RemoteSecurity::config();
            return $this->render('remote/session',['pageTitle'=>'Remote '.$code,'session'=>$s,
                'isViewer'=>Auth::isStaff() && Auth::id()===(int)$s['staff_id'],
                'remoteConfig'=>['relayUrl'=>$config['relay_url'],'iceServers'=>$config['ice_servers']]]);
        } catch (\RuntimeException $e) { $this->setFlash('danger',$e->getMessage()); $response->redirect('/remote-support'); return ''; }
    }
    public function downloadAgent(Request $request, Response $response): void
    {
        try {
            $config=RemoteSecurity::installationConfig();
            $zip=dirname(__DIR__,2).'/public/agent/TaskFlow-Agent.zip';
            if (!is_file($zip)) throw new \RuntimeException('Build the Windows agent package before installing. See docs/REMOTE_SUPPORT.md.');
            $enrollment=$this->model->issueEnrollment(Auth::id());
            $options=base64_encode(json_encode(['url'=>$config['public_url'],'enrollment'=>$enrollment['token'],
                'package_sha256'=>hash_file('sha256',$zip)],JSON_THROW_ON_ERROR));
            $installer=file_get_contents(dirname(__DIR__,2).'/public/agent/Install-TaskFlowAgent.ps1');
            $script="$" . "TaskFlowInstallOptions = '$options'\r\n" . $installer;
            header('Cache-Control: no-store'); header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="Install-TaskFlowAgent.ps1"');
            echo $script;
        } catch (\RuntimeException $e) { $this->setFlash('danger',$e->getMessage()); $response->redirect('/remote-support'); }
    }
    public function enroll(Request $request, Response $response): void
    {
        $this->api($response,function() use($request) {
            $config=RemoteSecurity::installationConfig();
            return $this->model->enroll((string)$request->input('enrollment'),(string)$request->input('name','Windows PC')) +
                ['url'=>$config['public_url']];
        });
    }
    public function package(Request $request, Response $response): void
    {
        $path=dirname(__DIR__,2).'/public/agent/TaskFlow-Agent.zip';
        if (!is_file($path)) { $response->json(['success'=>false,'message'=>'Agent package has not been built.'],503); return; }
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="TaskFlow-Agent.zip"');
        header('Content-Length: '.filesize($path)); readfile($path);
    }
    public function agentPoll(Request $request, Response $response): void
    {
        $this->api($response,function() use($request) {
            $s=$this->model->agentPoll($this->agent()); $config=RemoteSecurity::config();
            return ($s ? $this->sessionData($s,'agent',(int)$request->input('after_id',0)) : ['session'=>null,'signals'=>[]]) +
                ['config'=>['relayUrl'=>$config['relay_url'],'iceServers'=>$config['ice_servers']]];
        });
    }
    public function agentAction(Request $request, Response $response): void
    {
        $this->api($response,function() use($request) {
            $device=$this->agent(); $action=(string)$request->input('action');
            if ($action==='revoke') { $this->model->revoke((int)$device['id'],(int)$device['client_id']); return []; }
            $s=$this->authorized((string)$request->input('session_code'),true);
            if ($action==='accept' || $action==='deny') $this->model->decide($s,$action);
            elseif ($action==='connected') $this->model->connected($s,(string)$request->input('transport'));
            elseif ($action==='end') $this->model->end($s);
            elseif ($action==='signal') $this->model->signal($s,'CLIENT',(string)$request->input('signal_type'),(array)$request->input('payload',[]));
            else throw new DomainException('Invalid agent action.');
            return [];
        });
    }
    public function viewerPoll(Request $request, Response $response, string $code): void
    {
        $this->api($response,function() use($request,$code) {
            $s=$this->viewer($code); $this->model->viewerSeen($s);
            return $this->sessionData($s,'viewer',(int)$request->input('after_id',0));
        });
    }
    public function viewerSignal(Request $request, Response $response): void
    {
        $this->api($response,function() use($request) {
            $s=$this->viewer((string)$request->input('session_code'));
            $this->model->signal($s,'STAFF',(string)$request->input('signal_type'),(array)$request->input('payload',[])); return [];
        });
    }
    public function end(Request $request, Response $response): void
    {
        $this->api($response,function() use($request) { $this->model->end($this->authorized((string)$request->input('session_code'))); return []; });
    }
    public function revoke(Request $request, Response $response): void
    {
        $this->api($response,function() use($request) { $this->model->revoke((int)$request->input('device_id'),Auth::id()); return []; });
    }
}
