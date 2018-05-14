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
class ShopController extends BaseController {
	
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
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			)
		);
    	$this->display();
    }

// 作者：郑伊凡 2016-2-15 母版本 功能：首页自定义模版设置
    public function indexset(){
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'营销功能',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'模板设置',
				'url'=>U('Cms/Shop/indexset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$m=M('Shop_set');
		if(IS_POST){
			$data=I('post.');
			$re=$m->where('id=1')->save($data);
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		}
		$cache=$m->field("istpl,tplid")->find();
		$this->assign('cache',$cache);
    	$this->display();
    }
// 作者：郑伊凡 2016-2-15 母版本 功能：首页自定义模版设置

	//CMS后台门店设置
	public function set(){
		$id=I('id');
		$m=M('Shop_set');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'商城管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商城设置',
				'url'=>U('Cms/Shop/set')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		
		$tplist =M('Shop_tpl')->where('type=1')->select();
		foreach($tplist as $k =>$val){
			$tplist[$k]['picurl']=$this->getPic($val['pic']);
		}
		$this->assign('tpllist',$tplist);
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			$old=$m->where('id='.$id)->find();
			if($old){
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
		$cache=$m->where('id=1')->find();
		$this->assign('cache',$cache);
		$this->display();
	}
	
//拼团购设置	 xxf 2016-1-21 11:00
	public function Ptgset(){

		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'拼团购设置',
				'url'=>U('Cms/Shop/ptgset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$m=M("Shop_set");
		// 作者：郑伊凡 2016-1-25 母版本 功能：拼团购总控前台显示遍历
		$cache=$m->field("id,isptg,ptgname,ptgmsg")->find();
		// 作者：郑伊凡 2016-1-25 母版本 功能：拼团购总控前台显示遍历
		//处理POST提交	
		if(IS_POST){
			$data=I("post.");
			$data['id']=$cache['id'];
			$re=$m->where($map)->save($data);
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		}
		$this->assign("cache",$cache);
		$this->display();
	}
	
	
	////zxg    2016.2.18   拼团购列表页面
	public function ptglist(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/ptglist')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		
		//先判断是否开启拼团购按钮
		$isptg = M('Shop_set')->getField('isptg');
		$this->assign('isptg',$isptg);
		//绑定搜索条件与分页
		$m=M('Shop_goods');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$map['isgroup']='1';
		$map['status']='1';
		$map['iscut']='0';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $k=>$v){
			$listpic=$this->getPic($v['listpic']);
			$cache[$k]['imgsrc']=$listpic['imgurl'];
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '拼团购管理','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}

	/* * * * * *
	 * 拼团购的订单统计
	 * 作者：郑伊凡
	 * 时间：2016/3/8
	 */
	public function ptgorder(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'拼团购订单',
				'url'=>U('Cms/Shop/ptgorder')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		if($name){
			$map['vipid']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$m=M("Ptg_log");
		$cache=$m->where($map)->order('ctime desc')->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '拼团购订单','Joel-search');
		$this->assign('cache',$cache);
		$this->display();
	}

	public function showptgorder(){
		$ptgid=I('pid');
		$m=M('Ptg_order');
		$map=array(
					"ptgid"=>$ptgid,
					);
		$cache=$m->where($map)->order('ctime desc')->select();
		$o=M('Shop_order');
		foreach($cache as $k=>$v){
			$cache[$k]['status']=$o->where(array('oid'=>$v['oid']))->getField('status');
		}
		$this->assign('cache',$cache);
		$this->display();
	}

	public function ptgorderover(){
		if(IS_AJAX){
			$id=I('id');
			$m=M("Ptg_log");
			$re=$m->where('id='.$id)->setField('status',2);
			if($re!==false){
				$info['status']=1;
				$info['msg']="操作成功";
			} else {
				$info['status']=0;
				$info['msg']="操作失败";
			}
			$this->ajaxReturn($info);
		}
	}

	public function searchGoods(){
		if(IS_POST){
			$name=$_POST['nname'];
			if($name){
				$map['name']=array('like',"%".$name."%");
			}
			$map['status']='1';
			$map['iscut']='0';
			$map['isgroup']='0';
			$map['isteg']='0';
			$map['isyyb']='0';
			$data=M('Shop_goods')->where($map)->select();
			$this->ajaxReturn($data);
		}
	}
	
	
	public function ptglistset(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'拼团购列表管理',
				'url'=>U('Cms/Shop/ptglistset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		if (IS_POST) {
			unset($_POST['nname']);
			$data=$_POST;
			$m=M("Shop_goods");
			if ($data['goodsid']) {
				$wap['id']=$data['goodsid'];
				$where['isgroup']='1';
				$where['groupmax']=$data['groupmax'];
				$where['groupprice']=$data['groupprice'];
				$where['listpic']=$data['listpic'];
				$re = $m->where($wap)->save($where);
				if($re !== false) {
					$info['status'] = 1;
					$info['msg'] = '设置团购产品成功';
				} else {
					$info['status'] = 0;
					$info['msg'] = '设置团购产品失败';
				}
			} 
			$this->ajaxReturn($info);
		}
		if(I('id')){
			$cache=M("Shop_goods")->where("id=".I('id'))->find();
			$this->assign('cache',$cache);
		}
		$this->display();
	}

	public function delptgs(){
		$id=$_GET['id'];
		$id=trim($id,',');
    	$map['id']=array('in',$id);
		$goods=M('Shop_goods');
		$mapsss['isgroup']='0';
		$mapsss['groupmax']='0';
		$mapsss['groupprice']='0';
    	$re=$goods->where($map)->save($mapsss);
    	if(FALSE!==$re){
			$info['status']=1;
			$info['msg']='删除成功！';
		}else{
			$info['status']=0;
			$info['msg']='删除失败！';
		}
		$this->ajaxReturn($info);
	}
	//查看拼团进度(发起者)
	public function showptgs(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'拼团购设置',
				'url'=>U('Cms/Shop/ptglist')
			),
			'2'=>array(
				'name'=>'查看拼团购发起者',
				'url'=>U('Cms/Shop/showptgs')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$pid=I('pid');
		//绑定搜索条件与分页
		$m=M('ptg_log');
		$vip=M('vip');
		$p=$_GET['p']?$_GET['p']:1;
		$map['goodsid']=$pid;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $v=>$k){
			$list=$vip->where(array('id'=>$k['vipid']))->find();
			$cache[$v]['vip']=$list;
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '拼团购参与人员','Joel-search');
		$this->assign('cache',$cache);	
		$this->assign('pid',$pid);		
		$this->display();
		
	}
	
	//查看参与人员
	public function showptgmem(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'拼团购设置',
				'url'=>U('Cms/Shop/ptglist')
			),
			'2'=>array(
				'name'=>'查看拼团购发起者',
				'url'=>U('Cms/Shop/showptgs')
			),'2'=>array(
				'name'=>'查看拼团购参与人员',
				'url'=>U('Cms/Shop/showptgmem')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$ptg=I('id');
		//绑定搜索条件与分页
		$m=M('ptg_order');
		$vip=M('vip');
		$p=$_GET['p']?$_GET['p']:1;
		$map['ptgid']=$ptg;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $v=>$k){
			$list=$vip->where(array('id'=>$k['vipid']))->find();
			$cache[$v]['vip']=$list;
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '拼团购参与人员','Joel-search');
		$this->assign('cache',$cache);
		$this->assign('ptg',$ptg);		
		$this->display();
	}
	////zxg    2016.2.18   拼团购列表页面
	
	//1.20 zxg 聚友杀功能
	public function jysset(){

		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'聚友杀',
				'url'=>U('Cms/Shop/set')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$m=M("Shop_set");
		// 作者：郑伊凡 2016-1-25 母版本 功能：聚友杀总控前台显示遍历
		$cache=$m->field("id,isjys,jysname,jysmsg")->find();
		// 作者：郑伊凡 2016-1-25 母版本 功能：聚友杀总控前台显示遍历
		//处理POST提交	
		if(IS_POST){
			$data=I("post.");
			$da['id']=$cache['id'];
			$re=$m->where($da)->save($data);
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		}
		$this->assign("cache",$cache);
		$this->display();
	}
	/////聚友杀    张旭光   2016.2.19
	public function jyslist(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/jyslist')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		
		//先判断是否开启拼团购按钮
		$isjys = M('Shop_set')->getField('isjys');
		$this->assign('isjys',$isjys);
		//绑定搜索条件与分页
		$m=M('Shop_goods');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$map['isgroup']='0';
		$map['status']='1';
		$map['iscut']='1';
		
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $k=>$v){
			
			$listpic=$this->getPic($v['listpic']);
			$cache[$k]['imgsrc']=$listpic['imgurl'];
		}
		
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '聚友杀管理','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	public function jyslistset(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'聚友杀管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'聚友杀列表管理',
				'url'=>U('Cms/Shop/jyslistset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		if (IS_POST) {
			unset($_POST['nname']);
			$data=$_POST;
			$m=M("Shop_goods");
			if ($data['goodsid']) {
				$wap['id']=$data['goodsid'];
				$where['iscut']='1';
				$where['cutmax']=$data['cutmax'];
				$where['cutlow']=$data['cutlow'];
				$where['cuttop']=$data['cuttop'];
				$where['listpic']=$data['listpic'];
				$re = $m->where($wap)->save($where);
				if($re !== false) {
					$info['status'] = 1;
					$info['msg'] = '设置聚友杀产品成功';
				} else {
					$info['status'] = 0;
					$info['msg'] = '设置聚友杀产品失败';
				}
			} 
			$this->ajaxReturn($info);
		}
		if(I('id')){
			$cache=M("Shop_goods")->where("id=".I('id'))->find();
			$this->assign('cache',$cache);
		}
		$this->display();
	}

	public function deljys(){
		$id=$_GET['id'];
		$id=trim($id,',');
    	$map['id']=array('in',$id);
		$goods=M('Shop_goods');
		$mapsss['iscut']='0';
		$mapsss['cutmax']='0';
		$mapsss['cutlow']='0';
		$mapsss['cuttop']='0';
    	$re=$goods->where($map)->save($mapsss);
    	if(FALSE!==$re){
			$info['status']=1;
			$info['msg']='删除成功！';
		}else{
			$info['status']=0;
			$info['msg']='删除失败！';
		}
		$this->ajaxReturn($info);
	}
	//查看聚友杀进度(发起者)
	public function showjys(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'聚友杀设置',
				'url'=>U('Cms/Shop/jyslist')
			),
			'2'=>array(
				'name'=>'查看聚友杀发起者',
				'url'=>U('Cms/Shop/showjys')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$pid=I('pid');
		//绑定搜索条件与分页
		$m=M('jys_log');
		$vip=M('vip');
		$p=$_GET['p']?$_GET['p']:1;
		$map['goodsid']=$pid;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $v=>$k){
			$list=$vip->where(array('id'=>$k['vipid']))->find();
			$cache[$v]['vip']=$list;
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '聚友杀参与人员','Joel-search');
		$this->assign('cache',$cache);	
		$this->assign('pid',$pid);		
		$this->display();
		
	}
	
	//查看参与人员
	public function showjysmem(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'拼团购管理',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'聚友杀设置',
				'url'=>U('Cms/Shop/jyslist')
			),
			'2'=>array(
				'name'=>'查看聚友杀发起者',
				'url'=>U('Cms/Shop/showjys')
			),'2'=>array(
				'name'=>'查看聚友杀参与人员',
				'url'=>U('Cms/Shop/showjysmem')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$ptg=I('id');
		//绑定搜索条件与分页
		$m=M('ptg_order');
		$vip=M('vip');
		$p=$_GET['p']?$_GET['p']:1;
		$map['ptgid']=$ptg;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $v=>$k){
			$list=$vip->where(array('id'=>$k['vipid']))->find();
			$cache[$v]['vip']=$list;
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '拼团购参与人员','Joel-search');
		$this->assign('cache',$cache);
		$this->assign('ptg',$ptg);		
		$this->display();
	}
	////聚友杀    张旭光	 2016.2.19

	///积分商城  zxg  2016.2.24
	public function integset(){

		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'积分商城',
				'url'=>U('Cms/Shop/inteset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$m=M("Shop_set");
		$cache=$m->field("id,isteg,integname,integmsg,dzpid,zjdid,ggkid")->find();
		//处理POST提交	
		if(IS_POST){
			$data=I("post.");
			$da['id']=$cache['id'];
			$re=$m->where($da)->save($data);
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		}
		$map=array('status'=>1);
		$dzp=M('Dzp')->where($map)->field('id,name')->select();
		$zjd=M('Zjd')->where($map)->field('id,name')->select();
		$ggk=M('Ggk')->where($map)->field('id,name')->select();
		$this->assign('dzp',$dzp);
		$this->assign('zjd',$zjd);
		$this->assign('ggk',$ggk);
		$this->assign("cache",$cache);
		$this->display();
	}
	public function integlist(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'积分商城',
				'url'=>U('Cms/Shop/inteset')
			),
			'2'=>array(
				'name'=>'积分商品列表',
				'url'=>U('Cms/Shop/inteset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		
		$isjys = M('Shop_set')->getField('isteg');
		$this->assign('isteg',$isjys);
		//绑定搜索条件与分页
		$m=M('Shop_goods');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$map['isgroup']='0';
		$map['status']='1';
		$map['iscut']='0';
		$map['isteg']='1';
		
		if($name){
			$map['gname']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $k=>$v){
			
			$listpic=$this->getPic($v['listpic']);
			$cache[$k]['imgsrc']=$listpic['imgurl'];
		}
		
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '积分商城管理','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	public function integlistset(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'积分商城',
				'url'=>U('Cms/Shop/inteset')
			),
			'2'=>array(
				'name'=>'积分商品列表',
				'url'=>U('Cms/Shop/integlistset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		if (IS_POST) {
			unset($_POST['nname']);
			$data=$_POST;
			$m=M("Shop_goods");
			if ($data['goodsid']) {
				$wap['id']=$data['goodsid'];
				$where['isteg']='1';
				$where['integpay']=$data['integpay'];
				$where['listpic']=$data['listpic'];
				$re = $m->where($wap)->save($where);
				if($re !== false) {
					$info['status'] = 1;
					$info['msg'] = '设置积分商品成功';
				} else {
					$info['status'] = 0;
					$info['msg'] = '设置积分商品失败';
				}
			} 
			$this->ajaxReturn($info);
		}
		if(I('id')){
			$cache=M("Shop_goods")->where("id=".I('id'))->find();
			$this->assign('cache',$cache);
		}
		$this->display();
	}

	public function delinteg(){
		$id=$_GET['id'];
		$id=trim($id,',');
    	$map['id']=array('in',$id);
		$goods=M('Shop_goods');
		$mapsss['isteg']='0';
		$mapsss['integpay']='0';
    	$re=$goods->where($map)->save($mapsss);
    	if(FALSE!==$re){
			$info['status']=1;
			$info['msg']='删除成功！';
		}else{
			$info['status']=0;
			$info['msg']='删除失败！';
		}
		$this->ajaxReturn($info);
	}
	///积分商城  zxg  2016.2.24
	
	
	//一元夺宝  zxg   2016.3.4  一元夺宝设置开关以及分享内容
	public function yydbset(){

		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'一元夺宝',
				'url'=>U('Cms/Shop/yydbset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$m=M("Shop_set");
		$cache=$m->field("id,isyydb,yydbname,yydbmsg,yydbimg")->find();
		//处理POST提交	
		if(IS_POST){
			$data=I("post.");
			$da['id']=$cache['id'];
			$re=$m->where($da)->save($data);
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		}
		$this->assign("cache",$cache);
		$this->display();
	}
	public function yydblist(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'一元夺宝商品列表',
				'url'=>U('Cms/Shop/yydblist')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		
		$isyydb = M('Shop_set')->getField('isyydb');
		$this->assign('isyydb',$isyydb);
		//绑定搜索条件与分页
		$m=M('yydb_goods');
		$yzj=M('yydb_zj');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';

		if($name){
			$map['gname']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $k=>$v){
			$yid = $v['id'];
			$iswin = $yzj->where(array('yid'=>$yid))->find();
			$cache[$k]['iswin']=$iswin;
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '一元夺宝商品管理','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	public function yydblisttset(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'一元夺宝商品列表',
				'url'=>U('Cms/Shop/yydblist')
			),
			'2'=>array(
				'name'=>'一元夺宝商品设置',
				'url'=>U('Cms/Shop/yydblistset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$type=$_GET['type'];
		$this->assign('type',$type);
		
		if (IS_POST) {
			unset($_POST['nname']);
			unset($_POST['vipname']);
			$data=$_POST;
			$m=M("shop_goods");
			$yybm=M("yydb_goods");
			
			$data['num']=$data['price']/$data['yprice'];
			if($data['id']) {
				$re = $yybm->where(array('id'=>$data['id']))->save($data);
				if($re !== false) {
					$info['status'] = 1;
					$info['msg'] = '设置一元夺宝商品成功';
				} else {
					$info['status'] = 0;
					$info['msg'] = '设置一元夺宝商品失败';
				}
			}else{
				$data['ctime']=time();
				$wap['id']=$data['goodsid'];
				$where['isyyb']='1';
				$re = $m->where($wap)->save($where);
				
				$res = $yybm->add($data);
				if($re !== false) {
					$info['status'] = 1;
					$info['msg'] = '设置一元夺宝商品成功';
				} else {
					$info['status'] = 0;
					$info['msg'] = '设置一元夺宝商品失败';
				}
			}
			$this->ajaxReturn($info);
		}
		//指定会员ID
		$vip=M('vip')->where()->select();
		$this->assign('vip',$vip);
		
		$this->assign('vip',$vip);
		
		if(I('id')){
			$cache=M("yydb_goods")->where("id=".I('id'))->find();
			$this->assign('cache',$cache);
		}
		$this->display();
	}

	public function delyydbs(){
		$id=$_GET['id'];
		$id=trim($id,',');
		$goodsid=$_GET['goodsid'];
    	
		$goods=M('Shop_goods');
		$mapsss['isyyb']='0';
		$mapyyb['id']=$goodsid;
    	$re=$goods->where($mapyyb)->save($mapsss);
		
		$map['id']=$id;
		$yybgoods=M('yydb_goods');
		$re=$yybgoods->where($map)->delete();
		
    	if(FALSE!==$re){
			$info['status']=1;
			$info['msg']='删除成功！';
		}else{
			$info['status']=0;
			$info['msg']='删除失败！';
		}
		$this->ajaxReturn($info);
	}
	
	//查询昵称  与拼团购 聚友杀 积分商城为互斥
	public function searchNickname(){
		if(IS_POST){
			$name=$_POST['nname'];
			if($name){
				$map['nickname']=array('like',"%".$name."%");
			}
			$vip=M('vip');
			$vips=$vip->select();
			$chche=$vip->field('id,nickname')->select();
			$this->ajaxReturn($chche);
		}
	}
	
	///夺宝信息
	public function yydblog(){
		
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'一元夺宝商品列表',
				'url'=>U('Cms/Shop/yydblist')
			),
			'2'=>array(
				'name'=>'一元夺宝商品设置',
				'url'=>U('Cms/Shop/yydblistset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$yid=$_GET['yid'];
		$ylog=M('yydb_log');
		$vip=M('vip');
		$opt['yid']=$yid;
		$yres = $ylog->where($opt)->field(array("count(vipid)"=>"countvip","id", "vipname", "vipopenid","paytime","payprice","vipmobile","vipaddress","vipid"))->group('vipid')->select();
		foreach($yres as $v=>$k){
			
			$nickname=$vip->where(array('id'=>$k['vipid']))->getField('nickname');
			$yres[$v]['nickname']=$nickname;
		}
		$this->assign('cache',$yres);
		$this->display();
	}
	
	//中奖人信息
	public function yydbzj(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'一元夺宝商品列表',
				'url'=>U('Cms/Shop/yydblist')
			),
			'2'=>array(
				'name'=>'一元夺宝商品设置',
				'url'=>U('Cms/Shop/yydblistset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$yid=$_GET['yid'];
		//商品信息
		$ygood=M('yydb_goods');
		$ygoods=$ygood->where(array('id'=>$yid))->find();
		$sycs=$ygoods['num']-$ygoods['sells'];
		$this->assign('sycs',$sycs);
		$this->assign('cache',$ygoods);
		
		//中奖人信息
		$yzj=M('yydb_zj');
		$yvip=$yzj->where(array('yid'=>$yid))->find();
		$this->assign('yvip',$yvip);
		$vcode=$yvip['code'];
		$oid=$yvip['oid'];
		$this->assign('vcode',$vcode);
		
		//购买人信息
		$order=M('shop_order')->where(array('id'=>$oid))->find();
		$this->assign('order',$order);
		
		//获取夺宝记录
		$ylog=M('yydb_log');
		$ylog=$ylog->where(array('yid'=>$yid))->select();
		$this->assign('ylog',$ylog);
		
		$this->display();
	}
	
	//订单列表
	public function yydborder(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'营销活动',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'一元夺宝订单列表',
				'url'=>U('Cms/Shop/yydborder')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		
		$status=I('status');
		if($status || $status=='0'){
			$map['status']=$status;
		}
		$this->assign('status',$status);
		
		//是否开启提取方式
		$iszt=M('express_set')->getField('iszt');
		$this->assign('iszt',$iszt);
		
		//绑定搜索条件与分页
		$m=M('Shop_order');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$this->assign('ordercg',$ordercg);
		if($name){
			//订单号邦定
			$map['oid']=array('eq',"$name");
			$map['vipid']=array('eq',"$name");
			$map['vipmobile']=array('eq',"$name");			
			$map['_logic'] = 'OR';
			$this->assign('name',$name);
		}
		$map['yydb']='1';	
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
//		echo $m->getLastSql();
		$count=$m->where($map)->count();
		
		foreach ($cache as $k => $v) {
			$lp=M('location_province')->where('id='.$v['provids'])->find();
			$cache[$k]['provtext']=$lp['name'];
		}
		$this->getPage($count, $psize, 'Joel-loader', '商城订单','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	//一元夺宝  zxg   2016.3.4  一元夺宝设置开关以及分享内容

	//CMS后台商城分组
	public function goods(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商品管理',
				'url'=>U('Cms/Shop/goods')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Shop_goods');
		$p=$_GET['p']?$_GET['p']:1;
		$this->assign('p',$p);
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);			
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '商品管理','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台商品设置
	public function goodsSet(){
		$id=I('id');
		$p=I('p');
		$this->assign('p',$p);
		$m=M('Shop_goods');
		//dump($m);
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商品管理',
				'url'=>U('Cms/Shop/goods')
			),
			'2'=>array(
				'name'=>'商品设置',
				'url'=>$id?U('Cms/Shop/goodsSet',array('id'=>$id)):U('Cms/Md/mdSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			$data['content']=trimUE($data['content']);
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
		//读取标签
		$label=M('Shop_label')->select();
		$this->assign('label',$label);
		//JoelTree快速无限分类
		$field=array("id","pid","name","sorts","concat(path,'-',id) as bpath");
		$cate=joelTree(M('Shop_cate'), 0, $field);
		$this->assign('cate',$cate);
		//处理编辑界面
		if($id){			
			$cache=$m->where('id='.$id)->find();
			$this->assign('cache',$cache);	
		}	
		$this->assign("isptg",self::$CMS['shopset']['isptg']);
		$this->assign("isjys",self::$CMS['shopset']['isjys']);
		$this->assign("isdfx",self::$CMS['shopset']['isdfx']);
		$this->display();
	}
	
	public function goodsDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('Shop_goods');
		if(!$id){
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
	public function cate(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商城分类',
				'url'=>U('Cms/Shop/cate')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Shop_cate');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		//JoelTree快速无限分类
		$field=array("id","pid","lv","name","summary","soncate","sorts","concat(path,'-',id) as bpath");
		$cache=joelTree($m, 0, $field);
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台商城分类设置
	public function cateSet(){
		$id=I('id');
		$m=M('Shop_cate');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商城分类',
				'url'=>U('Cms/Shop/cate')
			),
			'2'=>array(
				'name'=>'分类设置',
				'url'=>$id?U('Cms/Shop/cateSet',array('id'=>$id)):U('Cms/Shop/cateSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			if($id){
				//保存时判断
				$old=$m->where('id='.$id)->limit(1)->find();
				if($old['pid']<>$data['pid']){
					$hasson=$m->where('pid='.$id)->limit(1)->find();
					if($hasson){
						$info['status']=0;
						$info['msg']='此分类有子分类，不可以移动！';
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
				$re=$m->save($data);
				if(FALSE!==$re){
					//更新新老父级，暂不做错误处理
					if($old['pid']<>$data['pid']){
						$re=setSoncate($m, $data['pid']);
						$rold=setSoncate($m, $old['pid']);						
						$info['status']=1;
						$info['msg']=$old['pid'];
						$this->ajaxReturn($info);
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
				if($data['pid']){
					//更新父级，强制处理
					$path=setPath($m, $data['pid']);
					$data['path']=$path['path'];
					$data['lv']=$path['lv'];
				}else{
					$data['path']=0;
					$data['lv']=1;
				}
				$re=$m->add($data);
				if($re){
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
		$field=array("id","pid","name","sorts","concat(path,'-',id) as bpath");
		$cate=joelTree($m, 0, $field);
		$this->assign('cate',$cate);
		$this->display();
	}
	
	public function cateDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('Shop_cate');
		if(!$id){
			$info['status']=0;
			$info['msg']='ID不能为空!';
			$this->ajaxReturn($info);
		}
		//删除时判断
			$self=$m->where('id='.$id)->limit(1)->find();
			if($self['soncate']){
				$info['status']=0;
				$info['msg']='不能删除，存在子分类！';
				$this->ajaxReturn($info);
			}
			$re=$m->delete($id);
			if($re){
				//更新上级soncate
				if($self['pid']){
					$re=setSoncate($m, $self['pid']);
				}				
				$info['status']=1;
				$info['msg']='删除成功!';
			}else{
				$info['status']=0;
				$info['msg']='删除失败!';
				$this->ajaxReturn($info);
			}		
		$this->ajaxReturn($info);
	}
	
	//CMS后台商城分组
	public function group(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商城分组',
				'url'=>U('Cms/Shop/group')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Shop_group');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '商城分组','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台分组设置
	public function groupSet(){
		$id=I('id');
		$m=M('Shop_group');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商城分组',
				'url'=>U('Cms/Shop/group')
			),
			'2'=>array(
				'name'=>'分组设置',
				'url'=>$id?U('Cms/Shop/groupSet',array('id'=>$id)):U('Cms/Shop/groupSet')
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
	
	public function groupDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('Shop_group');
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
	
	//CMS后台SKU属性
	public function skuattr(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'SKU属性',
				'url'=>U('Cms/Shop/skuattr')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Shop_skuattr');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', 'SKU属性','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台SKU属性设置
	public function skuattrSet(){
		$id=I('id');
		$m=M('Shop_skuattr');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商城分组',
				'url'=>U('Cms/Shop/skuattr')
			),
			'2'=>array(
				'name'=>'SKU属性设置',
				'url'=>$id?U('Cms/Shop/skuattrSet',array('id'=>$id)):U('Cms/Shop/skuattrSet')
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
					if($data['newitem']){
						$mitem=M('Shop_skuattr_item');
						$dit['pid']=$id;	
						$items=array_filter(explode(',', $data['newitem']));
						foreach($items as $v){
							$dit['name']=$v;
							$rit=$mitem->add($dit);
							if($rit){
								$rr['path']=$id.$rit;
								$rerr=$mitem->where('id='.$rit)->save($rr);
							}
						}
						$son=$mitem->where('pid='.$id)->field('name,path')->select();
						$dson['items']="";
						$dson['itemspath']="";
						foreach($son as $v){
							$dson['items']=$dson['items'].$v['name'].',';
							$dson['itemspath']=$dson['itemspath'].$v['path'].',';
						}
						$rfather=$m->where('id='.$id)->save($dson);
					}
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				$dt['name']=$data['name'];
				$dt['cctime']=time();
				$re=$m->add($dt);
				if($re){
					if($data['newitem']){
						$mitem=M('Shop_skuattr_item');
						$dit['pid']=$re;	
						$items=array_filter(explode(',', $data['newitem']));
						foreach($items as $v){
							$dit['name']=$v;
							$rit=$mitem->add($dit);
							if($rit){
								$rr['path']=$re.$rit;
								$rerr=$mitem->where('id='.$rit)->save($rr);
							}
						}
						$son=$mitem->where('pid='.$re)->field('name,path')->select();
						$dson['items']="";
						$dson['itemspath']="";
						foreach($son as $v){
							$dson['items']=$dson['items'].$v['name'].',';
							$dson['itemspath']=$dson['itemspath'].$v['path'].',';
						}
						$rfather=$m->where('id='.$re)->save($dson);
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
		$this->display();
	}
	
	public function skuattrDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('Shop_skuattr');
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
	
	//用于SKUINFO保存
	public function skuattrSave(){
		$id=$_GET['id'];//必须使用get方法
		if(!$id){
			$info['status']=0;
			$info['msg']='商品ID不能为空!';
			$this->ajaxReturn($info);
		}
		//处理skuattr
		$data=I('data');
		if(!$data){
			$info['status']=0;
			$info['msg']="您还没有选择任何属性！";
			$this->ajaxReturn($info);
		}
		$list;
		$arr=array_filter(explode(';', $data));
		foreach($arr as $k=>$v){
			$arr2=array_filter(explode('-', $v));
			$arrattr=explode(':', $arr2[0]);
			$arritem=array_filter(explode(',', $arr2[1]));
			$list[$k]['attrid']=$arrattr[0];
			$list[$k]['attrlabel']=$arrattr[1];
			$checked="";
			//循环item
			foreach($arritem as $kk=>$vv){
				$at=explode(':', $vv);
				$list[$k]['items'][$at[0]]=$at[1];
				$checked=$checked.$at[0].',';
			}
			$list[$k]['checked']=$checked;
		}
		$list=list_sort_by($list,'attrid','asc');
		//dump($list);
		//$info['status']=1;
		//$info['msg']=serialize($list);
		//$this->ajaxReturn($info);
		$m=M('Shop_goods');
		$skuinfo['skuinfo']=serialize($list);
		$re=$m->where('id='.$id)->save($skuinfo);
		if($re !== FALSE){
			$info['status']=1;
			$info['msg']='SKU属性保存成功!如有变更请及时更新所有SKU!';
		}else{
			$info['status']=0;
			$info['msg']='SKU属性保存失败!请重新尝试!';
		}
		$this->ajaxReturn($info);
	}

	//用于SKU生成
	public function skuattrMake(){
		$id=$_GET['id'];//必须使用get方法
		if(!$id){
			$info['status']=0;
			$info['msg']='商品ID不能为空!';
			$this->ajaxReturn($info);
		}		
		$m=M('Shop_goods');
		$goods=$m->where('id='.$id)->find();
		$skuinfo=unserialize($goods['skuinfo']);
		//dump($skuinfo);
		if(!$skuinfo){
			$info['status']=0;
			$info['msg']='您还未设置或保存SKU属性!';
			$this->ajaxReturn($info);
		}
		$cacheattrs=array();//缓存所有属性表
		$cache;//缓存skupath列表
		$tmpsku;//缓存零时sku
		$tmpskuattrs;//sku属性对照表
		foreach($skuinfo as $k=>$v){
			$cacheattrs=$cacheattrs+$skuinfo[$k]['items'];
			$cache[$k]=array_filter(explode(',', $v['checked']));
		}
				
		if(count($cache)>1){
			//快速排列
			$tmp = Descartes($cache); 			
			foreach($tmp as $k=>$v){
				$sttr;	
				foreach($v as $kk=>$vv){
					$sttr[$kk]=$cacheattrs[$vv];
				}
				$sk=$id.'-'.implode('-', $v);
				$tmpsku[$k]=$sk;
				$tmpskuattrs[$sk]=implode(',', $sttr);
				
			} 
		}else{
			foreach($cache[0] as $k=>$v){
				$sk=$id.'-'.$v;
				$tmpsku[$k]=$sk;
				$tmpskuattrs[$sk]=$cacheattrs[$v];
			}
		}
		//dump($tmpskuattrs);
		//dump($tmpsku); 
		
		$fftmpsku=array_flip($tmpsku);
		//处理原始sku
		$msku=M('Shop_goods_sku');
		$oldsku=$msku->where('goodsid='.$id)->select();
		if($oldsku){
			foreach($oldsku as $k=>$v){
				//如果已经建立,判断状态	
				if(!in_array($v['sku'], $tmpsku)){
					//如果不存在，禁用该sku	
					$v['status']=0;
					$ro=$msku->save($v);
				}else{
					//如果已经存在，开启该sku
					$v['status']=1;
					$ro=$msku->save($v);
					//移除fftmpsku对应项目
					unset($fftmpsku[$v['sku']]);
				}
				
			}
		}
		//最后需要添加的新sku
		$finaltmpsku=array_flip($fftmpsku);
		//dump($finaltmpsku);
		//die();
		if($finaltmpsku){
			$dsku;
			foreach($finaltmpsku as $k=>$v){
				$dsku[$k]['goodsid']=$id;
				$dsku[$k]['sku']=$v;
				$dsku[$k]['skuattr']=$tmpskuattrs[$v];
				$dsku[$k]['price']=$goods['price'];
				$dsku[$k]['num']=$goods['num'];
				$dsku[$k]['status']=1;
			}
			//强制重新排序
			sort($dsku);
			//计算总库存
			$re=$msku->addAll($dsku);
			if($re){
				$totalnum=$msku->where(array('goodsid'=>$id,'status'=>1))->sum('num');
				if($totalnum){
					$rgg=$m->where('id='.$id)->setField('num',$totalnum);
				}
				//计算总库存
				$info['status']=1;
				$info['msg']='SKU更新成功!';
			}else{
				$info['status']=0;
				$info['msg']='SKU更新失败!请重新尝试!';
			}
		}else{
			$totalnum=$msku->where(array('goodsid'=>$id,'status'=>1))->sum('num');
			if($totalnum){
					$rgg=$m->where('id='.$id)->setField('num',$totalnum);
			}
			$info['status']=1;
			$info['msg']='SKU更新成功!没有新增SKU!';
		}		
		$this->ajaxReturn($info);
	}

	//CMS后台SKU管理
	public function sku(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商品管理',
				'url'=>U('Cms/Shop/goods')
			),
			'1'=>array(
				'name'=>'商品SKU管理',
				'url'=>U('Cms/Shop/skuattr',array('id'=>$_GET['id']))
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$goodsid=I('id');
		$this->assign('goodsid',$goodsid);
		//绑定商品和skuinfo
		$goods=M('Shop_goods')->where('id='.$goodsid)->find();
		$this->assign('goods',$goods);
		if($goods['skuinfo']){
			$skuinfo=unserialize($goods['skuinfo']);
			$skm=M('Shop_skuattr_item');
			foreach($skuinfo as $k=>$v){
				$checked=explode(',', $v['checked']);
				$attr=$skm->field('path,name')->where('pid='.$v['attrid'])->select();
				foreach($attr as $kk=>$vv){
					$attr[$kk]['checked']=in_array($vv['path'], $checked)?1:'';
				}
				$skuinfo[$k]['allitems']=$attr;
			}
		}
		$this->assign('skuinfo',$skuinfo);
		//绑定搜索条件与分页
		$m=M('Shop_goods_sku');
		//追入商品条件
		$map['goodsid']=$goodsid;
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';		
		$map['status']=1;
		if($name){
			$map['skuattr']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		//$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$psize=50;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '商品SKU管理','Joel-search');
		$this->assign('cache',$cache);		
		$this->assign('p',I('p'));
		$this->display();
	}

	//CMS后台sku设置
	public function skuSet(){
		$id=I('id');
		$m=M('Shop_goods_sku');
		//处理编辑界面
		$cache=$m->where('id='.$id)->find();
		$this->assign('cache',$cache);	
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商品SKU管理',
				'url'=>U('Cms/Shop/sku',array('id'=>$cache['goodsid']))
			),
			'2'=>array(
				'name'=>'商品SKU设置',
				'url'=>U('Cms/Shop/skuSet',array('id'=>$id))
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//只有保存模式
			$data=I('post.');
			$re=$m->where('id='.$id)->save($data);
			if(FALSE!==$re){				
				//重新计算总库存
				$totalnum=$m->where(array('goodsid'=>$cache['goodsid'],'status'=>1))->sum('num');
				if($totalnum){
					$rgg=M('Shop_goods')->where('id='.$cache['goodsid'])->setField('num',$totalnum);
				}
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		}
			
		$this->display();
	}
	
	//CMS后台SKU查找带回管理器
	public function skuLoader(){
		$m=M('Shop_skuattr');
		$findback=I('fbid');
		$this->assign('findback',$findback);
		$map['id']=array('not in',I('ids'));
		$cache=$m->where($map)->select();
		$this->assign('cache',$cache);		
		$this->ajaxReturn($this->fetch());
	}
	//CMS后台SKU查找带回模板
	public function skuFindback(){
		if(IS_AJAX){			
			$m=M('Shop_skuattr');
			$id=I('id');
			$this->assign('findback',$findback);
			$map['id']=$id;
			$cache=$m->where($map)->limit(1)->find();
			$this->assign('cache',$cache);		
			$items=M('Shop_skuattr_item')->where('pid='.$id)->select();
			$this->assign('items',$items);
			$this->ajaxReturn($this->fetch());
		}else{
			utf8error('非法访问！');
		}
	}
	
	//CMS后台广告分组
	public function ads(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商城广告',
				'url'=>U('Cms/Shop/ads')
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
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
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
	
	//CMS后台广告设置
	public function adsSet(){
		$id=I('id');
		$m=M('Shop_ads');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商城广告',
				'url'=>U('Cms/Shop/ads')
			),
			'2'=>array(
				'name'=>'广告设置',
				'url'=>$id?U('Cms/Shop/adsSet',array('id'=>$id)):U('Cms/Shop/adsSet')
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

	
	//CMS后台商城订单
	public function order(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'订单管理',
				'url'=>U('Cms/Shop/order')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));		
		$status=I('status');
		if($status || $status=='0'){
			$map['status']=$status;
		}
		$this->assign('status',$status);
		
		//是否开启提取方式
		$iszt=M('express_set')->getField('iszt');
		$this->assign('iszt',$iszt);
		
		//绑定搜索条件与分页
		$m=M('Shop_order');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$map['yydb']='0';
		$ordercg=I('ordercg')?I('ordercg'):'';
		if($ordercg=='1'){
			$map['isgroup']=array('in','1,2');
		}
		if($ordercg=='2'){
			$map['iscut']=array('in','1,2');
		}
		if($ordercg=='3'){
			$map['iscut']=0;
			$map['isgroup']=0;
		}
		if($ordercg=='4'){
			$map['integpay']!='';
		}
		$this->assign('ordercg',$ordercg);
		if($name){
			//订单号邦定
			//$map['oid']=array('like',"%$name%");
			//$map['vipmobile']=array('like',"%$name%");
			$map['oid']=array('eq',"$name");
			$map['vipid']=array('eq',"$name");
			$map['vipname']=array('eq',"$name");
			$map['vipmobile']=array('eq',"$name");			
			$map['_logic'] = 'OR';
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('ctime desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '商城订单','Joel-search');
		foreach ($cache as $k => $v) {
			$lp=M('location_province')->where('id='.$v['provids'])->find();
			$cache[$k]['provtext']=$lp['name'];
		}
		$this->assign('cache',$cache);		
		$this->display();
	}
	
		//发货快递
	public function orderFhkd(){
		$map['id']=I('id');
		$cache=M('Shop_order')->where($map)->find();
		$this->assign('cache',$cache);
		$express=M('express_set')->find();
		$this->assign('express',$express);
		$mb=$this->fetch();
		$this->ajaxReturn($mb);
	}
	
	//发货快递
	public function orderFhkdSave(){
		$data=I('post.');
		if(!$data){
			$info['status']=0;
			$info['msg']='未正常获取数据！';
		}
		$re=M('Shop_order')->where('id='.$data['id'])->save($data);
		if(FALSE !== $re){

		   //追入操作员日志
		    $adlog['uid']=$_SESSION['CMS']['uid'];
		    $adlog['admin']=$_SESSION['CMS']['user']['username'];
			$adlog['oid']=$data['id'];
		    $adlog['ip']=get_client_ip();
		    $adlog['ctime']=time();
		    $adlog['event']='填写快递单';
			$radlog=M('Adminlog_order')->add($adlog);
			$info['status']=1;
			$info['msg']='操作成功！';
		}else{
			$info['status']=0;
			$info['msg']='操作失败！';
		}
		$this->ajaxReturn($info);
	}
	
	//CMS后台Order详情
	public function orderDetail(){
		$id=I('id');
		$m=M('Shop_order');
		$mlog=M('Shop_order_log');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'商城订单',
				'url'=>U('Cms/Shop/order')
			),
			'2'=>array(
				'name'=>'订单详情',
				'url'=>$id?U('Cms/Shop/orderDetail',array('id'=>$id)):U('Cms/Shop/orderDetail')
			)
		);
		
		//是否开启提取方式
		$iszt=M('express_set')->getField('iszt');
		$this->assign('iszt',$iszt);
		
		$this->assign('breadhtml',$this->getBread($bread));		
		$cache=$m->where('id='.$id)->find();
		//追入vip
		$vip=M('vip')->where('id='.$cache['vipid'])->find();
		$this->assign('vip',$vip);
		$cache['items']=unserialize($cache['items']);
		$log=$mlog->where('oid='.$cache['id'])->select();
		$fxlog=M('Fx_syslog')->where('oid='.$cache['id'])->select();
		$fxslog=M('Fxs_syslog')->where('oid='.$cache['id'])->select();
		$this->assign('log',$log);
		$this->assign('fxlog',$fxlog);
		$this->assign('fxslog',$fxslog);
		$this->assign('cache',$cache);
		$this->display();
	}
	
		
	//订单改价
	public function orderChange(){
		$map['id']=I('id');
		$cache=M('Shop_order')->where($map)->find();
		$this->assign('cache',$cache);
		$mb=$this->fetch();
		$this->ajaxReturn($mb);
	}
	
	public function orderChangeSave(){
		$data=I('post.');
		if(!$data){
			$info['status']=0;
			$info['msg']='未正常获取数据！';
		}
		$data['oid']=date('YmdHis').'-'.$data['id'];
		$data['changetime']=time();
		$m=M('Shop_order');
		$re=$m->where('id='.$data['id'])->save($data);	
		
		$cache=$m->where(array('id'=>$data['id']))->find();
		$mlog=M('Shop_order_log');
		if(FALSE !== $re){
			//追入操作员日志
		    $adlog['uid']=$_SESSION['CMS']['uid'];
		    $adlog['admin']=$_SESSION['CMS']['user']['username'];
			$adlog['oid']=$data['id'];
		    $adlog['ip']=get_client_ip();
		    $adlog['ctime']=time();
		    $adlog['event']='订单价格改为'.$data['payprice'];
			$radlog=M('Adminlog_order')->add($adlog);
			$log['sid']=$cache['sid'];
			$log['oid']=$cache['oid'];
			$log['msg']='订单价格改为'.$data['payprice'].'-成功';
			$log['ctime']=time();	
			$rlog=$mlog->add($log);
			$info['status']=1;
			$info['msg']='操作成功！';
		}else{
			$info['status']=0;
			$info['msg']='操作失败！';
		}
		$this->ajaxReturn($info);
	}
	
	//订单关闭
	public function orderClose(){
		$map['id']=I('id');
		$cache=M('Shop_order')->where($map)->find();
		$this->assign('cache',$cache);
		$mb=$this->fetch();
		$this->ajaxReturn($mb);
	}
	
	public function orderCloseSave(){
		$data=I('post.');
		if(!$data){
			$info['status']=0;
			$info['msg']='未正常获取数据！';
		}
		$m=M('Shop_order');
		$mlog=M('Shop_order_log');
		$mslog=M('Shop_order_syslog');
		$mgoods=M('Shop_goods');
		$msku=M('Shop_goods_sku');		
		$cache=$m->where('id='.$data['id'])->find();
		switch($cache['status']){
			case '1':
				$data['status']=6;
				$data['closetime']=time();
				$re=$m->where('id='.$data['id'])->save($data);
				if(FALSE !== $re){
					//返回库存
					$bdata =$this->backnum($cache);
					if(!$bdata){
						//前端LOG	
						$log['sid']=$cache['sid'];
						$log['oid']=$cache['id'];
						$log['msg']='未支付订单数量回库失败';
						$log['ctime']=time();
						$rlog=$mlog->add($log);
						//后端LOG
						$log['type']=-1;
						$log['paytype']=$cache['paytype'];
						$rslog=$mslog->add($log);
						$info['status']=0;
						$info['msg']='未支付订单数量回库失败！';
					}
					//前端LOG	
					$log['sid']=$cache['sid'];
					$log['oid']=$cache['id'];
					$log['msg']='未支付订单关闭成功';
					$log['ctime']=time();
					$rlog=$mlog->add($log);
					//后端LOG
					$log['type']=6;
					$log['paytype']=$cache['paytype'];
					$rslog=$mslog->add($log);
					
					//追入操作员日志
				    $adlog['uid']=$_SESSION['CMS']['uid'];
				    $adlog['admin']=$_SESSION['CMS']['user']['username'];
					$adlog['oid']=$data['id'];
				    $adlog['ip']=get_client_ip();
				    $adlog['ctime']=time();
				    $adlog['event']='关闭未支付订单！';
					$radlog=M('Adminlog_order')->add($adlog);
					
					$info['status']=1;
					$info['msg']='关闭未支付订单成功！';
				}else{
					//前端LOG	
					$log['sid']=$cache['sid'];
					$log['oid']=$cache['id'];
					$log['msg']='未支付订单关闭失败';
					$log['ctime']=time();
					$rlog=$mlog->add($log);
					//后端LOG
					$log['type']=-1;
					$log['paytype']=$cache['paytype'];
					$rslog=$mslog->add($log);
					$info['status']=0;
					$info['msg']='关闭未支付订单失败！';
				}
				$this->ajaxReturn($info);
				break;
			case '2':
				//已支付订单跳转到这里处理
				$this->orderClosePay($cache,$data);
				break;
			default:
				$info['status']=0;
				$info['msg']='只有未付款和已付款订单可以关闭!';
				$this->ajaxReturn($info);
				break;
		}
		
	}
	
	//已支付订单退款
	public function orderClosePay($cache,$data){
		//关闭订单时不再处理库存	
		$m=M('Shop_order');
		$mvip=M('Vip');
		$mlog=M('Shop_order_log');
		$mslog=M('Shop_order_syslog');
		$mgoods=M('Shop_goods');
		$msku=M('Shop_goods_sku');
		if(!$cache['ispay']){
			$info['status']=0;
			$info['msg']='订单支付状态异常！请重试或联系技术！';
			$this->ajaxReturn($info);
		}
		//抓取会员数据
		$vip=$mvip->where('id='.$cache['vipid'])->find();
		if(!$vip){
			$info['status']=0;
			$info['msg']='会员数据获取异常！请重试或联系技术！';
			$this->ajaxReturn($info);
		}
		//支付金额
		$payprice=$cache['payprice'];
		//全部退款至余额
		$data['status']=6;
		$data['closetime']=time();
		$re=$m->where('id='.$cache['id'])->save($data);
		if(FALSE !== $re){
			$log['sid']=$cache['sid'];
			$log['oid']=$cache['id'];
			$log['msg']='订单关闭-成功';
			$log['ctime']=time();	
			$rlog=$mlog->add($log);
			$info['status']=1;
			$info['msg']='关闭订单成功！';
			if($cache['ispay']){
				$mm=$vip['money']+$payprice;
				$rvip=$mvip->where('id='.$cache['vipid'])->setField('money',$mm);
				if($rvip){
					$bdata =$this->backnum($cache);
					if(!$bdata){
						//前端LOG	
						$log['sid']=$cache['sid'];
						$log['oid']=$cache['id'];
						$log['msg']='未支付订单数量回库失败';
						$log['ctime']=time();
						$rlog=$mlog->add($log);
						//后端LOG
						$log['type']=-1;
						$log['paytype']=$cache['paytype'];
						$rslog=$mslog->add($log);
						$info['status']=0;
						$info['msg']='未支付订单数量回库失败！';
					}
					//前端LOG	
					$log['sid']=$cache['sid'];
					$log['oid']=$cache['id'];
					$log['msg']='自动退款'.$payprice.'元至用户余额-成功';
					$log['ctime']=time();
					$rlog=$mlog->add($log);
					$log['type']=6;
					$log['paytype']=$cache['paytype'];
					$rslog=$mslog->add($log);
					//后端LOG
					//追入操作员日志
				    $adlog['uid']=$_SESSION['CMS']['uid'];
				    $adlog['admin']=$_SESSION['CMS']['user']['username'];
					$adlog['oid']=$data['id'];
				    $adlog['ip']=get_client_ip();
				    $adlog['ctime']=time();
				    $adlog['event']='关闭订单成功！自动退款'.$payprice.'元至用户余额成功!';
					$radlog=M('Adminlog_order')->add($adlog);
					$info['status']=1;
					$info['msg']='关闭订单成功！自动退款'.$payprice.'元至用户余额成功!';
				}else{
					//前端LOG	
					$log['sid']=$cache['sid'];
					$log['oid']=$cache['id'];
					$log['msg']='自动退款'.$payprice.'元至用户余额-失败!请联系客服!';
					$log['ctime']=time();
					$rlog=$mlog->add($log);
					//后端LOG
					$log['type']=-1;
					$log['paytype']=$cache['paytype'];
					$rslog=$mslog->add($log);
					$info['status']=1;
					$info['msg']='关闭订单成功！自动退款'.$payprice.'元至用户余额失败!请联系技术！';
				}
			}				
			
		}else{
			$info['status']=0;
			$info['msg']='关闭订单失败！请重新尝试!';
		}
		$this->ajaxReturn($info);
	}
	
	//订单发货
	public function orderDeliver(){
		$id=I('id');
		if(!$id){
			$info['status']=0;
			$info['msg']='未正常获取ID数据！';
		}
		$m=M('Shop_order');
		$cache=$m->where('id='.$id)->find();
		$re=$m->where('id='.$id)->setField('status',3);		
		$mlog=M('Shop_order_log');
		$mslog=M('Shop_order_syslog');
		if(FALSE !== $re){
			//订单发货==发送模板消息=======================================================================================================
			$SET=M('Set')->find();
			$tp=new \bb\template();
			if($cache['sid']){
				$url=$SET['wxurl'].U('wap/fxshop/orderDetail',array('orderid'=>$cache['id']));
			}else{
				$url=$SET['wxurl'].U('wap/shop/orderDetail',array('orderid'=>$cache['id']));
			}
			
			$array=array(
				'url'=>$url,
				'ordername'=>$cache['vipname'],
				'orderid'=>$cache['oid'],
				'kdname'=>$cache['fahuokd'],
				'kdid'=>$cache['fahuokdnum']
			);
			$templatedata=$tp->enddata('delivery',$cache['vipopenid'],$array);	//组合模板数据
			
			
			$options['appid']= $SET['wxappid'];
			$options['appsecret']= $SET['wxappsecret'];
			$_wx = new \Joel\wx\Wechat($options);
			$_wx->sendTemplateMessage($templatedata);	//发送模板
			
			//=============================================================================================================================
			$log['sid']=$cache['sid'];
			$log['oid']=$id;
			$log['msg']='订单已发货';
			$log['ctime']=time();	
			$rlog=$mlog->add($log);
			//后端LOG
			$log['type']=3;
			$log['paytype']=$cache['paytype'];
			$rslog=$mslog->add($log);
			//追入操作员日志
			$adlog['uid']=$_SESSION['CMS']['uid'];
			$adlog['admin']=$_SESSION['CMS']['user']['username'];
			$adlog['oid']=$id;
			$adlog['ip']=get_client_ip();
			$adlog['ctime']=time();
			$adlog['event']='订单发货！';
			$radlog=M('Adminlog_order')->add($adlog);			
			$info['status']=1;
			$info['msg']='操作成功！';
		}else{
			$info['status']=0;
			$info['msg']='操作失败！';
		}
		$this->ajaxReturn($info);
	}
	
	//完成订单
	public function orderSuccess(){
		$id=I('id');
		if(!$id){
			$info['status']=0;
			$info['msg']='未正常获取ID数据！';
			$this->ajaxReturn($info);
		}
		//判断商城配置
		if(!self::$CMS['shopset']){
			$info['status']=0;
			$info['msg']='未正常获取商城配置信息！';
			$this->ajaxReturn($info);
		}
		//判断会员配置
		if(!self::$CMS['vipset']){
			$info['status']=0;
			$info['msg']='未正常获取会员配置信息！';
			$this->ajaxReturn($info);
		}
		//分销流程介入
		$m=M('Shop_order');
		$map['id']=$id;
		$cache=$m->where($map)->find();
		if(!$cache){
			$info['status']=0;
			$info['msg']='操作失败！订单不存在！';
			$this->ajaxReturn($info);
		}
		if($cache['status']<>3){
			$info['status']=0;
			$info['msg']='操作失败！订单状态不正确！';
			$this->ajaxReturn($info);
		}
		//追入会员信息
		$vip=M('Vip')->where('id='.$cache['vipid'])->find();
		if(!$vip){
			$info['status']=0;
			$info['msg']='未正常获取此订单的会员信息!';
			$this->ajaxReturn($info);
		}
		$cache['etime']=time();//交易完成时间
		$cache['status']=5;
		$rod=$m->save($cache);
		if(FALSE !== $rod){
			//修改会员账户金额、经验、积分、等级
			$data_vip['id']=$cache['vipid'];
			$data_vip['score']=array('exp','score+'.round($cache['payprice']*self::$CMS['vipset']['cz_score']/100));
			if (self::$CMS['vipset']['cz_exp']>0) {
				$data_vip['exp']=array('exp','exp+'.round($cache['payprice']*self::$CMS['vipset']['cz_exp']/100));
				$data_vip['cur_exp']=array('exp','cur_exp+'.round($cache['payprice']*self::$CMS['vipset']['cz_exp']/100));
				$level=$this->getLevel($vip['cur_exp']+round($cache['payprice']*self::$CMS['vipset']['cz_exp']/100));
				$data_vip['levelid']=$level['levelid'];
				//会员分销统计字段
								
				//会员合计支付
				$data_vip['total_buy']=$data_vip['total_buy']+$cache['payprice'];
				//会员购满多少钱变成分销商
				if(self::$CMS['shopset']['vipfxneed']<=$data_vip['total_buy']){
					$data_vip['isfx']=1;
				}
			}
			$re=M('vip')->save($data_vip);
			if (FALSE===$re) {
				$info['status']=0;
				$info['msg']='更新订单关联会员信息失败！';
				$this->ajaxReturn($info);
			}

			//3层会员制分销机制
			//分销佣金计算
			//$pid=$vip['pid'];
			$mvip=M('Fxs_user');
			$mfxlog=M('Fxs_syslog');
			$fxlog['sid']=$cache['sid'];
			$fxlog['oid']=$cache['id'];
			$fxlog['fxprice']=$fxprice=$cache['payprice']-$cache['yf'];
			$fxlog['ctime']=time();
			$fx1rate=self::$CMS['shopset']['fx1rate']/100;
			$fx2rate=self::$CMS['shopset']['fx2rate']/100;
			$fx3rate=self::$CMS['shopset']['fx3rate']/100;
			$fxtmp=array();//缓存3级数组
			if($cache['sid']){
				//第一层分销
				$fx1=$mvip->where('id='.$cache['sid'])->find();
				if($fx1rate){
					$fxlog['fxyj']=$fxprice*$fx1rate;
					$fx1['money']=$fx1['money']+$fxlog['fxyj'];
					$fx1['total_xxbuy']=$fx1['total_xxbuy']+1;//下线中购买产品总次数
					$fx1['total_xxyj']=$fx1['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
					$rfx=$mvip->save($fx1);					
					$fxlog['from']=$vip['id'];
					$fxlog['fromname']=$vip['nickname'];
					$fxlog['to']=$fx1['id'];
					$fxlog['toname']=$fx1['nickname'];
					if(FALSE!==$rfx){
						//佣金发放成功
						$fxlog['status']=1;
					}else{
						//佣金发放失败
						$fxlog['status']=0;
					}
					//单层逻辑					
					//$rfxlog=$mfxlog->add($fxlog);
					//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
					array_push($fxtmp,$fxlog);
				}
				//第二层分销
				if($fx1['pid']){
					$fx2=$mvip->where('id='.$fx1['pid'])->find();
					if($fx2rate){
						$fxlog['fxyj']=$fxprice*$fx2rate;
						$fx2['money']=$fx2['money']+$fxlog['fxyj'];
						$fx2['total_xxbuy']=$fx2['total_xxbuy']+1;//下线中购买产品人数计数
						$fx2['total_xxyj']=$fx2['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
						$rfx=$mvip->save($fx2);
						$fxlog['from']=$vip['id'];
						$fxlog['fromname']=$vip['nickname'];
						$fxlog['to']=$fx2['id'];
						$fxlog['toname']=$fx2['nickname'];
						if(FALSE!==$rfx){
							//佣金发放成功
							$fxlog['status']=1;
						}else{
							//佣金发放失败
							$fxlog['status']=0;
						}
						//单层逻辑
						//$rfxlog=$mfxlog->add($fxlog);
						//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
						array_push($fxtmp,$fxlog);
					}
				}
				//第三层分销
				if($fx2['pid']){
					$fx3=$mvip->where('id='.$fx2['pid'])->find();
					if($fx3rate){
						$fxlog['fxyj']=$fxprice*$fx3rate;
						$fx3['money']=$fx3['money']+$fxlog['fxyj'];
						$fx3['total_xxbuy']=$fx3['total_xxbuy']+1;//下线中购买产品人数计数
						$fx3['total_xxyj']=$fx3['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
						$rfx=$mvip->save($fx3);
						$fxlog['from']=$vip['id'];
						$fxlog['fromname']=$vip['nickname'];
						$fxlog['to']=$fx3['id'];
						$fxlog['toname']=$fx3['nickname'];
						if(FALSE!==$rfx){
							//佣金发放成功
							$fxlog['status']=1;
						}else{
							//佣金发放失败
							$fxlog['status']=0;
						}
						//单层逻辑
						//$rfxlog=$mfxlog->add($fxlog);
						//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
						array_push($fxtmp,$fxlog);
					}
				}
				//多层分销
				if(count($fxtmp)>=1){
					$refxlog=$mfxlog->addAll($fxtmp);
					if(!$refxlog){
						file_put_contents('Joel_fxs_error.txt','错误日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'错误纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
					}
				}
								
			}
			//VIP分销佣金计算
			$pid=$vip['pid'];
			$mvip=M('Vip');
			$mfxlog=M('fx_syslog');
			$fxlog=array();//清空分销log
			$fxlog['sid']=$cache['sid'];
			$fxlog['oid']=$cache['id'];
			$fxlog['ctime']=time();
			if(self::$CMS['shopset']['isdfx']){
				//开启单商品分销
				$items=unserialize($cache['items']);
				foreach($items as $k=>$v){
					$goodset =M('shop_goods')->where('id='.$v['goodsid'])->find();
					$fx1rate=$goodset['vipfx1rate']/100;
					$fx2rate=$goodset['vipfx2rate']/100;
					$fx3rate=$goodset['vipfx3rate']/100;
					$fxlog['fxprice']=$fxprice=$v['price']*$v['num'];		
					
					$fxtmp=array();//缓存3级数组
					if($pid){
						//第一层分销
						$fx1=$mvip->where('id='.$pid)->find();
						if($fx1['isfx'] && $fx1rate){
							$fxlog['fxyj']=$fxprice*$fx1rate;
							$fx1['money']=$fx1['money']+$fxlog['fxyj'];
							$fx1['total_xxbuy']=$fx1['total_xxbuy']+1;//下线中购买产品总次数
							$fx1['total_xxyj']=$fx1['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
							$rfx=$mvip->save($fx1);					
							$fxlog['from']=$vip['id'];
							$fxlog['fromname']=$vip['nickname'];
							$fxlog['to']=$fx1['id'];
							$fxlog['toname']=$fx1['nickname'];
							if(FALSE!==$rfx){
								//佣金发放成功
								$fxlog['status']=1;
							}else{
								//佣金发放失败
								$fxlog['status']=0;
							}
							//单层逻辑					
							//$rfxlog=$mfxlog->add($fxlog);
							//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
							array_push($fxtmp,$fxlog);
						}
						
						//第二层分销
						if($fx1['pid'] && $fx2rate){
							$fx2=$mvip->where('id='.$fx1['pid'])->find();
							if($fx2['isfx'] && $fx2rate){
								$fxlog['fxyj']=$fxprice*$fx2rate;
								$fx2['money']=$fx2['money']+$fxlog['fxyj'];
								$fx2['total_xxbuy']=$fx2['total_xxbuy']+1;//下线中购买产品人数计数
								$fx2['total_xxyj']=$fx2['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
								$rfx=$mvip->save($fx2);
								$fxlog['from']=$_SESSION['WAP']['vipid'];
								$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
								$fxlog['to']=$fx2['id'];
								$fxlog['toname']=$fx2['nickname'];
								if(FALSE!==$rfx){
									//佣金发放成功
									$fxlog['status']=1;
								}else{
									//佣金发放失败
									$fxlog['status']=0;
								}
								//单层逻辑
								//$rfxlog=$mfxlog->add($fxlog);
								//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
								array_push($fxtmp,$fxlog);
							}
						}
						//第三层分销
						if($fx2['pid'] && $fx3rate){
							$fx3=$mvip->where('id='.$fx2['pid'])->find();
							if($fx3['isfx'] && $fx3rate){
								$fxlog['fxyj']=$fxprice*$fx3rate;
								$fx3['money']=$fx3['money']+$fxlog['fxyj'];
								$fx3['total_xxbuy']=$fx3['total_xxbuy']+1;//下线中购买产品人数计数
								$fx3['total_xxyj']=$fx3['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
								$rfx=$mvip->save($fx3);
								$fxlog['from']=$_SESSION['WAP']['vipid'];
								$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
								$fxlog['to']=$fx3['id'];
								$fxlog['toname']=$fx3['nickname'];
								if(FALSE!==$rfx){
									//佣金发放成功
									$fxlog['status']=1;
								}else{
									//佣金发放失败
									$fxlog['status']=0;
								}
								//单层逻辑
								//$rfxlog=$mfxlog->add($fxlog);
								//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
								array_push($fxtmp,$fxlog);
							}
						}
						
						//多层分销
						if(count($fxtmp)>=1){
							$refxlog=$mfxlog->addAll($fxtmp);
							if(!$refxlog){
								file_put_contents('Joel_fx_error.txt','错误日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'错误纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
							}
						}
					}
				}
			}else{
				$fxlog['fxprice']=$fxprice=$cache['payprice']-$cache['yf'];			
				$fx1rate=self::$CMS['shopset']['vipfx1rate']/100;
				$fx2rate=self::$CMS['shopset']['vipfx2rate']/100;
				$fx3rate=self::$CMS['shopset']['vipfx3rate']/100;
				$fxtmp=array();//缓存3级数组
				if($pid){
					//第一层分销
					$fx1=$mvip->where('id='.$pid)->find();
					if($fx1['isfx'] && $fx1rate){
						$fxlog['fxyj']=$fxprice*$fx1rate;
						$fx1['money']=$fx1['money']+$fxlog['fxyj'];
						$fx1['total_xxbuy']=$fx1['total_xxbuy']+1;//下线中购买产品总次数
						$fx1['total_xxyj']=$fx1['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
						$rfx=$mvip->save($fx1);					
						$fxlog['from']=$vip['id'];
						$fxlog['fromname']=$vip['nickname'];
						$fxlog['to']=$fx1['id'];
						$fxlog['toname']=$fx1['nickname'];
						if(FALSE!==$rfx){
							//佣金发放成功
							$fxlog['status']=1;
						}else{
							//佣金发放失败
							$fxlog['status']=0;
						}
						//单层逻辑					
						//$rfxlog=$mfxlog->add($fxlog);
						//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
						array_push($fxtmp,$fxlog);
					}
					
					//第二层分销
					if($fx1['pid'] && $fx2rate){
						$fx2=$mvip->where('id='.$fx1['pid'])->find();
						if($fx2['isfx'] && $fx2rate){
							$fxlog['fxyj']=$fxprice*$fx2rate;
							$fx2['money']=$fx2['money']+$fxlog['fxyj'];
							$fx2['total_xxbuy']=$fx2['total_xxbuy']+1;//下线中购买产品人数计数
							$fx2['total_xxyj']=$fx2['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
							$rfx=$mvip->save($fx2);
							$fxlog['from']=$_SESSION['WAP']['vipid'];
							$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
							$fxlog['to']=$fx2['id'];
							$fxlog['toname']=$fx2['nickname'];
							if(FALSE!==$rfx){
								//佣金发放成功
								$fxlog['status']=1;
							}else{
								//佣金发放失败
								$fxlog['status']=0;
							}
							//单层逻辑
							//$rfxlog=$mfxlog->add($fxlog);
							//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
							array_push($fxtmp,$fxlog);
						}
					}
					//第三层分销
					if($fx2['pid'] && $fx3rate){
						$fx3=$mvip->where('id='.$fx2['pid'])->find();
						if($fx3['isfx'] && $fx3rate){
							$fxlog['fxyj']=$fxprice*$fx3rate;
							$fx3['money']=$fx3['money']+$fxlog['fxyj'];
							$fx3['total_xxbuy']=$fx3['total_xxbuy']+1;//下线中购买产品人数计数
							$fx3['total_xxyj']=$fx3['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
							$rfx=$mvip->save($fx3);
							$fxlog['from']=$_SESSION['WAP']['vipid'];
							$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
							$fxlog['to']=$fx3['id'];
							$fxlog['toname']=$fx3['nickname'];
							if(FALSE!==$rfx){
								//佣金发放成功
								$fxlog['status']=1;
							}else{
								//佣金发放失败
								$fxlog['status']=0;
							}
							//单层逻辑
							//$rfxlog=$mfxlog->add($fxlog);
							//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
							array_push($fxtmp,$fxlog);
						}
					}
					
					//多层分销
					if(count($fxtmp)>=1){
						$refxlog=$mfxlog->addAll($fxtmp);
						if(!$refxlog){
							file_put_contents('Joel_fx_error.txt','错误日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'错误纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
						}
					}
				}
			}
			

			$mlog=M('Shop_order_log');
			$dlog['sid']=$cache['sid'];
			$dlog['oid']=$cache['id'];
			$dlog['msg']='确认收货,交易完成。';
			$dlog['ctime']=time();
			$rlog=$mlog->add($dlog);
			
			//后端日志
			$mlog=M('Shop_order_syslog');
			$dlog['sid']=$cache['sid'];
			$dlog['oid']=$cache['id'];
			$dlog['msg']='交易完成-后台点击';
			$dlog['type']=5;
			$dlog['paytype']=$cache['paytype'];
			$dlog['ctime']=time();
			$rlog=$mlog->add($dlog);
			//$this->success('交易已完成，感谢您的支持！');
			//追入操作员日志
			$adlog['uid']=$_SESSION['CMS']['uid'];
			$adlog['admin']=$_SESSION['CMS']['user']['username'];
			$adlog['oid']=$id;
			$adlog['ip']=get_client_ip();
			$adlog['ctime']=time();
			$adlog['event']='订单完成！';
			$radlog=M('Adminlog_order')->add($adlog);
			//==订单完成==给该用户的上级发送=获得的佣金=====================================================
			$ds=M('fx_syslog')->where(array('oid'=>$cache['id']))->find();		//查找将获得的佣金
			$vipuser=M('vip')->where(array('id'=>$ds['to']))->find(); //查找他的上级获取上级的openid
			$SET=M('Set')->find();
			
			$items=unserialize($cache['items']);
			$itemsname='';
			foreach($items as $k=>$v){
				$itemsname.=$v['name'].',';
			}
			$tp=new \bb\template();
			$array=array(
				'url'=>$SET['wxurl'].U('wap/fx/fxlog'),
				'name'=>$ds['toname'],		//上级名字
				'fromname'=>$ds['fromname'],		//下级级名字
				'ordername'=>rtrim($itemsname,','),
				'orderid'=>$cache['oid'],
				'money'=>$ds['fxprice'],		//商品价格
				'yj'=>$ds['fxyj']			//分销的佣金
			);
			$openid=$vipuser['openid'];		//发给的人
			$templatedata=$tp->enddata('getcommission',$openid,$array);	//组合模板数据
			$options['appid']= $SET['wxappid'];
			$options['appsecret']= $SET['wxappsecret'];
			$wx = new \Joel\wx\Wechat($options);
			$wx->sendTemplateMessage($templatedata);	//发送模板
			//====================================================
			$info['status']=1;
			$info['msg']='后台确认收货操作完成！';
		}else{
			//后端日志
			$mlog=M('Shop_order_syslog');
			$dlog['sid']=$cache['sid'];
			$dlog['oid']=$cache['id'];
			$dlog['msg']='确认收货失败';
			$dlog['type']=-1;
			$dlog['paytype']=$cache['paytype'];
			$dlog['ctime']=time();
			$rlog=$mlog->add($dlog);
			//$this->error('确认收货失败，请重新尝试！');
			$info['status']=0;
			$info['msg']='后台确认收货操作失败，请重新尝试！';
		}
		$this->ajaxReturn($info);
	}

	//订单退货
	public function orderTuihuo(){
		$map['id']=I('id');
		$cache=M('Shop_order')->where($map)->find();
		$this->assign('cache',$cache);
		$mb=$this->fetch();
		$this->ajaxReturn($mb);
	}
	
	public function orderTuihuoSave(){
		$data=I('post.');
		if(!$data){
			$info['status']=0;
			$info['msg']='未正常获取数据！';
			$this->ajaxReturn($info);
		}
		$m=M('Shop_order');
		$mlog=M('Shop_order_log');
		$mslog=M('Shop_order_syslog');
		$mvip=M('Vip');
		$mgoods=M('Shop_goods');
		$msku=M('Shop_goods_sku');		
		$cache=$m->where('id='.$data['id'])->find();
		if(!$cache){
			$info['status']=0;
			$info['msg']='未正常获取订单数据！';
			$this->ajaxReturn($info);
		}
		if(!$cache){
			$info['status']=0;
			$info['msg']='未正常获取此订单数据！';
			$this->ajaxReturn($info);
		}
		//追入会员信息
		$vip=$mvip->where('id='.$cache['vipid'])->find();		
		if(!$vip){
			$info['status']=0;
			$info['msg']='未正常获取此订单的会员信息！';
			$this->ajaxReturn($info);
		}
		switch($cache['status']){
			case '4':
				$data['status']=7;
				$data['tuihuotime']=time();
				if(!$data['tuihuoprice']){
					$info['status']=0;
					$info['msg']='退货金额不能为空！';
					$this->ajaxReturn($info);
				}
				$re=$m->where('id='.$data['id'])->save($data);
				if(FALSE !== $re){
						$vip['money']=$vip['money']+$data['tuihuoprice'];
						$rvip=$mvip->save($vip);
						if($rvip!==FALSE){
							$bdata =$this->backnum($cache);
							if(!$bdata){
								//前端LOG	
								$log['sid']=$cache['sid'];
								$log['oid']=$cache['id'];
								$log['msg']='未支付订单数量回库失败';
								$log['ctime']=time();
								$rlog=$mlog->add($log);
								//后端LOG
								$log['type']=-1;
								$log['paytype']=$cache['paytype'];
								$rslog=$mslog->add($log);
								$info['status']=0;
								$info['msg']='未支付订单数量回库失败！';
							}
							//前端LOG	
							$log['sid']=$cache['sid'];
							$log['oid']=$cache['id'];
							$log['msg']='成功退货，自动退款'.$data['tuihuoprice'].'元至用户余额-成功';
							$log['ctime']=time();
							$rlog=$mlog->add($log);
							$log['type']=6;
							$log['paytype']=$cache['paytype'];
							$rslog=$mslog->add($log);
							//追入操作员日志
							$adlog['uid']=$_SESSION['CMS']['uid'];
							$adlog['admin']=$_SESSION['CMS']['user']['username'];
							$adlog['oid']=$data['id'];
							$adlog['ip']=get_client_ip();
							$adlog['ctime']=time();
							$adlog['event']='退货并关闭订单成功！自动退款'.$data['tuihuoprice'].'元至用户余额成功!';
							$radlog=M('Adminlog_order')->add($adlog);
							//后端LOG
							$info['status']=1;
							$info['msg']='关闭订单成功！自动退款'.$data['tuihuoprice'].'元至用户余额成功!';
						}else{
							//前端LOG	
							$log['sid']=$cache['sid'];
							$log['oid']=$cache['id'];
							$log['msg']='成功退货，自动退款'.$data['tuihuoprice'].'元至用户余额-失败!请联系客服!';
							$log['ctime']=time();
							$rlog=$mlog->add($log);
							//后端LOG
							$log['type']=-1;
							$log['paytype']=$cache['paytype'];
							$rslog=$mslog->add($log);
							
							//追入操作员日志
							$adlog['uid']=$_SESSION['CMS']['uid'];
							$adlog['admin']=$_SESSION['CMS']['user']['username'];
							$adlog['oid']=$data['id'];
							$adlog['ip']=get_client_ip();
							$adlog['ctime']=time();
							$adlog['event']='成功退货，自动退款'.$data['tuihuoprice'].'元至用户余额失败!请联系技术！';
							$radlog=M('Adminlog_order')->add($adlog);
							$info['status']=1;
							$info['msg']='成功退货，自动退款'.$data['tuihuoprice'].'元至用户余额失败!请联系技术！';
						}
						
					}else{
						//前端LOG	
						$log['sid']=$cache['sid'];
						$log['oid']=$cache['id'];
						$log['msg']='订单退货失败';
						$log['ctime']=time();
						$rlog=$mlog->add($log);
						//后端LOG
						$log['type']=-1;
						$log['paytype']=$cache['paytype'];
						$rslog=$mslog->add($log);
						$info['status']=0;
						$info['msg']='订单退货失败！';
					}
				$this->ajaxReturn($info);
				break;
			default:
				$info['status']=0;
				$info['msg']='只有未付款和已付款订单可以关闭!';
				$this->ajaxReturn($info);
				break;
		}
		//$info['status']=0;
		//$info['msg']='通讯失败，请重新尝试!';
		//$this->ajaxReturn($info);
		
	}

	public function backnum($cache){
		$mgoods=M('Shop_goods');
		$msku=M('Shop_goods_sku');
		$items=unserialize($cache['items']);
		foreach($items as $k => $v){
			$goods=$mgoods->where('id='.$v['goodsid'])->find();
			if($goods['issku']){
				if($v['sku']){
					$map['sku']=$v['sku'];
					$sku=$msku->where($map)->setInc('num',$v['num']);
					if($sku){
						return ture;	
					}else{
						return false;
					}
				}		
			}else{
				//无sku
				$bre=$mgoods->where('id='.$v['goodsid'])->setInc('num',$v['num']);
				if($bre){
					return ture;	
				}else{
					return false;
				}
			}						
		}
	}

//驳回订单退货
	public function orderNoth(){
		$map['id']=I('id');
		$cache=M('Shop_order')->where($map)->find();
		$this->assign('cache',$cache);
		$mb=$this->fetch();
		$this->ajaxReturn($mb);
	}
	
	public function orderNothSave(){
		$data=I('post.');
		if(!$data){
			$info['status']=0;
			$info['msg']='未正常获取数据！';
			$this->ajaxReturn($info);
		}
		$m=M('Shop_order');
		$mlog=M('Shop_order_log');
		$mslog=M('Shop_order_syslog');
		$mvip=M('Vip');
		$cache=$m->where('id='.$data['id'])->find();
		if(!$cache){
			$info['status']=0;
			$info['msg']='未正常获取订单数据！';
			$this->ajaxReturn($info);
		}
		if(!$cache){
			$info['status']=0;
			$info['msg']='未正常获取此订单数据！';
			$this->ajaxReturn($info);
		}
		//追入会员信息
		$vip=$mvip->where('id='.$cache['vipid'])->find();		
		if(!$vip){
			$info['status']=0;
			$info['msg']='未正常获取此订单的会员信息！';
			$this->ajaxReturn($info);
		}
		switch($cache['status']){
			case '4':
				$data['status']=3;
				$data['nothtime']=time();
				$re=$m->where('id='.$data['id'])->save($data);
				if(FALSE !== $re){
					
							//前端LOG	
							$log['sid']=$cache['sid'];
							$log['oid']=$cache['id'];
							$log['msg']=$data['nothtt'].":".$data['nothct'];
							$log['ctime']=time();
							$rlog=$mlog->add($log);
							$log['type']=6;
							$log['paytype']=$cache['paytype'];
							$rslog=$mslog->add($log);
							//追入操作员日志
							$adlog['uid']=$_SESSION['CMS']['uid'];
							$adlog['admin']=$_SESSION['CMS']['user']['username'];
							$adlog['oid']=$data['id'];
							$adlog['ip']=get_client_ip();
							$adlog['ctime']=time();
							$adlog['event']='驳回订单退货请求!';
							$radlog=M('Adminlog_order')->add($adlog);
							//后端LOG
							$info['status']=1;
							$info['msg']='退货订单驳回成功!';
					
						
					}else{
						//前端LOG	
						$log['sid']=$cache['sid'];
						$log['oid']=$cache['id'];
						$log['msg']='订单退货失败';
						$log['ctime']=time();
						$rlog=$mlog->add($log);
						//后端LOG
						$log['type']=-1;
						$log['paytype']=$cache['paytype'];
						$rslog=$mslog->add($log);
						$info['status']=0;
						$info['msg']='订单退货失败！';
					}
				$this->ajaxReturn($info);
				break;
			default:
				$info['status']=0;
				$info['msg']='只有申请退货产品可以关闭退货!';
				$this->ajaxReturn($info);
				break;
		}
		//$info['status']=0;
		//$info['msg']='通讯失败，请重新尝试!';
		//$this->ajaxReturn($info);
		
	}

	//导出订单
	public function orderExport() {
			$id=I('id');
			$status=I('status');
			if ($id) {
				$map['id']=array('in',$id);
			} else {
				$map['status']=$status;
			}
			if($status==''){
				$tt="全部订单";
				unset($map['status']);
			}else{
				switch ($status) {
					case 0:$tt="交易取消";break;
					case 1:$tt="未付款";break;
					case 2:$tt="已付款";break;
					case 3:$tt="已发货";break;
					case 4:$tt="退货中";break;
					case 7:$tt="退货完成";break;
					case 5:$tt="交易成功";break;
					case 6:$tt="交易关闭";break;
				}
			}
			$data=M('Shop_order')->where($map)->field("
						  id,
						  oid,  
						  totalprice,
						  totalnum,  
						  djqid,
						  payprice,
						  payscore,
						  paytype,
						  paytime,
						  yf,
						  vipid,
						  vipname,
						  vipmobile,
						  vipaddress,
						  msg,  
						  ctime,  
						  changetime,
						  changemsg,
						  changeadmin,
						  closetime,
						  closemsg,
						  closeadmin,
						  tuihuoprice,
						  tuihuosqtime,
						  tuihuotime,
						  tuihuokd,
						  tuihuokdnum,
						  tuihuomsg,
						  tuihuoadmin, 
						  status,
						  fahuokd,
						  fahuokdnum,
						  wxorder,
						  provids,
						  items,
						  tqtype")->order('id desc')->select();
			//dump($data);
			//die();
			foreach($data as $k=>$v){
				//过滤字段
				switch ($v['status']) {
					case 0:$data[$k]['status']="交易取消";break;
					case 1:$data[$k]['status']="未付款";break;
					case 2:$data[$k]['status']="已付款";break;
					case 3:$data[$k]['status']="已发货";break;
					case 4:$data[$k]['status']="退货中";break;
					case 7:$data[$k]['status']="退货完成";break;
					case 5:$data[$k]['status']="交易成功";break;
					case 6:$data[$k]['status']="交易关闭";break;
				}
				switch ($v['paytype']) {
					case 'wxpay':$data[$k]['paytype']="微信支付";break;
					case 'alipay':$data[$k]['paytype']="支付宝支付";break;
					case 'money':$data[$k]['paytype']="余额支付";break;
					default:$data[$k]['paytype']="未支付";break;
				}
				switch ($v['tqtype']) {
					case 'ziti':$data[$k]['tqtype']="自提";break;
					case 'youji':$data[$k]['tqtype']="邮寄";break;
				}
				$data[$k]['djqid']=$v['djqid']?$v['djqid']:'无';
				$data[$k]['ctime']=date('Y-m-d H:i:s',$v['ctime']);
				$data[$k]['paytime']=$v['paytime']?date('Y-m-d H:i:s',$v['paytime']):'无';
				$data[$k]['changetime']=$v['changetime']?date('Y-m-d H:i:s',$v['changetime']):'无';
				$data[$k]['closetime']=$v['closetime']?date('Y-m-d H:i:s',$v['closetime']):'无';
				$data[$k]['tuihuosqtime']=$v['tuihuosqtime']?date('Y-m-d H:i:s',$v['tuihuosqtime']):'无';
				$data[$k]['tuihuotime']=$v['tuihuotime']?date('Y-m-d H:i:s',$v['tuihuotime']):'无';
				$tmpitems=unserialize($v['items']);
				$str="";
				foreach($tmpitems as $vv){
					$vt='品名：'.$vv['name'].' 属性：'.$vv['skuattr'].'数量：'.$vv['num'].'单价：'.$vv['price'];
					$str=$str.$vt.'/***/';
				}
				$data[$k]['items']=$str;
				$lp=M('location_province')->where('id='.$v['provids'])->find();
				if($lp['name']){
					$data[$k]['vipaddress']=$lp['name'].'-'.$data[$k]['vipaddress'];
				}else{
					$data[$k]['vipaddress']=$data[$k]['vipaddress'];
				}
				unset($data[$k]['provids']);
			}//dump($data);die;
			$title=array(
						'ID',
						'订单编号',
						'订单总价(元)',
						'商品总数',
						'代金券ID',
						'支付价格(元)',
						'支付积分',
						'支付类型',
						'支付时间',
						'邮费',
						'会员ID',
						'收货姓名',
						'收货电话',
						'收货地址',
						'购买留言',
						'订单创建时间',
						'改价时间',
						'改价原因',
						'改价操作员',
						'关闭时间',
						'关闭原因',
						'关闭操作员',
						'退货退款金额',
						'退货退款申请时间',
						'退货退款完成时间',
						'退货快递公司',
						'退货快递单号',
						'退货原因',
						'退货操作员',
						'订单状态',
						'发货快递',
						'发货快递号',
						'微信订单号',
						'订单商品详情',
						'提取方式'
						);
			$this->exportexcel($data,$title,$tt.'订单'.date('Y-m-d H:i:s',time()));
		}
	//CMS后台标签列表
	public function label(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'标签列表',
				'url'=>U('Cms/Shop/label')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Shop_label');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $k=>$v){
			$listpic=$this->getPic($v['lpic']);
			$cache[$k]['limgurl']=$listpic['imgurl'];
			$listpic=$this->getPic($v['pic']);
			$cache[$k]['imgurl']=$listpic['imgurl'];
			
			$listpic=$this->getPic($v['sppic']);
			$cache[$k]['spimgurl']=$listpic['imgurl'];
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '标签列表','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台标签设置
	public function labelSet(){
		$id=I('id');
		$m=M('Shop_label');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'商城首页',
				'url'=>U('Cms/Shop/index')
			),
			'1'=>array(
				'name'=>'标签列表',
				'url'=>U('Cms/Shop/label')
			),
			'2'=>array(
				'name'=>'标签设置',
				'url'=>$id?U('Cms/Shop/lebelSet',array('id'=>$id)):U('Cms/Shop/lebelSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			$data=I('post.');
			$re=$id?$m->save($data):$m->add($data);
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		} else {
			if($id){			
				$cache=$m->where('id='.$id)->find();
				$this->assign('cache',$cache);	
			}	
			$this->display();
		}
	}
	
	public function labelDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('Shop_label');
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
	
	//批量上架
	public function goodson()
	{
	$id=$_GET['id'];//必须使用get方法
	//$id=explode(",",$id);
		$m=M('Shop_goods');
		if(!$id){
			$info['status']=0;
			$info['msg']='ID不能为空!';
			$this->ajaxReturn($info);
		}
		$a['status']=1;
		$re=$m->where("id in(".$id.")")->save($a);
		if($re){
			$info['status']=1;
			$info['msg']='上架成功!';
		}else{
			$info['status']=0;
			$info['msg']='操作失败!';
		}
		$this->ajaxReturn($info);
	}
	
	public function shelvesset(){
		$p=I('post.');
		if($p['type']=='x'){
			$mpg['status']=0;
			$mpg['id']=$p['id'];
			$re=M('shop_goods')->save($mpg);
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='下架成功!';
			}else{
				$info['status']=0;
				$info['msg']='下架失败!';
			}
		}else if($p['type']=='s'){
			$mpg['status']=1;
			$mpg['id']=$p['id'];
			$re=M('shop_goods')->save($mpg);
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='上架成功!';
			}else{
				$info['status']=0;
				$info['msg']='上架失败!';
			}
		}else{
			$info['status']=0;
			$info['msg']='失败!';
		}
		$this->ajaxReturn($info);
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
				$title[$k] = iconv("UTF-8", "UTF-8", $v);
			}
			$title = implode("\t", $title);
			echo "$title\n";
		}
		if (!empty($data)) {
			foreach ($data as $key => $val) {
				foreach ($val as $ck => $cv) {
					$data[$key][$ck] = iconv("UTF-8", "UTF-8", $cv);
				}
				$data[$key] = implode("\t", $data[$key]);

			}
			echo implode("\n", $data);
		}

	}

}