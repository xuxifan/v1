<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--CMS分组魔法关键词类
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
class WxController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
	//CMS后台魔法关键词引导页
    public function index(){
    	//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'微信管理',
				'url'=>U('Cms/Wx/index')
			)
		);
    	$this->display();
    }

	//CMS后台微信设置
	public function set(){
		$m=M('Set');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'微信管理',
				'url'=>U('Cms/Wx/index')
			),
			'1'=>array(
				'name'=>'微信设置',
				'url'=>U('Cms/Wx/set')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			$old=$m->find();
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
				$info['status']=0;
				$info['msg']='设置失败！系统配置表不存在！';
			}
			$this->ajaxReturn($info);
		}
		$cache=$m->find();
		$this->assign('cache',$cache);
		$this->display();
	}
	
	
	//CMS后台关键词分组
	public function keyword(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'魔法关键词',
				'url'=>U('Cms/Wx/index')
			),
			'1'=>array(
				'name'=>'关键词列表',
				'url'=>U('Cms/Wx/Keyword')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Wx_keyword');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach($cache as $k=>$v){
			if($v['url']){
				$url_arr=explode('&', $v['url']);
				foreach($url_arr as $kk=>$vv){
					$url_arr[$kk]=$vv.' ';
				}
				$url=implode('&', $url_arr);
				$cache[$k]['url']=$url;
			}
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '关键词分组','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台关键词设置
	public function keywordSet(){
		$id=I('id');
		$m=M('Wx_keyword');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'魔法关键词',
				'url'=>U('Cms/Wx/index')
			),
			'1'=>array(
				'name'=>'关键词列表',
				'url'=>U('Cms/Wx/keyword')
			),
			'2'=>array(
				'name'=>'关键词设置',
				'url'=>$id?U('Cms/Wx/keywordSet',array('id'=>$id)):U('Cms/Wx/keywordSet')
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
	
	public function keywordDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('Wx_keyword');
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
	
	//CMS后台关键词分组
	public function img(){
		$kid=I('kid')?I('kid'):die('缺少KID参数！');
		//绑定keyword
		$keyword=M('Wx_keyword')->where('id='.$kid)->find();
		$this->assign('keyword',$keyword);
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'魔法关键词',
				'url'=>U('Cms/Wx/keyword')
			),
			'1'=>array(
				'name'=>'关键词图文列表',
				'url'=>U('Cms/Wx/img',array('kid'=>$kid))
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Wx_keyword_img');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$map['kid']=$kid;
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '关键词图文列表','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台关键词设置
	public function imgSet(){
		$kid=I('kid');
		$id=I('id');
		$m=M('Wx_keyword_img');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'魔法关键词',
				'url'=>U('Cms/Wx/keyword')
			),
			'1'=>array(
				'name'=>'关键词图文列表',
				'url'=>U('Cms/Wx/img',array('kid'=>$kid))
			),
			'2'=>array(
				'name'=>'关键词图文设置',
				'url'=>$id?U('Cms/Wx/imgSet',array('id'=>$id)):U('Cms/Wx/imgSet')
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
		//绑定keyword
		$keyword=M('Wx_keyword')->where('id='.$kid)->find();
		$this->assign('keyword',$keyword);
		//处理编辑界面
		if($id){						
			$cache=$m->where('id='.$id)->find();
			$this->assign('cache',$cache);	
		}	
		$this->display();
	}
	
	public function imgDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('Wx_keyword_img');
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