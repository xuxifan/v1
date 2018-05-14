<?php
// +----------------------------------------------------------------------
// | 红包管理类
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
class RedpaperController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
		//初始化两个配置
		self::$CMS['shopset']=M('Shop_set')->find();
		self::$CMS['vipset']=M('Vip_set')->find();		
	}
	
	//CMS后台商城管理引导页
    public function index(){
    	//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'砸金蛋',
				'url'=>U('Cms/Zjd/index')
			)
		);
    	$this->display();
    }

    /* * * * *
     * 红包设置
     */
	public function set(){
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'营销功能',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'红包设置',
				'url'=>U('Cms/redpaper/set')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$m=M('Redpaper');
		if(IS_POST){
			$id=I('id');
			$post=I('post.');
			$set['isredpaper']=$post['status'];
			$set['redpic']=$post['fxpic'];
			$set['redtitle']=$post['fxtitle'];
			$set['redmsg']=$post['fxmsg'];
			$data['id']=$post['id'];
			$data['name']=$post['name'];
			$data['pic']=$post['pic'];
			$data['manager']=$post['manager'];
			$data['price1']=$post['price1'];
			$data['price2']=$post['price2'];
			$data['price3']=$post['price3'];
			$data['content']=$post['content'];
			$data['ctime']=time();
			$ree=M('Shop_set')->where('id=1')->save($set);
			if($id){
				$re=$m->where('id='.$id)->save($data);
			}else{
				$re=$m->add($data);
			}
			if($re !== false && $ree !== false){
				$info['status']=1;
				$info['msg']="修改成功！";
			}else{
				$info['status']=0;
				$info['msg']="修改失败！";
			}
			$this->ajaxReturn($info);
		}else{
			$cache=$m->find();
			$set=M('Shop_set')->field('isredpaper,redpic,redtitle,redmsg')->find();
			$cache['status']=$set['isredpaper'];
			$cache['fxpic']=$set['redpic'];
			$cache['fxtitle']=$set['redtitle'];
			$cache['fxmsg']=$set['redmsg'];
			$this->assign('cache',$cache);
		}
		$this->display();
	}

	/* * * * *
     * 红包订单列表设置
     */
	public function orderlist(){
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'营销功能',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'红包设置',
				'url'=>U('Cms/redpaper/set')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$m=M('Redpaper_order');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['nickname']=array('like',"%$name%");
			$map['vipid']=array('eq',$name);
			$map['_logic'] = 'OR';
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '红包订单管理','Joel-search');
		$this->assign('cache',$cache);
		$this->display();
	}

	/* * * * *
     * 红包订单列表设置
     */
	public function loglist(){
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'营销功能',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'红包设置',
				'url'=>U('Cms/redpaper/set')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$m=M('Redpaper_log');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$status=I('status')?I('status'):false;
		if(I('status')==="0"){
			$status = 0;
		}
		if($name || $status || $status===0){
			if($name){
				$where['toNickname']=array('like',"%$name%");
				$where['toVip']=array('eq',$name);
				$where['fromNickname']=array('like',"%$name%");
				$where['fromVip']=array('eq',$name);
				$where['_logic'] = 'OR';
				$map['_complex'] = $where;
			}
			if($status !==false){
				$map['status']=array('eq',$status);
				$this->assign('status',$status);
			}
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '红包订单管理','Joel-search');
		$this->assign('cache',$cache);
		$this->display();
	}

	/* * * * *
     * 红包日志导出页面
     */
	public function logOutput(){
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'营销功能',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'红包设置',
				'url'=>U('Cms/redpaper/set')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		// 计算价格区间
		$m=M('Redpaper_log');
		$cache['money']=$m->distinct(true)->field('money')->select();
		// 选择时间 默认为昨天的时间
		$cache['etime']=date('Y-m-d',time());
		$cache['stime']=date('Y-m-d',time()-60*60*24);
		// 选择状态
		$this->assign('cache',$cache);
		$this->display();
	}

	/* * * * *
     * 红包日志导出text格式
     */
	public function downloadtext(){
		if(I('status')!='all'){
			$map['status']=I('status');
		}
		if(I('money')!='all'){
			$map['money']=I('money');
		}
		$stime=strTotime(I("stime")." 00:00:00");
		$etime=strTotime(I("etime")." 00:00:00");
		$map['ctime']=array(array('egt',$stime),array('lt',$etime), 'and');
		$m=M("Redpaper_log");
		$cache=$m->where($map)->field('toOpenid')->select();
		$arr=array('未发送','未领取','已领取','超时未领取','发送失败','all'=>'全部');
		$title="金额_".I('money')."_状态_".$arr[I('status')].'_'.I("stime")."-".I("etime");
		Header( "Content-type: application/octet-stream "); 
		Header( "Accept-Ranges: bytes "); 
		header( "Content-Disposition: attachment; filename={$title}.txt "); 
		header( "Expires: 0 "); 
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0 "); 
		header( "Pragma: public "); 
		$enter = "
";
		foreach($cache as $val){
			echo $val['toOpenid'].$enter;
		}
	}

	public function changeStatus(){
		if(IS_POST){
			if(I('status')!='all'){
				$map['status']=I('status');
			}
			if(I('money')!='all'){
				$map['money']=I('money');
			}
			$stime=strTotime(I("stime")." 00:00:00");
			$etime=strTotime(I("etime")." 00:00:00");
			$map['ctime']=array(array('egt',$stime),array('lt',$etime), 'and');
			$m=M("Redpaper_log");
			$cache=$m->where($map)->field('fromVip')->distinct(true)->select();
			$str="";
			foreach($cache as $v){
				$str.=$v['fromVip'].",";
			}
			$str=trim($str,',');
			$ree=M('Redpaper_order')->where(array('vipid'=>array('in',$str)))->save(array('status'=>'3'));
			$re=$m->where($map)->save(array('status'=>'1'));
			if($re!==false && $ree!==false){
				$info['status']=1;
				$info['msg']="发送成功";
			}else{
				$info['status']=0;
				$info['msg']="发送失败，请重试";
			}
			$this->ajaxReturn($info);
		}
	}

	public function sendselected(){
		if(IS_AJAX){
			// dump(I('post.'));
			$m=M('Redpaper_log');
			$o=M('Redpaper_order');
			$o_map['vipid']=array('in',I('ids'));
			$map['fromVip']=array('in',I('ids'));
			$re=$m->where($map)->setField('status',1);
			$ree=$o->where($o_map)->setField('status',3);
			if($re !== false && $ree !== false){
				$info['status']=1;
				$info['msg']="设置成功！";
			}else{
				$info['status']=0;
				$info['msg']="设置失败，请重试！";
			}
			$this->ajaxReturn($info);
		}
	}
	
}