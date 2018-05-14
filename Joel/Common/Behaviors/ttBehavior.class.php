<?php
namespace Common\Behaviors;
class ttBehavior{
    //行为执行入口
    public function run(&$param){
    	echo '我是行为钩子common';
    }
}