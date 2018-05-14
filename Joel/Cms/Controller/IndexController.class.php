<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--CMS分组主入口类Index
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
class IndexController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		//p($_SESSION);
		parent::_initialize();
	}
	
	//CMS后台框架入口
    public function index(){
    	//权限处理
		$this->assign('useroath',$_SESSION['CMS']['user']['oath']);
		$this->assign('sys','sys');
		$this->assign('wx','wx');
		$this->assign('fxs','fxs');
		$this->assign('active','active');//1.20 zxg 左菜单显示微活动列表
		$this->assign('vip','vip');
		$this->assign('shop','shop');
		$this->assign('log','log');
    	$this->display();
    }
	
	//CMS后台统计页面
	public function main(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'主控面板',
				'url'=>U('Cms/Index/main')
			)
		);
		$breadhtml=$this->getBread($bread);
		$this->assign('breadhtml',$breadhtml);
		
		//今日起始
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$mapToday['ctime']=array('between',array($beginToday,$endToday));
		//昨日起始
		$beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y')); 
		$endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		$mapYesterday['ctime']=array('between',array($beginYesterday,$endYesterday));
		//上周起始
		$beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y')); 
		$endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
		$mapLastweek['ctime']=array('between',array($beginLastweek,$endLastweek));
		//本月起始
		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y')); 
		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
		$mapThismonth['ctime']=array('between',array($beginThismonth,$endThismonth));
		
		//会员分布
		$mvip=M('Vip');
		$viptotal=$mvip->count();
		$vipsub=$mvip->where('subscribe=1')->count();
		$vipdissub=$viptotal-$vipsub;
		$this->assign('viptotal',$viptotal);
		$this->assign('vipsub',$vipsub);
		$this->assign('vipdissub',$vipdissub);
		//新会员
		$newvipToday=$mvip->where($mapToday)->count();
		$newvipYesterday=$mvip->where($mapYesterday)->count();
		//环比
		if($newvipYesterday){
			$newviprate=intval(($newvipToday-$newvipYesterday)/$newvipYesterday*100);
		}else{
			$newviprate=$newvipToday*100;
		}
		//总共
		if($viptotal){
			$newviptotalrate=intval($newvipToday/$viptotal*100);
		}else{
			$newviptotalrate=$newvipToday*100;
		}
		
		$this->assign('newvipToday',$newvipToday);
		$this->assign('newvipYesterday',$newvipYesterday);
		$this->assign('newviprate',$newviprate);
		$this->assign('newviptotalrate',$newviptotalrate);
		//dump($mapToday);
		//订单分布
		$morder=M('Shop_order');
		$ordertotal=$morder->count();
		$this->assign('ordertotal',$ordertotal);
		for($i=0;$i<7;$i++){
			$name='order'.$i;
			$num=$morder->where('status='.$i)->count();
			$this->assign($name,$num);
		}
		//订单
		$neworderToday=$morder->where($mapToday)->count();
		$neworderYesterday=$morder->where($mapYesterday)->count();
		//环比
		if($neworderYesterday){
			$neworderrate=intval(($neworderToday-$neworderYesterday)/$neworderYesterday*100);
		}else{
			$neworderrate=$neworderToday*100;
		}
		//总共
		if($ordertotal){
			$newordertotalrate=intval($neworderToday/$ordertotal*100);
		}else{
			$newordertotalrate=$neworderToday*100;
		}
		
		$this->assign('neworderToday',$neworderToday);
		$this->assign('neworderYesterday',$neworderYesterday);
		$this->assign('neworderrate',$neworderrate);
		$this->assign('newordertotalrate',$newordertotalrate);
		
		
		//佣金分析
		//订单分布
		$myj=M('Fxs_syslog');
		$yjtotal=$myj->sum('fxyj');
		//今日佣金
		$yjToday=number_format($myj->where($mapToday)->sum('fxyj'),2);
		$yjYesterday=number_format($myj->where($mapYesterday)->sum('fxyj'),2);
		if(!$yjToday){
			$yjToday=0;
		}
		if(!$yjYesterday){
			$yjYesterday=0;
		}
		//环比
		if($yjYesterday){
			$yjrate=intval(($yjToday-$yjYesterday)/$yjYesterday*100);
		}else{
			$yjrate=$yjrate*100;
		}
		//总共
		if($yjtotal){
			$yjtotalrate=intval($yjToday/$yjtotal*100);
		}else{
			$yjtotalrate=$yjToday*100;
		}
		$this->assign('yjtotal',$yjtotal);
		$this->assign('yjToday',$yjToday);
		$this->assign('yjYesterday',$yjYesterday);
		$this->assign('yjrate',$yjrate);
		$this->assign('yjtotalrate',$yjtotalrate);
		//分销分布
		$mfxs=M('Fxs_user');
		$fxstotal=$mfxs->count();
		$this->assign('fxstotal',$fxstotal);
		//一级分销商
		$fx1=$mfxs->where(array('lv'=>1,'status'=>1))->count();
		//二级分销商
		$fx2=$mfxs->where(array('lv'=>2,'status'=>1))->count();
		//三级分销商
		$fx3=$mfxs->where(array('lv'=>3,'status'=>1))->count();
		//四级分销商
		$fx4=$mfxs->where(array('lv'=>4,'status'=>1))->count();
		$this->assign('fx1',$fx1);
		$this->assign('fx2',$fx2);
		$this->assign('fx3',$fx3);
		$this->assign('fx4',$fx4);
    	$this->display();
    }
	
	//CMS后台全局配置
	public function set(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'全局配置',
				'url'=>U('Cms/Index/set')
			)
		);
		$breadhtml=$this->getBread($bread);
		$this->assign('breadhtml',$breadhtml);
		$this->display();
	}
	
	//CMS后台微信配置
	public function setWx(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'微信配置',
				'url'=>U('Cms/Index/setWx')
			)
		);
		$breadhtml=$this->getBread($bread);
		$this->assign('breadhtml',$breadhtml);
		$this->display();
	}
	
	//CMS后台邮件设置
	public function setMail(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'邮件配置',
				'url'=>U('Cms/Index/setMail')
			)
		);
		$breadhtml=$this->getBread($bread);
		$this->assign('breadhtml',$breadhtml);
		$this->display();
	}
	
	//CMS后台邮件设置
	public function setPay(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'支付配置',
				'url'=>U('Cms/Index/setPay')
			)
		);
		$breadhtml=$this->getBread($bread);
		$this->assign('breadhtml',$breadhtml);
		$this->display();
	}
	
	//CMS后台短信设置
	public function setSms(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'短信配置',
				'url'=>U('Cms/Index/setSms')
			)
		);
		$breadhtml=$this->getBread($bread);
		$this->assign('breadhtml',$breadhtml);
		$this->display();
	}
	
	//CMS后台图片浏览器
	public function joelImgviewer(){
		$ids=I('ids');
		//dump($ids);
		$m=M('Upload_img');
		$map['id']=array('in',$ids);
		$cache=$m->where($map)->select();
		$this->assign('cache',$cache);
		$this->ajaxReturn($this->fetch());
	}
	
	//CMS后台区域设置
	public function Location(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'系统设置',
				'url'=>U('Cms/Index/#')
			),
			'1'=>array(
				'name'=>'区域配置',
				'url'=>U('Cms/Index/Location')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		
		$m=M('location_province');
		$province=$m->select();
		$this->assign('province',$province);
		
		$this->display();
	}
	
	public function getLocation(){
		$post = I('get.');
		$m = M('location_'.$post['method']);
		$data = $m->where('pid='.$post['pid'])->select();

		if($data){
			$info['status']=1;
			$info['data']=$data;
			
		}else{
			$info['status']=0;
		}
		$this->ajaxReturn($info);
	}
}