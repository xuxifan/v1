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
namespace Cms\Controller;
use Cms\Controller\BaseController;
class GgkController extends BaseController {
	
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
				'name'=>'大转盘',
				'url'=>U('Cms/Ggk/index')
			)
		);
    	$this->display();
    }

	
    //CMS后台  活动列表
	public function dzp(){

		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'微活动',
				'url'=>U('Cms/Ggk/index')
			),
			'1'=>array(
				'name'=>'活动列表',
				'url'=>U('Cms/Ggk/dzp')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('ggk');
		$type=M('Ggk');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		//$map['uid']=self::$UID;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cate0=$m->where($map)->page($p,$psize)->select();
		foreach ($cate0 as $k=>$v) {
			
			//$where['uid']=self::$UID;
			$where['id']=$v['cjmode'];
			$acname=$type->where($where)->find();
			$cate0[$k]['acname']=$acname['name'];
		}

		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '大转盘设置','Joel-search');
		$this->assign('cache',$cate0);		
		$this->display();
	}
	
	//CMS后台活动列表设置
	public function dzpset(){
		$m=M('ggk');
		$id=I('id');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'微活动',
				'url'=>U('Cms/Ggk/index')
			),
			'1'=>array(
				'name'=>'活动列表',
				'url'=>U('Cms/Ggk/dzp')
			),
			'2'=>array(
				'name'=>'活动列表 编辑',
				'url'=>U('Cms/Ggk/dzpset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			$data=I('post.');
		    $data['stime']=strtotime($data['stime']);
			$data['etime']=strtotime($data['etime']);
			$data['jstime']=strtotime($data['jstime']);
			$data['kstime']=strtotime($data['kstime']);
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
				//$data['uid']=self::$UID;
				$data['ctime']=time();
				$old=$m->add($data);
//				echo $m->getLastSql();die;
				if($old){
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！系统配置表不存在！';
				}
			}
			
			$this->ajaxReturn($info);
		}
		//处理编辑界面
		if($id){			
			$cache=$m->where('id='.$id)->find();
		}else{
			$cache['stime'] = time();	
			$cache['etime'] = time();	
			$cache['jstime'] = time();	
			$cache['kstime'] = time();	
		}
		// 输出预计人数
		$actype=M("active")->select();
		$this->assign('actype',$actype);
		// 输出预计人数
		$vip_num=M("Vip")->count();
		$this->assign('vip_num',$vip_num);
		$this->assign('cache',$cache);	
		$this->display();
	}
	
	//活动列表
	public function dzpDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('ggk');
