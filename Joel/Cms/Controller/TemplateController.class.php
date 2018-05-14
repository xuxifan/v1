<?php
// +----------------------------------------------------------------------
// | 徐阳-微信自定义模板类
// +----------------------------------------------------------------------
// | JoelCMS V1.0 Beta
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.JoelCMS.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 徐阳 <xybingbing@live.com>
// +----------------------------------------------------------------------
namespace Cms\Controller;
use Cms\Controller\BaseController;
class TemplateController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();	
	}
	
	//模板列表
	public function index(){
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'自定义微信模板',
				'url'=>U('Cms/Template/set')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$m=M('template');
		$data=$m->select();
		$this->assign('template',$data);
		$this->display();
	}
	
	//模板新增于修改
	public function set(){
		$id=I('id');
		$m=M('template');
		$template_data=M('template_data');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'新增自定义微信模板',
				'url'=>U('Cms/Template/set')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$template_type=M('template_type')->select();
		$this->assign('template_type',$template_type);
		
		if(IS_POST){
			if($id){
				$post=I('post.');
				$data['id']=$post['id'];
				$data['title']=$post['title'];
				$data['name']=$post['name'];
				$data['template_id']=$post['template_id'];
				$data['url']=$post['url'];
				$data['topcolor']=$post['topcolor'];
				$m->save($data);
				$datas=array();
				foreach ($post['data'] as $key => $value){
					foreach ($value as $k => $v) {
						if($key!='id'){
							$datas[$k]['data_'.$key]=$v;
						}else{
							$datas[$k][$key]=$v;
						}
					}
				}
				//-----------
				foreach ($datas as $k => $v) {
					$template_data->save($v);
				}
				$info['status']=1;
				$info['msg']='修改成功！';
			}else{
				$post=I('post.');
				$data['title']=$post['title'];
				$data['name']=$post['name'];
				$data['template_id']=$post['template_id'];
				$data['url']=$post['url'];
				$data['topcolor']=$post['topcolor'];
				$re=$m->add($data);
				if(FALSE!==$re){
					$datas=array();
					foreach ($post['data'] as $key => $value){
						foreach ($value as $k => $v) {
							$datas[$k]['data_id']=$re;
							$datas[$k]['data_'.$key]=$v;
						}
					}
					//-----------
					foreach ($datas as $k => $v) {
						$template_data->add($v);
					}
					$info['status']=1;
					$info['msg']='添加成功！';
				}else{
					$info['status']=0;
					$info['msg']='添加失败！';
				}
			}
			$this->ajaxReturn($info);
		}
		//处理编辑界面
		if($id){			
			$cache=$m->where('id='.$id)->find();
			$this->assign('cache',$cache);
			$tmp_data=$template_data->where('data_id='.$id)->select();
			$this->assign('tmp_data',$tmp_data);	
		}
		
		$this->display();
	}

	//删除
	public function del(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('template');
		$template_data=M('template_data');
		if(!$id){
			$info['status']=0;
			$info['msg']='ID不能为空!';
			$this->ajaxReturn($info);
		}
		
		$re=$m->delete($id);
		if(FALSE!==$re){
			$template_data->where(array('data_id'=>$id))->delete();
			$info['status']=1;
			$info['msg']='删除成功!';
		}else{
			$info['status']=0;
			$info['msg']='删除失败!';	
		}
		$this->ajaxReturn($info);
	}
	
	//查看josn
	public function ckjson(){
		
		$josn['touser']=$opid='999999999';
		
		$array=array(
			'name'=>'小明',
			'n'=>'的支付消息',
			'id'=>'13',
			'user'=>'谁',
			'a'=>'你'
		);
		
		//组合微信模板数据
		$tp=new \bb\template();
		$data=$tp->enddata('1',$opid,$array);
		
		p($data);
	}
}//类结束