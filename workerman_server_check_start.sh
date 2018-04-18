#!/bin/sh
#自动循环检测gateway-woker重启
basepath=$(cd `dirname $0`; pwd)
while true
do
procnum=`ps -ef|grep "workerman_server_for_linux.php"|grep -v grep|wc -l`
        if [ $procnum -eq 0 ]
        then
        php $basepath/workerman_server_for_linux.php restart
        fi
        sleep 5
done
