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
class LogController extends BaseController {
	
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
				'url'=>U('Cms/Log/index')
			)
		);
    	$this->display();
    }

	
	//CMS后台日志-会员
	public function vip(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'日志管理',
				'url'=>U('Cms/Log/index')
			),
			'1'=>array(
				'name'=>'会员日志',
				'url'=>U('Cms/Log/vip')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Vip_log');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			//$map['vipid']=array('like',"%$name%");
			$map['vipid']=$name;
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '会员日志','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台日志-订单
	public function order(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'日志管理',
				'url'=>U('Cms/Log/index')
			),
			'1'=>array(
				'name'=>'会员日志',
				'url'=>U('Cms/Log/order')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Shop_order_syslog');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			//$map['oid']=array('like',"%$name%");
			$map['oid']=$name;
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '订单日志','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台日志-分销
	public function fx(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'日志管理',
				'url'=>U('Cms/Log/index')
			),
			'1'=>array(
				'name'=>'分销日志[会员]',
				'url'=>U('Cms/Log/order')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Fx_syslog');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$stype=I('stype')?I('stype'):'';
		if($stype==1){
				if($name){
					$map['to']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',1);
			}
			if($stype==2){
				if($name){
					$map['from']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',2);
			}
			if($stype==3){
				if($name){
					$map['oid']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',3);
			}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '分销日志','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台日志-分销
	public function fxs(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'日志管理',
				'url'=>U('Cms/Log/index')
			),
			'1'=>array(
				'name'=>'分销日志[经销商]',
				'url'=>U('Cms/Log/order')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Fxs_syslog');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
			$stype=I('stype')?I('stype'):'';
		if($stype==1){
				if($name){
					$map['to']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',1);
			}
			if($stype==2){
				if($name){
					$map['from']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',2);
			}
			if($stype==3){
				if($name){
					$map['oid']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',3);
			}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '分销日志','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台日志-推广
	public function tj(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'日志管理',
				'url'=>U('Cms/Log/index')
			),
			'1'=>array(
				'name'=>'推广日志',
				'url'=>U('Cms/Log/tj')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Fx_log_tj');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			//$map['vipid']=array('like',"%$name%");
			$map['vipid']=$name;
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '会员推广日志','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台日志-分销商推广
	public function fxstj(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'日志管理',
				'url'=>U('Cms/Log/index')
			),
			'1'=>array(
				'name'=>'推广日志',
				'url'=>U('Cms/Log/tj')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Fxs_log_sub');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			//$map['vipid']=array('like',"%$name%");
			$map['sid']=$name;
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '分销商推广日志','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	
}