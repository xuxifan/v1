<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--CMS分组基础类
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
use Think\Controller;
class BaseController extends Controller {
	protected static $SYS;//系统级全局静态变量
	protected static $CMS;//CMS全局静态变量
    //初始化验证模块	
    protected function _initialize(){
    	//预留检测
    	$this->checkJoel();
		//刷新系统全局配置
		self::$SYS['set']=$_SESSION['SYS']['set']=$this->checkSysSet();
    	//刷新CMS全局配置
    	self::$CMS['set']=$_SESSION['CMS']['set']=$this->checkSet();
		//检测登陆状态
    	$check=$this->checkLogin();
	}	
	
	//CMS总入口	
    public function index(){
		$this->display();
    }
	
	//全局Joel预留方法
	public function checkJoel(){
		return TRUE;
	}
	
	//返回系统全局配置
	public function checkSysSet(){
		$set=M('Set')->find();
		return $set?$set:utf8error('系统还未配置！');
	}
	
	//返回CMS全局配置
	public function checkSet(){
		$set=M('Cms_set')->find();
		return $set?$set:utf8error('系统还未配置！');
	}
	
	//检查用户是否登陆,返回TRUE或跳转登陆
	public function checkLogin(){
		$passlist=array('login','logout','reg','checkusername','verify');//不检测登陆状态的操作
		$check=in_array(ACTION_NAME, $passlist);
		if(!$check){
			if(!isset($_SESSION['CMS']['uid'])){
				$this->error('登陆已超时,请重新登陆',U('Cms/Public/login'));
			}
		}else{
			return TRUE;
		}		
	}
	//停止不动的信息通知页面处理
	public function diemsg($status,$msg){
		//成功为1，失败为0
		$status=$status?$status:'0';
		$this->assign('status',$status);
		$this->assign('msg',$msg);
		$this->display('Base_diemsg');
		die();
	}
	
	//拼装面包导航
	public function getBread($bread){
		if($bread){
			$this->assign('bread',$bread);
			return $this->fetch('Base_bread');
		}else{
			$this->error('请传入面包导航！');
		}
	}
	
	//封装分页类
	public function getPage($count,$psize,$loader,$loadername,$searchname,$map){
		if(!$count && !$psize || !$loader || !$loadername){
			die('缺少分页参数!');
		}
		$page = new \Joel\Pagecms($count,$psize);// 实例化分页类 传入总记录数和每页显示的记录数
		$page->setConfig('loader',$loader);
		$page->setConfig('loadername',$loadername);
		//绑定前端form搜索表单ID,默认为#Joel-search
		if($searchname){
			$page->setConfig('searchname',$searchname);
		}		
		if($map){
			foreach($map as $key=>$val) {
    			$page->parameter[$key]   =   urlencode($val);
			}
		}
		$show = $page->show();// 分页显示输出
		$this->assign('page',$show);
		return true;	
	}
	
	//获取单张图片
	public function getPic($id){
		$m=M('Upload_img');
		$map['id']=$id;
		$list=$m->where($map)->find();
		if($list){
			$list['imgurl']="/upload/".$list['savepath'].$list['savename'];
		}
		return $list?$list:"";
	}
	//获取图集合
	public function getAlbum($ids){
		$m=M('Upload_img');
		$map['id']=array('in',$ids);
		$list=$m->where($map)->select();
		foreach($list as $k=>$v){
			$list[$k]['imgurl']="/upload/".$list[$k]['savepath'].$list[$k]['savename'];			
		}		
		return $list?$list:"";
	}
	
	//获取会员等级经验对称数据
	public function getlevel($exp) {
		$data=M('vip_level')->order('exp')->select();
		if ($data) {
			$level;
			foreach ($data as $k=>$v) {
				if ($k+1==count($data)) {
					if ($exp>=$data[$k]['exp']) {
						$level['levelid']=$data[$k]['id'];
						$level['levelname']=$data[$k]['name'];
					}
				} else {
					if ($exp>=$data[$k]['exp'] && $exp<$data[$k+1]['exp']) {
						$level['levelid']=$data[$k]['id'];
						$level['levelname']=$data[$k]['name'];
					}
				}
			}
		} else {
			return utf8error('会员等级未定义！');
		}
		return $level;
	}
	//下载网络图片
	function download_remote_file($file_url, $save_to){
		$content = file_get_contents($file_url);
		file_put_contents($save_to, $content);
	}
}