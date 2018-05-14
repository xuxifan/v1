<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--自定义菜单管理类
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
class DiymenuController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();	
	}
	
	
	//CMS后台商城分类
	public function cate(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'自定义菜单首页',
				'url'=>U('Cms/Diymenu/index')
			),
			'1'=>array(
				'name'=>'商城分类',
				'url'=>U('Cms/Diymenu/cate')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Diymenu_cate');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		//JoelTree快速无限分类
		$field=array("id","pid","lv","name","type","keyword","url","sorts","concat(path,'-',id) as bpath");
		$cache=joelTree($m, 0, $field);
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台商城分类设置
	public function cateSet(){
		$id=I('id');
		$m=M('Diymenu_cate');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'自定义菜单',
				'url'=>U('Cms/Diymenu/cate')
			),
			'1'=>array(
				'name'=>'自定义菜单设置',
				'url'=>$id?U('Cms/Diymenu/cateSet',array('id'=>$id)):U('Cms/Diymenu/cateSet')
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
//				if($old['pid']<>$data['pid']){
//					$hasson=$m->where('pid='.$id)->limit(1)->find();
//					if($hasson){
//						$info['status']=0;
//						$info['msg']='此菜单有子菜单，不可以移动！';
//						$this->ajaxReturn($info);
//					}
//				}
				//子菜单不能超过2级
//				if($old['lv']>=2){
//					$info['status']=0;
//					$info['msg']='自定义菜单不允许超过2级！';
//					$this->ajaxReturn($info);
//				}
				//判断顶级一级和二级分类个数
				
				
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
				//判断顶级一级和二级分类个数
				if($data[pid]=='0'){
					$all=count($m->where(array('pid'=>'0'))->select());
					if($all>=3){
						$info['status']=0;
						$info['msg']='微信一级菜单不能超过3个！';
						$this->ajaxReturn($info);
					};
				}else{
					$all=count($m->where(array('pid'=>$data['pid']))->select());
					if($all>=5){
						$info['status']=0;
						$info['msg']='微信二级菜单不能超过5个！';
						$this->ajaxReturn($info);
					};
				}
				if($data['pid']){
					//新增时判断
					$old=$m->where('id='.$id)->limit(1)->find();
					//子菜单不能超过2级
					if($old['lv']>=2){
						$info['status']=0;
						$info['msg']='自定义菜单不允许超过2级！';
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
		$m=M('Diymenu_cate');
		if(!$id){
			$info['status']=0;
			$info['msg']='ID不能为空!';
			$this->ajaxReturn($info);
		}
		//删除时判断
			$self=$m->where('id='.$id)->limit(1)->find();
			if($self['soncate']){
				$info['status']=0;
				$info['msg']='不能删除，存在子菜单！';
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
	
	public function update(){
		//处理menu数组	
		$m=M('Diymenu_cate');
		$field=array("id","pid","lv","name","type","keyword","url","sorts","concat(path,'-',id) as bpath");
		$cate=joelTree($m, 0, $field);
		if(!$cate){
			$data['status'] = 0;
			$data['msg']='您还没有创建任何菜单!';
			$this->ajaxReturn($data);
		}
		$menu=array();
		foreach($cate as $k=>$v){
			if($v['_child']){
				//有子栏目的顶级菜单
				$menu[$k]['name']=$v['name'];
				
				//生成子栏目
				$submenu=array();
				foreach($v['_child'] as $kk=>$vv){
					
					if($vv['type']=='click'){
						$submenu[$kk]['type']='click';
						$submenu[$kk]['name']=$vv['name'];
						$submenu[$kk]['key']=$vv['keyword'];
					}else{
						$submenu[$kk]['type']='view';
						$submenu[$kk]['name']=$vv['name'];
						$submenu[$kk]['url']=$vv['url'];
					}
				}
				$menu[$k]['sub_button']=$submenu;
			}else{
				//单个顶级菜单
				if($v['type']=='click'){
						$menu[$k]['type']='click';
						$menu[$k]['name']=$v['name'];
						$menu[$k]['key']=$v['keyword'];
					}else{
						$menu[$k]['type']='view';
						$menu[$k]['name']=$v['name'];
						$menu[$k]['url']=$v['url'];
					}
			}
		}
		$button=array('button'=>$menu);
		//$this->ajaxReturn(array('1',$button));
		
		//缓存微信API模型类
		$options['token']=self::$SYS['set']['wxtoken'];
		$options['appid']= self::$SYS['set']['wxappid'];
		$options['appsecret']= self::$SYS['set']['wxappsecret'];
		if(!$options['appid'] || !$options['appsecret']){
			$data['status'] = 0;
			$data['msg']='请设置微信接口的Appid和Appsecret！';
			$this->ajaxReturn($data);
		}
		$wx=new \Joel\wx\Wechat($options);
		$result = $wx->createMenu($button);
		//$this->ajaxReturn($result,'JSON');
		if($result){
			$data['status'] = 1;
			$data['msg']='自定义菜单创建成功!';
			$this->ajaxReturn($data);
		}else{
			$data['status'] = 0;
			$data['msg']='自定义菜单创建失败，请重试!';
			$this->ajaxReturn($data);
		}
		
	}

	//=============
	public function scasstoken(){
		//缓存微信API模型
		$options['appid']= self::$SYS['set']['wxappid'];
		$options['appsecret']= self::$SYS['set']['wxappsecret'];
		if(!$options['appid'] || !$options['appsecret']){
			$data['status'] = 0;
			$data['msg']='请设置微信接口的Appid和Appsecret！';
			$this->ajaxReturn($data);
		}
		$wx=new \Joel\wx\Wechat($options);
		$result = $wx->resetAuth($options['appid']);
		if($result){
			$data['status'] = 1;
			$data['msg']='删除缓存成功!';
			$this->ajaxReturn($data);
		}else{
			$data['status'] = 0;
			$data['msg']='删除缓存失败!';
			$this->ajaxReturn($data);
		}
	}


}