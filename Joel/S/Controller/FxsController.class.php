<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--S分组分销商中心类
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
class FxsController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();	
	}
	
	//S后台广告分组
	public function ads(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的商城',
				'url'=>U('S/Fxs/index')
			),
			'1'=>array(
				'name'=>'商城广告',
				'url'=>U('S/Fxs/ads')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Fxs_ads');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$map['sid']=self::$S['uid'];
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
		$m=M('Fxs_ads');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'我的商城',
				'url'=>U('S/Fxs/index')
			),
			'1'=>array(
				'name'=>'商城广告',
				'url'=>U('S/Fxs/ads')
			),
			'2'=>array(
				'name'=>'广告设置',
				'url'=>$id?U('S/Fxs/adsSet',array('id'=>$id)):U('S/Fxs/adsSet')
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
		$m=M('Fxs_ads');
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
	
	
	//CMS后台商城分类
	public function user(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的分销中心',
				'url'=>U('S/Fxs/index')
			),
			'1'=>array(
				'name'=>'分销商管理',
				'url'=>U('S/Fxs/user')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Fxs_user');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['nickname']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		//JoelTree快速无限分类
		$field=array("id","pid","no","lv","nickname","username","mobile","total_xxyj","total_xxlink","total_xxsub","total_xxbuy","sonnum","soncate","ctime","sorts","qrticket","status","concat(path,'-',id) as bpath");
		$cache=joelTree($m, self::$S['uid'], $field);
		$this->assign('cache',$cache);
		$mapp['path']=array('like',self::$S['user']['path'].'-'.self::$S['user']['id'].'%');
		$all=$m->field('id')->where($mapp)->count();
		$this->assign('all',$all);
		$total=$_SESSION['S']['user']['sonnum'];
		$now=count($cache);
		$left=$total-$now;
		$this->assign('now',$now);
		$this->assign('left',$left);		
		$this->display();
	}
	
	//S后台商城分类设置
	public function userSet(){
		$id=I('id');
		$m=M('Fxs_user');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'我的分销中心',
				'url'=>U('S/Fxs/index')
			),
			'1'=>array(
				'name'=>'分销商管理',
				'url'=>U('S/Fxs/user')
			),
			'2'=>array(
				'name'=>'分销商设置',
				'url'=>$id?U('S/Fxs/userSet',array('id'=>$id)):U('S/Fxs/userSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			$isname=$m->where(array('username'=>$data['username']))->find();
			if($isname){
				$info['status']=0;
				$info['msg']='此登陆用户名已存在！请重新设置！';
				$this->ajaxReturn($info);
			}
			if($id){
				//保存时判断
				$old=$m->where('id='.$id)->limit(1)->find();
				if($old['pid']<>$data['pid']){
					$hasson=$m->where('pid='.$id)->limit(1)->find();
					if($hasson){
						$info['status']=0;
						$info['msg']='此分销商下有子分销商，不可以移动！';
						$this->ajaxReturn($info);
					}
				}
				if($data['pid']){
					$father=$m->where('id='.$old['pid'])->find();
					//不允许超过上线最大数
					if($father['sonnum']<$data['sonnum']){
						$info['status']=0;
						$info['msg']='此分销商下线总数不可以超过'.$old['sonnum'].'个！';
						$this->ajaxReturn($info);
					}
				}
				
				if($data['pid']){					
					//更新Path，强制处理
					$path=setPath($m, $data['pid']);
					$data['path']=$path['path'];
					$data['lv']=$path['lv'];
				}else{
					$data['path']=0;
					$data['lv']=1;
				}
				
				//重新生成二维码
				if(!$old['qrticket']){
					$options['appid']=self::$SYS['set']['wxappid'];
					$options['appsecret']=self::$SYS['set']['wxappsecret'];
					$wx=new \Joel\wx\Wechat($options);
					$rqr=$wx->getQRCode($data['id'],1);
					if($rqr){
						$data['qrticket']=$rqr['ticket'];
						$data['qrurl']=$rqr['url'];
					}
				}
				
				//修改分销商密码
				if($data['userpass']){
					$data['userpass']=md5($data['userpass']);
				}
				
				$re=$m->save($data);
				if(FALSE!==$re){
					//更新新老父级，暂不做错误处理
					if($old['pid']<>$data['pid']){
						$re=setSoncate($m, $data['pid']);
						$rold=setSoncate($m, $old['pid']);						
					
					}else{
						$re=setSoncate($m, $data['pid']);
					}
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				//处理下线
				if($data['pid']){
					//新增时处理最大3层逻辑
					$father=$m->where('id='.$data['pid'])->limit(1)->find();
					//不允许超过3级
					if($father['lv']>=3){
						$info['status']=0;
						$info['msg']='系统定义分销商层级不允许超过3层！';
						$this->ajaxReturn($info);
					}
					//不得超过上级下线
					if($father['sonnum']<$data['sonnum']){
						$info['status']=0;
						$info['msg']='此分销商的允许下线数量不允许超过'.$father['sonnum'].'个！';
						$this->ajaxReturn($info);
					}
					//
					$fatherson=$m->where(array('pid'=>$data['pid']))->count();
					if(($fatherson+1)>$father['sonnum']){
						$info['status']=0;
						$info['msg']='您最多只能建立'.$father['sonnum'].'个分销商！';
						$this->ajaxReturn($info);
					}
					
				}
							
				if($data['pid']){
					//更新父级，强制处理
					$path=setPath($m, $data['pid']);
					$data['path']=$path['path'];
					$data['lv']=$path['lv'];
				}else{
					$data['path']=0;
					$data['lv']=1;
				}
				//处理下线密码
				$data['userpass']=md5($data['userpass']);
				//处理下线生成时间
				$data['ctime']=time();
				$re=$m->add($data);
				if($re){
					//生成二维码
					$options['appid']=self::$SYS['set']['wxappid'];
					$options['appsecret']=self::$SYS['set']['wxappsecret'];
					$wx=new \Joel\wx\Wechat($options);
					$rqr=$wx->getQRCode($re,1);
					if($rqr){
						$r=$m->where('id='.$re)->setField('qrticket',$rqr['ticket']);
						$rr=$m->where('id='.$re)->setField('qrurl',$rqr['url']);
					}
					//更新父级，暂不做错误处理
					if($data['pid']){
						$re=setSoncate($m, $data['pid']);
					}
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
		//JoelTree快速无限分类
		$field=array("id","pid","lv","no","nickname","username","mobile","total_xxyj","total_xxlink","total_xxsub","total_xxbuy","sonnum","soncate","qrticket","status","concat(path,'-',id) as bpath");
		$cate=joelTree($m, 0, $field);
		$this->assign('cate',$cate);
		$this->display();
	}

//S后台商城分类设置
	public function myuserSet(){
		$id=I('id');
		$m=M('Fxs_user');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'我的分销中心',
				'url'=>U('S/Fxs/index')
			),
			'1'=>array(
				'name'=>'分销商管理',
				'url'=>U('S/Fxs/user')
			),
			'2'=>array(
				'name'=>'分销商设置',
				'url'=>$id?U('S/Fxs/userSet',array('id'=>$id)):U('S/Fxs/userSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			$isname=$m->where(array('username'=>$data['username']))->find();
			if($isname){
				$info['status']=0;
				$info['msg']='此登陆用户名已存在！请重新设置！';
				$this->ajaxReturn($info);
			}
			if($id){
				//保存时判断
				$old=$m->where('id='.$id)->limit(1)->find();
				if($old['pid']<>$data['pid']){
					$hasson=$m->where('pid='.$id)->limit(1)->find();
					if($hasson){
						$info['status']=0;
						$info['msg']='此分销商下有子分销商，不可以移动！';
						$this->ajaxReturn($info);
					}
				}
				if($data['pid']){
					$father=$m->where('id='.$old['pid'])->find();
					//不允许超过上线最大数
					if($father['sonnum']<$data['sonnum']){
						$info['status']=0;
						$info['msg']='此分销商下线总数不可以超过'.$old['sonnum'].'个！';
						$this->ajaxReturn($info);
					}
				}
				
				if($data['pid']){					
					//更新Path，强制处理
					$path=setPath($m, $data['pid']);
					$data['path']=$path['path'];
					$data['lv']=$path['lv'];
				}else{
					$data['path']=0;
					$data['lv']=1;
				}
				
				//重新生成二维码
				if(!$old['qrticket']){
					$options['appid']=self::$SYS['set']['wxappid'];
					$options['appsecret']=self::$SYS['set']['wxappsecret'];
					$wx=new \Joel\wx\Wechat($options);
					$rqr=$wx->getQRCode($data['id'],1);
					if($rqr){
						$data['qrticket']=$rqr['ticket'];
						$data['qrurl']=$rqr['url'];
					}
				}
				
				//修改分销商密码
				if($data['userpass']){
					$data['userpass']=md5($data['userpass']);
				}
				
				$re=$m->save($data);
				if(FALSE!==$re){
					//更新新老父级，暂不做错误处理
					if($old['pid']<>$data['pid']){
						$re=setSoncate($m, $data['pid']);
						$rold=setSoncate($m, $old['pid']);						
						
					}else{
						$re=setSoncate($m, $data['pid']);
					}
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				//处理下线
				if($data['pid']){
					//新增时处理最大3层逻辑
					$father=$m->where('id='.$data['pid'])->limit(1)->find();
					//不允许超过3级
					if($father['lv']>=3){
						$info['status']=0;
						$info['msg']='系统定义分销商层级不允许超过3层！';
						$this->ajaxReturn($info);
					}
					//不得超过上级下线
					if($father['sonnum']<$data['sonnum']){
						$info['status']=0;
						$info['msg']='此分销商的允许下线数量不允许超过'.$father['sonnum'].'个！';
						$this->ajaxReturn($info);
					}
					//
					$fatherson=$m->where(array('pid'=>$data['pid']))->count();
					if(($fatherson+1)>$father['sonnum']){
						$info['status']=0;
						$info['msg']='您最多只能建立'.$father['sonnum'].'个分销商！';
						$this->ajaxReturn($info);
					}
					
				}
							
				if($data['pid']){
					//更新父级，强制处理
					$path=setPath($m, $data['pid']);
					$data['path']=$path['path'];
					$data['lv']=$path['lv'];
				}else{
					$data['path']=0;
					$data['lv']=1;
				}
				//处理下线密码
				$data['userpass']=md5($data['userpass']);
				//处理下线生成时间
				$data['ctime']=time();
				$re=$m->add($data);
				if($re){
					//生成二维码
					$options['appid']=self::$SYS['set']['wxappid'];
					$options['appsecret']=self::$SYS['set']['wxappsecret'];
					$wx=new \Joel\wx\Wechat($options);
					$rqr=$wx->getQRCode($re,1);
					if($rqr){
						$r=$m->where('id='.$re)->setField('qrticket',$rqr['ticket']);
						$rr=$m->where('id='.$re)->setField('qrurl',$rqr['url']);
					}
					//更新父级，暂不做错误处理
					if($data['pid']){
						$re=setSoncate($m, $data['pid']);
					}
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
		//JoelTree快速无限分类
		$field=array("id","pid","lv","no","nickname","username","mobile","total_xxyj","total_xxlink","total_xxsub","total_xxbuy","sonnum","soncate","qrticket","status","concat(path,'-',id) as bpath");
		$cate=joelTree($m, 0, $field);
		$this->assign('cate',$cate);
		$this->display();
	}

	//S后台商城用户设置
	public function infoSet(){
		$id=$_SESSION['S']['uid'];
		$m=M('Fxs_user');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'我的分销中心',
				'url'=>U('S/Fxs/index')
			),
			'2'=>array(
				'name'=>'用户设置',
				'url'=>U('S/Fxs/infoSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			if($data['userpass']){
				$data['userpass']=md5($data['userpass']);
			}else{
				unset($data['userpass']);
			}
			if($id){
				//保存时判断
				$re=$m->save($data);
				if(FALSE!==$re){
					//更新新老父级，暂不做错误处理
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				//处理下线
				$info['status']=0;
				$info['msg']='ID参数不存在！';
			}
			$this->ajaxReturn($info);
		}else{
			//处理编辑界面
			$cache=$m->where('id='.$id)->find();
			$this->assign('cache',$cache);	
			$this->display();
		}
		
	}

	//S后台我的佣金详情
	public function yj(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的分销中心',
				'url'=>U('S/Fxs/#')
			),
			'1'=>array(
				'name'=>'我的佣金详情',
				'url'=>U('S/Fxs/yj')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));		
		$status=I('status');
		if($status || $status=='0'){
			$map['status']=$status;
		}
		$this->assign('status',$status);
		//绑定搜索条件与分页
		$m=M('Fxs_syslog');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
			$stype=I('stype')?I('stype'):'';
		if($stype==1){
				if($name){
					$map['from']=array('eq',"$name");
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
		$map['to']=self::$S['uid'];
		$psize=self::$S['set']['pagesize']?self::$S['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		//dump(self::$S['uid']);
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '我的佣金详情','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}

	//CMS后台Vip提现订单
	public function txorder(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的分销中心',
				'url'=>U('S/Fxs/#')
			),
			'1'=>array(
				'name'=>'我的提现订单',
				'url'=>U('S/Fxs/txorder')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));		
		$status=I('status');
		if($status || $status=='0'){
			$map['status']=$status;
		}
		$this->assign('status',$status);
		//绑定搜索条件与分页
		$m=M('Fxs_tx');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			//提现人姓名
			$map['txname']=$name;
			$this->assign('name',$name);
		}
		$map['sid']=self::$S['uid'];
		$psize=self::$S['set']['pagesize']?self::$S['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('id desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '我的提现订单','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	//S后台申请体现
	public function tx(){
		$uid=$_SESSION['S']['uid'];
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的分销中心',
				'url'=>U('S/Fxs/#')
			),
			'1'=>array(
				'name'=>'我的提现订单',
				'url'=>U('S/Fxs/txorder')
			),
			'2'=>array(
				'name'=>'申请提现',
				'url'=>U('S/Fxs/tx')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));		
		$m = M('Fxs_user');
		$fxs=$m->where('id='.$uid)->find();
		if(!$fxs){
			die('未获取分销商信息，请重新尝试！');
		}
		$this->assign('fxs',$fxs);
		if (IS_POST) {
			
			$mtx=M('Fxs_tx');
			$post = I('post.');
			if(!$post['txprice']){
				$info['status']=0;
				$info['msg']='提现金额不能为空！';
				$this->ajaxReturn($info);
			}
			if($post['txprice']<self::$S['set']['tx_money']){
				$info['status']=0;
				$info['msg']='提现金额不得少于'.self::$S['set']['tx_money'].'元！';
				$this->ajaxReturn($info);
			}
			
			if($post['txprice']>$fxs['money']){
				$info['status']=0;
				$info['msg']='您的余额不足！';
				$this->ajaxReturn($info);
			}
			$fxs['money']=$fxs['money']-$post['txprice'];
			$rfxs=$m->save($fxs);
		
			if(FALSE!==$rvip){
				$post['sid']=$fxs['id'];
				$post['txsqtime']=time();
				$post['status']=1;
				$r=$mtx->add($post);
				if($r){
					$info['status']=1;
					$info['msg']='提现成功！系统会在3个工作日内处理您的提现申请！';
				}else{
					$info['status']=0;
					$info['msg']='提现失败！请立刻联系客服！';
				}
			}else{
				$info['status']=0;
				$info['msg']='提现失败！请立刻联系客服！';
			}
			$this->ajaxReturn($info);
		} else {
  			$this->display();
		}
	}
	
	public function txorderExport() {
		$id=I('id');
		$status=I('status');
		if ($id) {
			$map['id']=array('in',$id);
		} else {
			$map['status']=$status;
		}
		$map['sid']=self::$S['uid'];
		switch ($status) {
				case 0:$tt="提现失败";break;
				case 1:$tt="新申请";break;
				case 2:$tt="提现完成";break;
		}
		$data=M('Fxs_tx')->where($map)->select();
		foreach($data as $k=>$v){
			switch ($v['status']) {
				case 0:$data[$k]['status']="提现失败";break;
				case 1:$data[$k]['status']="新申请";break;
				case 2:$data[$k]['status']="提现完成";break;
			}
			$data[$k]['txsqtime']=date('Y-m-d H:i:s',$v['txsqtime']);
			$data[$k]['txtime']=$v['txtime']?date('Y-m-d H:i:s',$v['txtime']):'未执行';
		}
		$title=array('ID','分销商ID','提现金额','提现姓名','提现电话','提现银行','提现分行','提现银行所在地','提现银行卡卡号','提现申请时间','提现完成时间','订单状态');
		$this->exportexcel($data,$title,$tt.'订单'.date('Y-m-d H:i:s',time()));
	}
	
	/**
	 * 导出数据为excel表格
	 *@param $data    一个二维数组,结构如同从数据库查出来的数组
	 *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
	 *@param $filename 下载的文件名
	 *@examlpe
	 $stu = M ('User');
	 $arr = $stu -> select();
	 exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
	 */
	private function exportexcel($data = array(), $title = array(), $filename = 'report') {
		header("Content-type:application/octet-stream");
		header("Accept-Ranges:bytes");
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=" . $filename . ".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		//导出xls 开始
		if (!empty($title)) {
			foreach ($title as $k => $v) {
				$title[$k] = iconv("UTF-8", "GB2312", $v);
			}
			$title = implode("\t", $title);
			echo "$title\n";
		}
		if (!empty($data)) {
			foreach ($data as $key => $val) {
				foreach ($val as $ck => $cv) {
					$data[$key][$ck] = iconv("UTF-8", "GB2312", $cv);
				}
				$data[$key] = implode("\t", $data[$key]);

			}
			echo implode("\n", $data);
		}

	}
	
}