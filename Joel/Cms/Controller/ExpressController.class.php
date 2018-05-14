<?php
namespace Cms\Controller;
use Cms\Controller\BaseController;
/**
 * 快递邮费功能
 */
class ExpressController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	/**
	 * 邮费设置
	 */
	public function set(){
		if(IS_POST){
			$data=I('post.');
			$data['ctime']=time();
			$result=M('express_set')->save($data);
			if($result!==false){
				$info['status']=1;
				$info['msg']='保存成功';
			}else{
				$info['status']=0;
				$info['msg']='操作失败';
			}
			$this->ajaxReturn($info);
		}else{
			//设置面包导航，主加载器请配置		
			$bread=array(
				'0'=>array(
					'name'=>'快递设置',
					'url'=>U('Cms/express/set')
				)
			);
			$this->assign('breadhtml',$this->getBread($bread));
			$data=M('express_set')->find();
			$this->assign('data',$data);
			$this->display();
		}
	}
	/**
	 * 区域设置
	 */
	public function area(){
		$bread=array(
			'0'=>array(
				'name'=>'快递设置',
				'url'=>U('Cms/express/set')
			),
			'1'=>array(
				'name'=>'区域邮费',
				'url'=>U('Cms/express/area')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		$list=M('express_area')->select();
		foreach ($list as $k => $v) {
			$p=array_filter(explode("|", $v['provids']));
			$map['id']=array('in',$p);
			$pr=M('location_province')->where($map)->field('name')->select();
			$prtext='';
			foreach ($pr as $key => $value) {
				$prtext=$prtext.$value['name'].',';
			}
			$list[$k]['prtext']=$prtext;
			//邮费描述
			$heavy=explode(",", $v['heavylist']);
			$money=explode(",", $v['moneylist']);
			$list[$k]['summary']='首重:'.$heavy[0].'KG,价格:'.$money[0].'元/KG;';
			$iplus=0;
			for ($i=1; $i <count($heavy)-1; $i++) { 
				$list[$k]['summary']=$list[$k]['summary']."≤".$heavy[$i].'KG的部分:'.$money[$i].'元/KG;';
				$iplus=$i;
			}
			$list[$k]['summary']=$list[$k]['summary'].'>'.$heavy[$iplus].'KG的部分:'.$money[$iplus+1].'元/KG;';
		}
		$this->assign('list',$list);
		$this->display();
	}
	/**
	 * 区域保存
	 */
	public function areaset(){
		//处理POST提交	
		if(IS_POST){
			$data=I('post.');
			$data['ctime']=time();
			if($data['id']){
				$re=M('express_area')->save($data);
				if(FALSE!==$re){
					$info['status']=1;
					$info['msg']='修改成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				$re=M('express_area')->add($data);
				if($re){
					$info['status']=1;
					$info['msg']='新增成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}
			$this->ajaxReturn($info);
		}else{
			//设置面包导航，主加载器请配置		
			$bread=array(
				'0'=>array(
					'name'=>'快递设置',
					'url'=>U('Cms/express/set')
				),
				'1'=>array(
					'name'=>'区域邮费',
					'url'=>U('Cms/express/area')
				),
				'2'=>array(
					'name'=>'区域设置'
				)
			);
			$this->assign('breadhtml',$this->getBread($bread));
			if(I('id')){
				$cache=M('express_area')->where('id='.I('id'))->find();
				$cache['heavylist']=explode(",", $cache['heavylist']);
				$cache['moneylist']=explode(",", $cache['moneylist']);
				$this->assign('cache',$cache);
			}
			//省份
			$prov=M('location_province')->select();
			$this->assign('prov',$prov);
			$dis=M('express_area')->where('id<>'.I('id'))->select();
			$dispr;
			foreach ($dis as $k => $v) {
				$dispr=$dispr.$v['provids'];
			}
			$disids=implode("|", array_filter(explode("|", $dispr))) ;
			$this->assign('disids',$disids);
			$this->display();
		}
	}
	/**
	 * 删除区域
	 */
	public function areadel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('express_area');
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