<?php
namespace Home\Addon\JoelDemo\Controller;
use Think\Controller;
class IndexController extends Controller{
	protected static $addon;
	protected function _initialize() {
		self::$addon=array(
			'id'=>'JoelDemo',//插件目录名(唯一识别)，不得做任意修改！
			'name'=>'CMS演示插件',
			'desc'=>'此插件演示了CMS项目开发规范。'
		);
	}
    public function index(){
        echo 'Addon SystemInfo';
    }
	
	public function haha(){
		dump(I('addon'));
        echo 'haha';
		echo T('Home@Public/menu');
		dump(I('get.'));
		dump(MODULE_NAME);
		dump(__SELF__);
		dump(__INFO__);
		dump(MODULE_PATH);
		C('VIEW_PATH',MODULE_PATH.'Addon/'.self::$addon['id'].'/Tpl/');
		$url=U('Home/Index/haha/addon/JoelDemo/id/1');
		$a=D('Admin');
		$list=$a->select();
		dump($list);
		echo($url);
		//$this->redirect('Home/Index/haha/addon/JoelDemo/id/12/rank/11');
		$this->display();
    }
 }