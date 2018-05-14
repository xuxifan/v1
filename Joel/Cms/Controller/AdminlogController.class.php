<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--CMS分组日志管理类
// +----------------------------------------------------------------------
// | JoelCMS V1.0 Beta
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.JoelCMS.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Joel <2094157689@qq.com>
// +----------------------------------------------------------------------
namespace Cms\Controller;
use Cms\Controller\BaseController;
class AdminlogController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
	//CMS后台日志管理引导页
    public function index(){
    	//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'日志管理',
				'url'=>U('Cms/Adminlog/index')
			)
		);
    	$this->display();
    }
	

	//CMS后台日志-登录日志
	public function login(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'管理员日志管理',
				'url'=>U('Cms/Adminlog/index')
			),
			'1'=>array(
				'name'=>'登录登出日志',
				'url'=>U('Cms/Adminlog/login')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Adminlog_login');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['uid']=$name;
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '管理员登录日志','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台日志-会员
	public function vip(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'管理员日志管理',
				'url'=>U('Cms/Adminlog/index')
			),
			'1'=>array(
				'name'=>'操作会员日志',
				'url'=>U('Cms/Adminlog/vip')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Adminlog_vip');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$stype=I('stype')?I('stype'):'';
		if($stype==1){
				if($name){
					$map['uid']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',1);
			}
			if($stype==2){
				if($name){
					$map['vipid']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',2);
			}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '操作会员日志','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台日志-订单日志
	public function order(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'管理员日志管理',
				'url'=>U('Cms/Adminlog/index')
			),
			'1'=>array(
				'name'=>'操作订单日志',
				'url'=>U('Cms/Adminlog/order')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Adminlog_order');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$stype=I('stype')?I('stype'):'';
		if($stype==1){
				if($name){
					$map['uid']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',1);
			}
			if($stype==2){
				if($name){
					$map['oid']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',2);
			}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '管理员操作订单日志','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	
	
}