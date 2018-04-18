# 基于 thinkphp5 + gateway-worker, 将websocket前后端均封装为控制器模型方式调用

* composer安装的 Thinkphp5,walker/workerman,workerman/gateway-worker以及workerman/GlobalData
* 仅支持linux系统
* workerman_server_check_start.sh为linux脚本,其会自动检测并启动websocket服务,在系统终端运行```bash workerman_server_check_start.sh &```命令即可