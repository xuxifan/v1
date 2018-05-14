<?php
namespace Cms\Controller;
use Cms\Controller\BaseController;
class UserController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
	
	public function userList(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'管理员中心',
				'url'=>U('Cms/User/#')
			),
			'1'=>array(
				'name'=>'管理员列表',
				'url'=>U('Cms/User/userList')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('user');
		$p=$_GET['p']?$_GET['p']:1;
		$search=I('search')?I('search'):'';
		if($search){
			$map['username']=array('like',"%$search%");
			$this->assign('search',$search);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '管理员列表','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台商品设置
	public function userSet(){
		$id=I('id');
		$m=M('user');
		//dump($m);
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'管理员中心',
				'url'=>U('Cms/User/#')
			),
			'1'=>array(
				'name'=>'管理员列表',
				'url'=>U('Cms/User/userList')
			),
			'1'=>array(
				'name'=>'管理员编辑',
				'url'=>U('Cms/User/userSet',array('id'=>$id))
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			if($id){
				if($data['userpass']){
					$data['userpass']=md5($data['userpass']);
				}else{
					unset($data['userpass']);
				}
				$re=$m->save($data);
				if(FALSE!==$re){
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				$data['userpass']=md5($data['userpass']);
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
		$oath=M('User_oath')->select();
		$this->assign('oath',$oath);
		//处理编辑界面
		if($id){			
			$cache=$m->where('id='.$id)->find();
			$this->assign('cache',$cache);	
		}	
		$this->display();
	}


	
	public function userDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('User');
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
	
		
}