<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--CMS分组商城管理类
// +----------------------------------------------------------------------
// | JoelCMS V1.0 Beta
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.JoelCMS.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Joel <2094157689@qq.com>
// +----------------------------------------------------------------------
namespace S\Controller;
use S\Controller\BaseController;
class ShopController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
		//初始化两个配置
		self::$S['shopset']=M('Shop_set')->find();
		self::$S['vipset']=M('Vip_set')->find();		
	}
	
	//S后台商城管理引导页
    public function index(){
    	//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的商城',
				'url'=>U('S/Shop/index')
			)
		);
    	$this->display();
    }
	
		
	//S后台商城分组
	public function goods(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的商城',
				'url'=>U('S/Shop/index')
			),
			'1'=>array(
				'name'=>'商品管理',
				'url'=>U('S/Shop/goods')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Shop_goods');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$S['set']['pagesize']?self::$S['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '商品管理','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
		
	
	//S后台广告分组
	public function ads(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的商城',
				'url'=>U('S/Shop/index')
			),
			'1'=>array(
				'name'=>'商城广告',
				'url'=>U('S/Shop/ads')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Shop_ads');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$S['set']['pagesize']?self::$S['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $k=>$v){
			$listpic=$this->getPic($v['pic']);
			$cache[$k]['imgurl']=$listpic['imgurl'];
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '商城广告','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//S后台广告设置
	public function adsSet(){
		$id=I('id');
		$m=M('Shop_ads');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'我的商城',
				'url'=>U('S/Shop/index')
			),
			'1'=>array(
				'name'=>'商城广告',
				'url'=>U('S/Shop/ads')
			),
			'2'=>array(
				'name'=>'广告设置',
				'url'=>$id?U('S/Shop/adsSet',array('id'=>$id)):U('S/Shop/adsSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			if($id){
				$re=$m->save($data);
				if(FALSE!==$re){
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				$re=$m->add($data);
				if($re){
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}
			$this->ajaxReturn($info);
		}
		//处理编辑界面
		if($id){			
			$cache=$m->where('id='.$id)->find();
			$this->assign('cache',$cache);	
		}	
		$this->display();
	}
	
	public function adsDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('Shop_ads');
		if(!id){
			$info['status']=0;
			$info['msg']='ID不能为空!';
			$this->ajaxReturn($info);
		}
		$re=$m->delete($id);
		if($re){
			$info['status']=1;
			$info['msg']='删除成功!';
		}else{
			$info['status']=0;
			$info['msg']='删除失败!';
		}
		$this->ajaxReturn($info);
	}

	
	//S后台商城订单
	public function order(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的商城',
				'url'=>U('S/Shop/index')
			),
			'1'=>array(
				'name'=>'订单管理',
				'url'=>U('S/Shop/order')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));		
		$status=I('status');
		if($status || $status=='0'){
			$map['status']=$status;
		}
		$this->assign('status',$status);
		//绑定搜索条件与分页
		$m=M('Shop_order');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			//订单号邦定
			//$map['oid']=array('like',"%$name%");
			//$map['vipmobile']=array('like',"%$name%");
			//$map['oid']=array('eq',"$name");
			$map['vipid']=array('eq',"$name");
//			$map['vipname']=array('eq',"$name");
//			$map['vipmobile']=array('eq',"$name");			
//			$map['_logic'] = 'OR';
			$this->assign('name',$name);
		}
		//追入分销商逻辑
		$map['sid']=self::$S['uid'];
		$psize=self::$S['set']['pagesize']?self::$S['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '商城订单','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//S后台Order详情
	public function orderDetail(){
		$id=I('id');
		$m=M('Shop_order');
		$mlog=M('Shop_order_log');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'我的商城',
				'url'=>U('S/Shop/index')
			),
			'1'=>array(
				'name'=>'商城订单',
				'url'=>U('S/Shop/order')
			),
			'2'=>array(
				'name'=>'订单详情',
				'url'=>$id?U('S/Shop/orderDetail',array('id'=>$id)):U('S/Shop/orderDetail')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));		
		$cache=$m->where('id='.$id)->find();
		$cache['items']=unserialize($cache['items']);
		$log=$mlog->where('oid='.$cache['id'])->select();
		$fxlog=M('Fx_syslog')->where('oid='.$cache['id'])->select();
		$this->assign('log',$log);
		$this->assign('fxlog',$fxlog);
		$this->assign('cache',$cache);
		$this->display();
	}
	
		
	

}