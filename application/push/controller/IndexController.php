<?php
namespace app\push\controller;
use GatewayWorker\Lib\Gateway;

class IndexController extends BaseController
{

    /*用户登录方法*/
    public function login(){
        if(empty($this->params['token']) || empty($this->params['token']['token'])){
            Gateway::sendToClient($this->client_id, wmjson(['status'=>1,'message'=>'登录失败:缺少身份标识','controller'=>'index','action'=>'login']));
            Gateway::closeClient($this->client_id);
        }else if(empty($this->params['user_phone'])){
            Gateway::sendToClient($this->client_id, wmjson(['status'=>1,'message'=>'登录失败:用户账号不能为空','controller'=>'index','action'=>'login']));
            Gateway::closeClient($this->client_id);
        }else{
            $user = model('User')->where(['user_phone'=>$this->params['user_phone']])->find();
            if(!empty($user)){
                $token = md5($user['user_id'].$user['user_phone'].$user['user_salt'].$this->params['logintime']);
                if($token==$this->params['token']['token']){
                    //将客户端ID与用户名绑定成UID
                    Gateway::bindUid($this->client_id,$user['user_phone']);
                    //保存客户端在socket服务端的session
                    wmsession($this->client_id,['user_id'=>$user['user_id'],'uid'=>$user['user_phone'],'role_id'=>$user['user_role_id'],'token'=>$token]);
                    //构建并返回信息给客户端
                    Gateway::sendToClient($this->client_id, wmjson(['status'=>0,'message'=>'登录成功','controller'=>'index','action'=>'login']));
                }else{
                    Gateway::sendToClient($this->client_id, wmjson(['status'=>1,'message'=>'登录失败:身份验证失败','controller'=>'index','action'=>'login']));
                    Gateway::closeClient($this->client_id);
                }
            }else{
                Gateway::sendToClient($this->client_id, wmjson(['status'=>1,'message'=>'登录失败:用户不存在','controller'=>'index','action'=>'login']));
                Gateway::closeClient($this->client_id);
            }
        }
    }

    /*用户加入组*/
    public function joinGroup(){
        $user_id = wmsession($this->client_id,'user_id');
        if(!empty($user_id)){
            if(isset($this->params['groupname'])){
                $group = model('User')->getGroup($this->params['groupname'],$user_id);
                wmsession($this->client_id,'group','group');
                joinAllGroup($this->client_id,'group');
                (!empty($group) && $group[0]!='guest') ? wmsession($this->client_id,'role_type',$this->params['groupname']) : wmsession($this->client_id,'role_type','guest');
                Gateway::sendToClient($this->client_id, wmjson(['status'=>0,'message'=>'加入组成功','controller'=>'index','action'=>'joinGroup']));
            }else{
                Gateway::sendToClient($this->client_id, wmjson(['status'=>1,'message'=>'加入组失败,缺少组参数groupname','controller'=>'index','action'=>'joinGroup']));
            }
        }else{
            Gateway::sendToClient($this->client_id, wmjson(['status'=>1,'message'=>'用户还未登录,无法加入组','controller'=>'index','action'=>'joinGroup']));
        }
    }

    /*报错方法*/
    public function error(){
        Gateway::sendToClient($this->client_id,['status'=>1,'message'=>'错误']);
    }

}