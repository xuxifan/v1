<?php

namespace Cms\Controller;
use Cms\Controller\BaseController;
class NewsController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
    public function index(){
    	//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'新闻管理',
				'url'=>U('Cms/News/index')
			)
		);
    	$this->display();
    }

	
	
	//新闻
	public function News(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'新闻管理',
				'url'=>U('Cms/News/index')
			),
			'1'=>array(
				'name'=>'新闻列表',
				'url'=>U('Cms/News/News')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('News');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '门店分组','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//新闻设置
	public function NewsSet(){
		$id=I('id');
		$m=M('News');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'新闻管理',
				'url'=>U('Cms/News/index')
			),
			'1'=>array(
				'name'=>'新闻列表',
				'url'=>U('Cms/News/News')
			),
			'2'=>array(
				'name'=>'新闻设置',
				'url'=>$id?U('Cms/News/NewsSet',array('id'=>$id)):U('Cms/News/NewsSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			$data['content']=trimUE($data['content']);
			$data['ctime']=time();
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
		//处理门店分组
		$group=M('News_group')->select();
		$this->assign('group',$group);
		//处理编辑界面
		if($id){			
			$cache=$m->where('id='.$id)->find();
			$this->assign('cache',$cache);	
		}	
		$this->display();
	}
	
	public function NewsDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('News');
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

	// 员工列表
	public function staff(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'员工管理',
				'url'=>U('Cms/News/index')
			),
			'1'=>array(
				'name'=>'员工列表',
				'url'=>U('Cms/News/staff')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Vip_news');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '门店分组','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}

	public function searchNickname(){
		if(IS_POST){
			$name=$_POST['nname'];
			if($name){
				$map['nickname']=array('like',"%".$name."%");
			}
			$data=M('Vip')->where($map)->field('openid,nickname')->select();
			$this->ajaxReturn($data);
		}
	}

	public function setstaff(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'员工管理',
				'url'=>U('Cms/News/index')
			),
			'1'=>array(
				'name'=>'员工列表',
				'url'=>U('Cms/News/staff')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		if (IS_POST) {
			unset($_POST['nname']);
			$data=$_POST;
			$m=M("Vip_news");
			if ($data['id']) {
				$re = $m->save($data);
				if($re !== false) {
					$info['status'] = 1;
					$info['msg'] = '修改成功';
				} else {
					$info['status'] = 0;
					$info['msg'] = '修改失败';
				}
			} else {
				unset($data['id']);
				$data['ctime'] = time();
				if($m->where('name="'.$data['name'].'"')->find()){
					$info['status'] = 0;
					$info['msg'] = '此员工已存在';
				} else {
					$re = $m->add($data);
					if($re !== false) {
						$info['status'] = 1;
						$info['msg'] = '添加成功';
					} else {
						$info['status'] = 0;
						$info['msg'] = '添加失败';
					}
				}
			}
			$this->ajaxReturn($info);
		}
		if(I('id')){
			$cache=M("Vip_news")->where("id=".I('id'))->find();
			$this->assign('cache',$cache);
		}
		$this->display();
	}

	public function delstaff(){
		$id=$_GET['id'];
		$id=trim($id,',');
    	$map['id']=array('in',$id);
    	$re=M('Vip_news')->where($map)->delete();
    	if(FALSE!==$re){
			$info['status']=1;
			$info['msg']='删除成功！';
		}else{
			$info['status']=0;
			$info['msg']='删除失败！';
		}
		$this->ajaxReturn($info);
	}
	
	public function showwork(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'员工管理',
				'url'=>U('Cms/News/index')
			),
			'1'=>array(
				'name'=>'员工列表',
				'url'=>U('Cms/News/staff')
			)
		);
		$l=M("News_log");
		$this->assign('breadhtml',$this->getBread($bread));
		$id=I('id');
		$ids=$l->where('pid='.$id)->field('newsid')->distinct(true)->select();
		$str="";
		foreach($ids as $v){
			$str.=$v["newsid"].",";
		}
		$ids=trim($str,',');
		//绑定搜索条件与分页
		$m=M('News');
		$map['id']=array('in',$ids);
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name) {
			$map['name']=array('like',"%$name%");
			$map['istg']=1;
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '门店分组','Joel-search');
		// 统计该员工分享的文章的浏览数
		foreach($cache as $k=>$v) {
			$logmap['newsid']=$v['id'];
			$logmap['pid']=$id;
			$cache[$k]['num']=$l->where($logmap)->count();
		}
		$staff_name=M('Vip_news')->where('id='.$id)->getField('name');
		$this->assign('staff_name',$staff_name);
		$this->assign('cache',$cache);
		$this->assign('pid',$id);
		$this->display();
	}

	public function showdetails(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'员工管理',
				'url'=>U('Cms/News/index')
			),
			'1'=>array(
				'name'=>'员工列表',
				'url'=>U('Cms/News/staff')
			),
			'2'=>array(
				'name'=>'分享列表',
				'url'=>U('Cms/News/showwork',array('id'=>I('pid')))
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('News_log');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$map['newsid']=I('newsid');
		$map['pid']=I('pid');
		if($name){
			$map['nickname']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '门店分组','Joel-search');
		$this->assign('id',I('pid'));
		$this->assign('cache',$cache);		
		$this->display();
	}

	public function showform(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'员工管理',
				'url'=>U('Cms/News/index')
			),
			'1'=>array(
				'name'=>'员工列表',
				'url'=>U('Cms/News/staff')
			),
			'2'=>array(
				'name'=>'分享列表',
				'url'=>U('Cms/News/showwork',array('id'=>I('pid')))
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('News_form');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$map['newsid']=I('newsid');
		$map['staffid']=I('staffid');
		if($name){
			$where['name']=array('like',"%$name%");
			$where['area']=array('like',"%$name%");		
			$where['company']=array('like',"%$name%");		
			$where['tel']=array('like',"%$name%");		
			$where['_logic']='OR';
			$map['_complex']=$where;
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '门店分组','Joel-search');
		$this->assign('id',I('staffid'));
		$this->assign('cache',$cache);
		$this->display();
	}

	public function delform(){
		$id=$_GET['id'];
		$id=trim($id,',');
    	$map['id']=array('in',$id);
    	$re=M('News_form')->where($map)->delete();
    	if(FALSE!==$re){
			$info['status']=1;
			$info['msg']='删除成功！';
		}else{
			$info['status']=0;
			$info['msg']='删除失败！';
		}
		$this->ajaxReturn($info);
	}

	public function showAllForm(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'新闻管理',
				'url'=>U('Cms/News/index')
			),
			'1'=>array(
				'name'=>'新闻列表',
				'url'=>U('Cms/News/News')
			),
			'2'=>array(
				'name'=>'新闻设置',
				'url'=>$id?U('Cms/News/NewsSet',array('id'=>$id)):U('Cms/News/NewsSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('News_form');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		$map['newsid']=I('newsid');
		if($name){
			$where['name']=array('like',"%$name%");
			$where['area']=array('like',"%$name%");		
			$where['company']=array('like',"%$name%");		
			$where['tel']=array('like',"%$name%");		
			$where['_logic']='OR';
			$map['_complex']=$where;
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '门店分组','Joel-search');
		$this->assign('id',I('staffid'));
		$this->assign('cache',$cache);
		$this->display();
	}

}