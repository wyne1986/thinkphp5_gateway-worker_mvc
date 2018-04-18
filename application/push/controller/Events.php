<?php
namespace app\push\controller;
use Workerman\Lib\Timer;
use GatewayWorker\Lib\Gateway;
use think\Controller;
use think\Exception;
use GlobalData\Client;

class Events extends Controller
{

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        /*
         * GlobalData全局共享变量 global $global
         * 在线用户总数增加
        */
        global $global;
        \think\Loader::import('workerman.globaldata.src.Client',VENDOR_PATH,'.php');
        $global = new Client('127.0.0.1:2207');
        if(!$global->add('user_count', 0))$global->increment('user_count',1);
        if($global->user_count<(config('WORKER.MAX_USER_COUNT')?config('WORKER.MAX_USER_COUNT'):100)){
            $message = json_encode(['status'=>0,'message'=>'连接成功','controller'=>'index','action'=>'connect'],true);
            Gateway::sendToCurrentClient($message);
            /* 暂离检测,自动断线
            $user_timer = Timer::add(config('WORKER.CHECK_AFK_TIME')?config('WORKER.CHECK_AFK_TIME'):30, function($client_id){
                if((wmsession($client_id,'afk_time')+(config('WORKER.AFK_TIME')?config('WORKER.AFK_TIME'):600)) < time()){
                    $json = json_encode(array('status'=>1,'message'=>'暂离超时自动断开连接','controller'=>'index','action'=>'error'),true);
                    Gateway::sendToClient($client_id,$json);
                    Gateway::closeClient($client_id);
                }
            }, [$client_id]);
            wmsession($client_id,'user_timer',$user_timer);
            */
        }else{
            $json = json_encode(['status'=>1,'message'=>'错误:服务器已满员,请稍后再试','controller'=>'index','action'=>'error','result'=>'test'],true);
            Gateway::sendToCurrentClient($json);
            Gateway::closeClient($client_id);
        }
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
       //将json格式字符串解析成数组备用
       $message = json_decode($message,true);
       //获取控制器名称
       $main['controller'] = isset($message['controller']) ?  $message['controller'] : 'index';
       //获取方法名称
       $main['action'] = isset($message['action']) ? $message['action'] : 'error';

       /*刷新暂离时间*/
       wmsession($client_id,'afk_time',time());

       /*调用请求类方法*/
       try{
           /*调用控制器方法*/
           @$controller = \think\Loader::controller($main['controller']);
           /*调用Base控制器初始化接收参数方法*/
           if(@$controller->init($client_id,$message)){
               @$controller->$main['action']();
           }
       }catch(Exception $e){
           $json = json_encode(array('status'=>1,'message'=>'错误:'.$e->getMessage(),'controller'=>'index','action'=>'error'),true);
           Gateway::sendToCurrentClient($json);
       }

   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       /*
        * GlobalData全局共享变量 global $global
        * 在线用户总数减少
       */
       global $global;
       \think\Loader::import('workerman.globaldata.src.Client',VENDOR_PATH,'.php');
       $global = new Client('127.0.0.1:2207');
       if(!$global->add('user_count', 0))$global->increment('user_count',-1);
       /*断开连接时销毁定时器*/
       Timer::del($_SESSION['user_timer']);

       $json = json_encode(['status'=>0,'message'=>'已断开连接','controller'=>'index','action'=>'disconnect'],true);
       Gateway::sendToCurrentClient($json);
       Gateway::closeClient($client_id);
   }

}