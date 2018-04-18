#!/bin/sh
#自动运行系统启动
basepath=$(cd `dirname $0`; pwd)
while true
do
        php $basepath/think tbaas
        sleep 3600
done