//		if(!$id){
//			$info['status']=0;
//			$info['msg']='ID不能为空!';
//			$this->ajaxReturn($info);
//		}
		if(!$id){
			$re=$m->where('1=1')->delete();
		}else{
			$re=$m->delete($id);
		}
		if($re){
			$info['status']=1;
			$info['msg']='删除成功!';
		}else{
			$info['status']=0;
			$info['msg']='删除失败!';
		}
		$this->ajaxReturn($info);
	}
	
    public function prize(){

     	$bread=array(
			'0'=>array(
				'name'=>'大转盘',
				'url'=>U('Cms/Ggk/index')
			),
			'1'=>array(
				'name'=>'奖品设置',
				'url'=>U('Cms/Ggk/prize')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		// 增加搜索分页
		$act=M("ggk");
		$Ggk=$act->select();
		$this->assign("active",$active);
		// 遍历奖品
		$prize=M("ggk_prize");
		//$map['uid']=self::$UID;
		$pname=I('pname')?I('pname'):'';
		if($pname){
			$map['pname']=array('like',"%".$pname."%");
		}
		$s_active=I("s_active");
		if($s_active){
			$map['dzpid']=array('eq',$s_active);
		}
		$p=$_GET['p']?$_GET['p']:1;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$all_prize=$prize->where($map)->page($p,$psize)->select();
		$count=$prize->where($map)->count();
		$arr=array('1'=>"一等奖",
				   '2'=>"二等奖",
				   '3'=>"三等奖",
				   '4'=>"四等奖",
				   '5'=>"五等奖",
				   '6'=>"六等奖",
				   '7'=>"七等奖",
				   );
		$dzp=M("ggk");
		// dzparr保存大转盘数据
		$status=array(0=>"关闭",1=>"开启");
		$dzparr=array();
		foreach($all_prize as $k=>$v){
			$all_prize[$k]['level']=$arr[$v['level']];
			if(!array_key_exists($v['dzpid'],$dzparr)){
				$dzparr[$v['dzpid']]=$dzp->where("id=".$v['dzpid'])->find();
			}
			$all_prize[$k]['dzpname']=$dzparr[$v['dzpid']]['name'];
			$all_prize[$k]['ifcontrol']=$status[$dzparr[$v['dzpid']]['ifcontrol']];
		}
		// dump($s_active);
		$this->assign("s_active",$s_active);
		$this->assign("pname",$pname);
		$this->getPage($count, $psize, 'Joel-loader', '奖品设置','Joel-search');
		$this->assign('cache',$all_prize);
    	$this->display();
    }

    public function prizeset(){
    	$bread=array(
			'0'=>array(
				'name'=>'大转盘',
				'url'=>U('Cms/Ggk/index')
			),
			'1'=>array(
				'name'=>'奖品设置',
				'url'=>U('Cms/Ggk/prize')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$id=I('id');
		if(IS_POST){
			$data=I('post.');
			$prize=M("ggk_prize");
			$data['ctime']=time();
			if($data['id']){
				$re=$prize->save($data);
			}else{
				//$data['uid']=self::$UID;
				$re=$prize->add($data);
			}
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		}
		//奖品的顶部设置
		$dzp=M("ggk")->select();
		$p=M("ggk_prize");
		$pr=$p->where('id='.$id)->find();
		$this->assign('cache',$pr);
		$this->assign("dzp",$dzp);
    	$this->display();
    }

    public function prizeDel(){
    	$id=$_GET['id'];
		$m=M('ggk_prize');
    	if(!$id){
			$re=$m->where('1=1')->delete();
		}else{
			$re=$m->delete($id);
		}
		
    	if(FALSE!==$re){
			$info['status']=1;
			$info['msg']='删除成功！';
		}else{
			$info['status']=0;
			$info['msg']='删除失败！';
		}
		$this->ajaxReturn($info);
    }

    //用户操作写入日志数据库
//	public function dzplog(){
//		$l=M('ggk_log');
//		$d=M('dzp');
////		$data['dzpid']=$d->where('id='.$id)->find();
//		$data['dzpid']=$dzpid;
//		$data['openid']=$_SESSION['sqopenid'];
////		$data['uid']=$d->where('uid='.$uid)->find();
//		$data['uid']=$uid;
//		$data['ip']=get_client_ip();
//		$data['ctime']=time();
//		$data['result']=$result;
//		$m->add($data);
//	}
	
	//抽奖日志
	public function dzplog(){

		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'微活动',
				'url'=>U('Cms/Ggk/dzplog')
			),
			'1'=>array(
				'name'=>'中奖纪录',
				'url'=>U('Cms/Ggk/dzplog')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('ggk_log');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['vipid']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		//$map['uid']=self::$UID;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:10;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '中奖纪录','Joel-search');
		$this->assign("name",$name);
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//中奖纪录
	public function zjlog(){

		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'微活动',
				'url'=>U('Cms/Ggk/zjlog')
			),
			'1'=>array(
				'name'=>'中奖纪录',
				'url'=>U('Cms/Ggk/zjlog')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('ggk_zj');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['vipid']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		//$map['uid']=self::$UID;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '中奖纪录','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	public function zjloginfo(){
		$m=M('ggk_zj');
		$datas=M('ggk_acform_data');
		$field=M('Acform_fields');
		$id=I('id');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'微活动',
				'url'=>U('Cms/Ggk/zjlog')
			),
			'1'=>array(
				'name'=>'中奖纪录',
				'url'=>U('Cms/Ggk/zjloginfo')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));

		//处理编辑界面
		if($id){			
			$cache=$m->where('id='.$id)->find();

			$openid=$cache['vipopenid'];
			//$where['uid']=self::$UID;
			$where['dzpid']=$cache['dzpid'];
			$list=$datas->where($where)->select();
			
			for($i=2;$i<count($list);$i++){

				//$fwh['uid']=self::$UID;
				$fwh['field']=$list[$i]['value'];
				$re=$field->where($fwh)->getField('name');
				$list[$i]['finame']=$re;
			}
		}
		//dump($list);
		$this->assign('list',$list);	
		$this->assign('cache',$cache);	
		$this->display();
	}

	
	//手动派奖
	public function sdpj(){

		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'微活动',
				'url'=>U('Cms/Ggk/sdpj')
			),
			'1'=>array(
				'name'=>'手动派奖',
				'url'=>U('Cms/Ggk/sdpj')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('ggk_vip');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['dzpid']=array('like',"%$name%");
			$this->assign('name',$name);
		}
	//	$map['uid']=self::$UID;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '中奖纪录','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	public function sdpjset(){
		//$uid=self::$UID;
    	$bread=array(
			'0'=>array(
				'name'=>'大转盘',
				'url'=>U('Cms/Ggk/index')
			),
			'1'=>array(
				'name'=>'奖品设置',
				'url'=>U('Cms/Ggk/prize')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$prize=M("ggk_vip");
		$vip=M("vip");
		$store=M("ggk_prize");
		$id=I('id');
		if(IS_POST){
			$map['openid']=I('openid');
			unset($_POST['nname']);
			unset($_POST['prival']);
			$data=I('post.');
			$id=$data['nickname'];
			$data['ctime']=time();
			$prizeid=$data['prizeid'];
			$data['lname']=$store->where(array('id'=>$prizeid))->getField('lname');
			if($data['id']){
				$sell=$prize->where(array('id'=>$data['id']))->find();
				if($sell['prizeid']!=$data['prizeid']){
					$store->where(array('id'=>$data['prizeid']))->setInc('sell');
					$store->where(array('id'=>$sell['prizeid']))->setDec('sell');
				}
				$re=$prize->save($data);
			}else{
				
				$re=M("Ggk_vip")->where($map)->find();
				if($re){
					$info['status']=0;
					$info['msg']='已配置过该用户！';
					$this->ajaxReturn($info);
				}
				//减少库存 增加发送奖品量
				$store->where(array('id'=>$data['prizeid']))->setInc('sell');
				$data['status']=2;
				$re=$prize->add($data);
			}
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		}
		
		$p=M("ggk_vip");
		$pr=$p->where('id='.$id)->find();
		$this->assign('cache',$pr);
		
		//大转盘选项
		$pzds=M('ggk')->where(array('status'=>'1'))->select();
		$this->assign('pzds',$pzds);
		
		//奖项设置
		$ma['dzpid']=$pr['dzpid'];
		//$ma['uid']=$uid;
		$ma['store']=array('neq','0');
		$prize=M('ggk_prize')->where($ma)->select();
		$this->assign('prize',$prize);
		
		$asd=$vip->where(array('openid'=>$pr['openid']))->find();
		$this->assign('asd',$asd);
		
    	$this->display();
    }

	//查询昵称
	public function searchNickname(){
		if(IS_POST){
			$name=$_POST['nname'];
			if($name){
				$map['nickname']=array('like',"%".$name."%");
			}
			$dzpvip=M('zjd_vip');
			$dzpzj=M('zjd_zj');
			$vip=M('vip');

			//昵称 OPENID
			$vips=$vip->select();
			$item="";
			for($i=0;$i<count($vips);$i++){
				
				//查询dzp_vip,是否手动派过奖并中奖
				$openid=$dzpvip->where(array('openid'=>$vips[$i]['openid']))->select();
			    for($j=0;$j<count($openid);$j++){
					$item.=$openid[$j]['openid'].",";
				}
				
				//查询dzp_zj,判断有没有中过奖
				$zjopenid=$dzpzj->where(array('vipopenid'=>$vips[$i]['openid']))->select();
				for($q=0;$q<count($zjopenid);$q++){
					$item.=$openid[$q]['openid'].",";
				}
			}
			
			$map['openid']=array('not in',$item);
			$chche=$vip->where($map)->field('openid,nickname')->select();
			$this->ajaxReturn($chche);
		}
	}

	
	//二级联动
	public function getprize(){
		
		$cid=$_POST['cid'];
		
		$ma['dzpid']=$cid;
		//$ma['uid']=self::$UID;
		$ma['store']=array('neq','0');
		$pzds=M('ggk_prize')->where($ma)->select();
		$this->ajaxReturn($pzds);
	}

	//以昵称来查询openid
	public function getopenid(){
		
		$cid=$_POST['cid'];
		$ma['id']=$cid;
	//	$ma['uid']=self::$UID;
		$pzds=M('vip')->where($ma)->getField('openid');
		$this->ajaxReturn($pzds);
	}
	
    public function sdpjDel(){
    	$id=$_GET['id'];
    	$map['id']=array('in',$id);
    	$re=M('ggk_vip')->where($map)->delete();
    	if(FALSE!==$re){
			$info['status']=1;
			$info['msg']='删除成功！';
		}else{
			$info['status']=0;
			$info['msg']='删除失败！';
		}
		$this->ajaxReturn($info);
    }
	
	//活动表单设置
	public function acformList(){

		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'活动表单管理',
				'url'=>U('Cms/Form/index')
			),
			'1'=>array(
				'name'=>'活动表单列表',
				'url'=>U('Cms/Form/formList')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('ggk_acform');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['dzpid']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		//$map['uid']=self::$UID;
		//$map['isform']='1';
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '活动表单列表','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	public function acformSet(){
		$id=I('id');
		$m=M('ggk_acform');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'万能表单管理',
				'url'=>U('Cms/Ggk/index')
			),
			'1'=>array(
				'name'=>'万能表单列表',
				'url'=>U('Cms/Ggk/acformList')
			),
			'2'=>array(
				'name'=>'万能表单设置',
				'url'=>$id?U('Cms/Ggk/acformSet',array('id'=>$id)):U('Cms/Ggk/acformSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			$data=I('post.');
			//$data['uid']=self::$UID;
			//修改万能表单密码
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
				$data['ctime']=time();
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
		//活动
		$active=M("ggk")->select();
		$this->assign('active',$active);
		
		//字段
		$field=M("Acform_fields")->select();
		$this->assign('field',$field);		
		
		$this->display();
	}
	
	public function acformDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('ggk_acform');
		if(!$id){
			$info['status']=0;
			$info['msg']='ID不能为空!';
			$this->ajaxReturn($info);
		}
		$rq=M('ggk_acform_data')->where(array('formid'=>$id))->find();
		if($rq){
			$info['status']=0;
			$info['msg']='此表单已有数据，不允许删除!';
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
	
	//判断此活动有没有表单
	public function getishd(){
		$val=$_POST['val'];
		$form=M('ggk_acform');
		//$where['uid']=self::$UID;
		$where['dzpid']=$val;
		$as=$form->where($where)->find();
		if($as){
			$info['status']='0';
			$info['msg']='此活动已有表单';
		}else{
			$info['status']='1';
			$info['msg']='此活动没有表单';
		}
		$this->ajaxReturn($info);
	}
	
	/////////////////////////////////////////////////
	//万能表单字段处理逻辑
	public function acformField(){
		
		//$dzpid=I('dzpid');
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'活动表单管理',
				'url'=>U('Cms/Form/index')
			),
			'1'=>array(
				'name'=>'活动表单列表',
				'url'=>U('Cms/Form/formList')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Acform_fields');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		//$map['dzpid']=$dzpid;
	//	$map['uid']=self::$UID;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '活动表单列表','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	public function acfieldDel(){

    	$id=$_GET['id'];
    	$map['id']=array('in',$id);
    	$re=M('Acform_fields')->where($map)->delete();
		//echo M('Acform_fields')->getLastSql();die;
    	if(FALSE!==$re){
			$info['status']=1;
			$info['msg']='删除成功！';
		}else{
			$info['status']=0;
			$info['msg']='删除失败！';
		}
		$this->ajaxReturn($info);
    }
	
	//数据
	public function acformdata(){
		$id =I('id');
		$dzpid =I('dzpid');
		$mdate =M('ggk_ggk_acform_data');
		$acfield=M("Acform_fields");
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'数据列表管理',
				'url'=>U('Cms/Ggk/index')
			),
			'1'=>array(
				'name'=>'数据列表',
				'url'=>U('Cms/Ggk/acformdata')
			),
			
		);
		$this->assign('breadhtml',$this->getBread($bread));
		
		//$map['uid']=self::$UID;
		$map['dzpid']=$dzpid;

		//数据列表
		$cache=$mdate->where($map)->distinct(true)->field('dzpid,openid')->select();
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$count=$mdate->where($map)->count('distinct openid');//去除重复数据的总数
		$this->getPage($count, $psize, 'Joel-loader', '活动表单列表','Joel-search');
		$this->assign('cache',$cache);	
		$this->assign('ids',$ids);		
		$this->display();
		
	}

	public function acformdatainfo(){

		$datas=M('ggk_ggk_acform_data');
		$field=M('Acform_fields');
		$dzpid=I('dzpid');
		$openid=I('openid');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'微活动',
				'url'=>U('Cms/Ggk/zjlog')
			),
			'1'=>array(
				'name'=>'活动列表',
				'url'=>U('Cms/Ggk/acformdata')
			),
			'2'=>array(
				'name'=>'活动数据列表',
				'url'=>U('Cms/Ggk/acformdatainfo')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));

		//处理编辑界面
		if($dzpid){			

			$openid=$openid;
			//$where['uid']=self::$UID;
			$where['dzpid']=$dzpid;
			$list=$datas->where($where)->select();
			//echo $datas->getLastSQl();
			for($i=2;$i<count($list);$i++){

				//$fwh['uid']=self::$UID;
				$fwh['field']=$list[$i]['value'];
				$re=$field->where($fwh)->getField('name');
				$list[$i]['finame']=$re;
			}
			$cache=$datas->where($where)->distinct(true)->field('dzpid,openid')->select();
		}
		//dump($cache);
		$this->assign('list',$list);
		$this->assign('cache',$cache);	
		$this->display();
	}


	
	//自定义字段Loader
	public function formDiyFieldLoader(){
		$findback=I('fbid');
		$this->assign('findback',$findback);
		$this->assign('diy',I('count'));
		$this->ajaxReturn($this->fetch());
	}
	
	
	//标准字段Loader
	public function formFieldLoader(){
		$m=M('Acform_fields');
		$findback=I('fbid');
		$this->assign('findback',$findback);
		$map['id']=array('not in',I('ids'));
		$cache=$m->where($map)->select();
		$this->assign('cache',$cache);		
		$this->ajaxReturn($this->fetch());
	}
	
	//保存字段
	public function formFieldGethtml(){
		
//		$cache['id']=I('id');
//		$cache['ftype']=I('ftype');
//		$cache['field']=I('field');
//		$cache['name']=I('name');
//		$cache['type']=I('type');
//		$cache['place']=I('place');
//		$cache['value']=I('value');
//		$this->assign('cache',$cache);
//		$mb=$this->fetch();
//		$this->ajaxReturn($mb);
		
		$cache['name']=$_POST['name'];
		$cache['fields']=$_POST['field'];
		$cache['type']=$_POST['type'];
		$cache['place']=$_POST['place'];
		$cache['value']=$_POST['value'];
		$cache['status']=$_POST['status'];
		$this->assign('cache',$cache);
		
		$mb=$this->fetch();
		//$this->ajaxReturn($mb);
		
		//$cache['uid']=self::$UID;
		$fields=M('Acform_fields');
		$re = $fields->add($cache);
		if($re){
			$info['status']='1';
			$info['msg']='添加成功';
			$info['mb']=$mb;
		}else{
			$info['status']='0';
			$info['msg']='添加失败';
			$info['mb']=$mb;
		}
		$this->ajaxReturn($info);

		//$cache['id']=I('id');
		//$cache['ftype']=I('ftype');
//		$cache['name']=I('name');
//		$cache['field']=I('field');
//		
//		$cache['type']=I('type');
//		$cache['place']=I('place');
//		$cache['value']=I('value');
//		$cache['status']=I('status');
//		$this->assign('cache',$cache);
//		$mb=$this->fetch();
//		$this->ajaxReturn($mb);
	}
	
	//导出
	public function orderExport() {
		$id=I('id');
		$status=I('status');
		if ($id) {
			$map['id']=array('in',$id);
		} 
		//$map['uid']=self::$UID;
		
		$data=M('ggk_zj')->where($map)->select();

		foreach($data as $k=>$v){
			//过滤字段
		//unset($data[$k]['uid']);
			unset($data[$k]['prizeid']);
			$data[$k]['ctime']=date('Y-m-d H:i:s',$v['ctime']);
		}

		$title=array('ID','大转盘编号','会员ID','昵称','OPENID','奖项名称','真实名称','手机号','地址','记录时间','IP','SN' );
		$this->exportexcel($data,$title,$tt.'中奖纪录'.date('Y-m-d H:i:s',time()));
	}


/**
	 * 导出数据为excel表格
	 *@param $data    一个二维数组,结构如同从数据库查出来的数组
	 *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
	 *@param $filename 下载的文件名
	 *@examlpe
	 *$stu = M ('User');
	 *$arr = $stu -> select();
	 *exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
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