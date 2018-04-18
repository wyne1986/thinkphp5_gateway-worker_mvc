<?php
namespace app\push\controller;
use Workerman\Worker;
use GatewayWorker\Register;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GlobalData\Server;

class RunController
{

    public function __construct()
    {
        /*由于是手动添加，因此需要注册命名空间，方便自动加载，具体代码路径以实际情况为准*/
        \think\Loader::addNamespace([
            'Workerman' => VENDOR_PATH . 'workerman/workerman',
            'GatewayWorker' =>VENDOR_PATH . 'workerman/gateway-worker/src'
        ]);

        /*初始化register*/
        new Register('text://0.0.0.0:1238');

        //初始化 bussinessWorker 进程
        $worker = new BusinessWorker();
        $worker->name = 'AppBusinessWorker';
        $worker->count = 4;
        $worker->registerAddress = '127.0.0.1:1238';

        /*设置处理业务的类,此处制定Events的命名空间*/
        $worker->eventHandler = '\app\push\controller\Events';

        /*初始化 gateway 进程*/
        $gateway = new Gateway("websocket://0.0.0.0:8282");
        $gateway->name = 'AppGateway';
        $gateway->count = 4;
        $gateway->lanIp = '127.0.0.1';
        $gateway->startPort = 2900;
        $gateway->registerAddress = '127.0.0.1:1238';

        /*全局共享变量服务$global*/
        \think\Loader::import('workerman.globaldata.src.Server',VENDOR_PATH,'.php');
        new Server('127.0.0.1', 2207);

        /*运行所有Worker*/
        Worker::runAll();

    }

}