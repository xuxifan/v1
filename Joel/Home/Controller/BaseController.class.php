<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
	public static $SET;//全局静态配置	
	public static $SHOPSET;//全局静态VIP配置
	public static $VIPSET;//全局静态VIP配置	
	public static $HOME;//HOME全局静态变量	
	//微信缓存
	protected static $_wxappid;
	protected static $_wxappsecret;
	
    //初始化验证模块	
    protected function _initialize(){
    	
		//缓存全局SET
    	self::$SET=$_SESSION['SET']=$this->checkSet();
    	self::$_wxappid=self::$SET['wxappid'];
		self::$_wxappsecret=self::$SET['wxappsecret'];
		
		//缓存全局商城设置
		self::$SHOPSET=$_SESSION['SHOPSET']=$this->checkShopSet();
		
		//缓存全局会员配置
		self::$VIPSET=$_SESSION['VIPSET']=$this->checkVipSet();
		
		//自动判断是否已登录
		$islogin=$this->checkWcLogin();
		
		//网站ppid实现
		if($_GET['ppid']){
			$_SESSION['ppid']=$_GET['ppid']?$_GET['ppid']:1;
		}
		
	}	
	
	//返回全局配置
	public function checkSet(){
		$set=M('Set')->find();
		return $set?$set:utf8error('系统全局设置未定义！');
	}
	
	//返回VIP配置
	public function checkShopSet(){
		$set=M('Shop_set')->find();
		return $set?$set:utf8error('商城设置未定义！');
	}
	
	//返回VIP配置
	public function checkVipSet(){
		$set=M('Vip_set')->find();
		return $set?$set:utf8error('会员设置未定义！');
	}
	
	//自动监测微信登录
	public function checkWcLogin(){
		if(!isset($_SESSION['HOME']['vipid'])){
			$ssid=session_id();
			$ca=M('Wclogin')->where(array('ssid'=>$ssid))->find();
			if($ca && $ca['vipid']){
				$_SESSION['HOME']['vipid']=$ca['vipid'];
			}
		}
		return TRUE;	
	}
		
	//检查用户是否登陆,返回TRUE或跳转扫码登陆
	public function checkLogin($backurl){
		if(!isset($_SESSION['HOME']['vipid'])){
			$this->redirect(U('Home/Vip/login'));
		} else {
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
	
	public function _fxLevelTree($id){
		$m = M('fx_level');
		$list = $m -> select();
		$html = '';
		foreach($list as $k => $val){
			if($id == $val['level']){
				$html .= '<option value="'.$val['level'].'" selected>'.$val['name'].'</option>';	
			}else{
				$html .= '<option value="'.$val['level'].'">'.$val['name'].'</option>';	
			}	
		}
		return $html;	
	}
	
}