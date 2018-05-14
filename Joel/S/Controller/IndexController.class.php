<?php
// +----------------------------------------------------------------------
// | 分销后台基础类--S分组主入口类Index
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
class IndexController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
	//S后台框架入口
    public function index(){
    	$this->display();
    }
	
	//S后台统计页面
	public function main(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'主控面板',
				'url'=>U('S/Index/main')
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
		
		//取渠道ID
		$sid=self::$S['uid'];
		$mapsid['sid']=$sid;
		//会员分布
		$mvip=M('Vip');
		$viptotal=$mvip->where($mapsid)->count();
		$vipsub=$mvip->where(array('subscribe'=>1,'sid'=>$sid))->count();
		$vipdissub=$viptotal-$vipsub;
		$this->assign('viptotal',$viptotal);
		$this->assign('vipsub',$vipsub);
		$this->assign('vipdissub',$vipdissub);
		//新会员
		$newvipToday=$mvip->where($mapsid)->where($mapToday)->count();
		$newvipYesterday=$mvip->where($mapsid)->where($mapYesterday)->count();
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
		$ordertotal=$morder->where($mapsid)->count();
		$this->assign('ordertotal',$ordertotal);
		for($i=0;$i<7;$i++){
			$name='order'.$i;
			$num=$morder->where($mapsid)->where('status='.$i)->count();
			$this->assign($name,$num);
		}
		//订单
		$neworderToday=$morder->where($mapsid)->where($mapToday)->count();
		$neworderYesterday=$morder->where($mapsid)->where($mapYesterday)->count();
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
		$yjtotal=$myj->where($mapsid)->sum('fxyj');
		//今日佣金
		$yjToday=number_format($myj->where($mapsid)->where($mapToday)->sum('fxyj'),2);
		$yjYesterday=number_format($myj->where($mapsid)->where($mapYesterday)->sum('fxyj'),2);
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
		//累积提现
		$nowmoney=$_SESSION['S']['user']['money'];
		$txtotal=M('Fxs_tx')->where($mapsid)->where('status=2')->sum('txprice');
		$this->assign('nowmoney',$nowmoney);
		$this->assign('txtotal',$txtotal);
    	$this->display();
    }
	
		
	//S后台图片浏览器
	public function joelImgviewer(){
		$ids=I('ids');
		//dump($ids);
		$m=M('Upload_img');
		$map['id']=array('in',$ids);
		$cache=$m->where($map)->select();
		$this->assign('cache',$cache);
		$this->ajaxReturn($this->fetch());
	}
	
}