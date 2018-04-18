<?php
namespace app\tcion\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;
use think\Db;

/*自动系统,不需要再死循环,已由shell脚本循环执行*/
class tbaas extends Command
{
	/*启动配置*/
    protected function configure()
    {
        $this->setName('tbaas')->setDescription('tuanzhuan TB AutoAdd System ');
    }
	
	//系统初始化
	protected function initialize(Input $input, Output $output){
        //TODO
	}

    //系统执行
    protected function execute(Input $input, Output $output)
    {
		//TODO
    }
		
}