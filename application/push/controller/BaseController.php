<?php
namespace app\push\controller;
use GatewayWorker\Lib\Gateway;

class BaseController
{

    public $client_id;
    public $params;

    /*控制器初始化方法*/
    public final function init($client_id,$message)
    {
        $this->client_id = $client_id;
        $this->params = isset($message['result']) ? $message['result'] : [];
        /*判断是否登录*/
        $session = wmsession($client_id);
        $imfreeauth = model('ImAuth')->getFreeAuth(true);
        if(in_array($message['controller'].'/'.$message['action'],$imfreeauth)){
            return true;
        }
        if(!empty($session['role_type']) && !empty($session['role_id'])){
            if(isset($session['token'])){
                if(empty($session['token']) || empty($this->params['token']['token']) || $session['token']!=$this->params['token']['token']){
                    $json = json_encode(array('status'=>1,'message'=>'用户身份验证失败','controller'=>'index','action'=>'error'),true);
                    Gateway::sendToCurrentClient($json);
                    Gateway::closeClient($client_id);
                    return false;
                }
                $imauth = model('ImRoleAuth')->getAuth($session['role_id'],$session['role_type']);
                if(!in_array($message['controller'].'/'.$message['action'],$imauth)){
                    $json = json_encode(array('status'=>1,'message'=>'暂无权限','controller'=>'index','action'=>'error'),true);
                    Gateway::sendToCurrentClient($json);
                    return false;
                }
                return true;
            }else{
                $json = json_encode(array('status'=>1,'message'=>'未登录','controller'=>'index','action'=>'error'),true);
                Gateway::sendToCurrentClient($json);
                Gateway::closeClient($client_id);
                return false;
            }
        }else{
            $json = json_encode(array('status'=>1,'message'=>'权限检查失败','controller'=>'index','action'=>'error'),true);
            Gateway::sendToCurrentClient($json);
            Gateway::closeClient($client_id);
            return false;
        }
    }

    public function json($controller,$action='',$message='',$status=0,$result=[]){
        if(is_array($controller)) return json_encode($controller,true);
        return json_encode(['status'=>$status,'message'=>$message,'controller'=>$controller,'action'=>$action,'result'=>$result],true);
    }

}