<?php
// +----------------------------------------------------------------------
// | Joel-单用户微信基础类
// +----------------------------------------------------------------------
// | JoelCMS V1.0 Beta
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.JoelCMS.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Joel <2094157689@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Think\Controller;
class IndexController extends Controller {
	//全局相关	
	public static $SET;//全局静态配置
	//微信缓存
	protected static $_user;	
	protected static $_wxappid;
	protected static $_wxappsecret;
	protected static $_wecha_id;
	protected static $_wechat_code;
	
	public function __construct($options)
	{
		//缓存全局SET
    	self::$SET=$_SESSION['SET']=$this->checkSet();
    	self::$_wxappid=self::$SET['wxappid'];
		self::$_wxappsecret=self::$SET['wxappsecret'];
		
		//缓存通行证数据模型
		self::$_user=M('Vip');
		
		
		//绑定高级鉴权返回地址和高级鉴权ppid
		//if(!$_GET['code']){
		//	$_SESSION['oappid']=intval($_GET['ppid'])?intval($_GET['ppid']):0;
		//	$_SESSION['oaurl']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		//}			
		//微信授权
		if(strpos($_SERVER["HTTP_USER_AGENT"],"MicroMessenger")){
				$snsapi =$_GET['snsapi'];dump($snsapi);die;
				if($_GET['code']){
					self::$_wechat_code =$_GET['code'];
					//讲真实地址还原
					/*$n=strpos($_SESSION['oaurl'],'?');//寻找位置
					if ($n){
						$_SESSION['oaurl']=substr($_SESSION['oaurl'],0,$n);//删除后面
					}*/
					//第二次鉴权
					//$_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
					if($_GET['code'] != 'authdeny'){
						
						//用户授权
						$options['appid']= self::$_wxappid;
						$options['appsecret']= self::$_wxappsecret;
						$wx = new \Joel\wx\Wechat($options);							
						$re=$wx->getOAJoel($_GET['code']);//获取access_token和openid
						$access_token=$re['access_token'];
						$openid=$re['openid'];
						if($re){
							$_SESSION['sqmode']='wecha';
							$_SESSION['sqopenid']=$openid;
							self::$_wecha_id =	$openid	;	
							$user=$wx->getOauthUserinfo($access_token, $openid);
							if($user){
								self::$_user->add($user);
							}	//dump($user);die;		
						}						
					}else{
						//用户未授权
						//$this->diemsg(0,'本应用需要您的授权才可以使用!');
					}
				}else{
					$_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
					$options['appid']= self::$_wxappid;
					$options['appsecret']= self::$_wxappsecret;
					$wx = new \Joel\wx\Wechat($options);
					if($snsapi=='openid'){
						$squrl=$wx->getOauthRedirect($_url,'1','snsapi_base');
					}elseif($snsapi=='userinfo'){
						$squrl=$wx->getOauthRedirect($_url,'1','snsapi_userinfo');
					}
					header("Location:".$squrl);
				}			
			
		}else{
			//其他浏览器不做授权跳出
			//$this->diemsg(0,'请使用微信浏览器访问本应用！');
			
//				$_SESSION['sqmode'] ='wechat';
//				$_SESSION['sqopenid']='o8osiwtNm_DaFZ90btwg3Oy6Lxlk';
		}

		//检查是否存在VIP
		$openid=$_SESSION['sqopenid'];
		$vip=self::$_user->where(array('openid'=>$openid))->find();
		if(!$vip){
			if(self::$_wechat_code){
				$options['appid']= self::$_wxappid;
				$options['appsecret']= self::$_wxappsecret;
				$wx = new \Joel\wx\Wechat($options);
				$re=$wx->getOAJoel(self::$_wechat_code);//获取access_token和openid
				
				$access_token=$re['access_token'];
				$user=$wx->getOauthUserinfo($access_token, $openid);
				if($user){
					self::$_user->add($user);
				}dump($user);die;
			}else{
				$_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				$options['appid']= self::$_wxappid;
				$options['appsecret']= self::$_wxappsecret;
				$wx = new \Joel\wx\Wechat($options);
				$squrl=$wx->getOauthRedirect($_url,'1','snsapi_userinfo');
				header("Location:".$squrl);
			}
		}
		
		//判断用户是否关注	
		if(!$vip['subscribe']){
			$options['appid']= self::$_wxappid;
			$options['appsecret']= self::$_wxappsecret;
			$wx = new \Joel\wx\Wechat($options);
			$user =$wx->getUserInfo($vip['openid']);
			if($user['subscribe']){
				if(!$vip['subscribe']){
					$sub['subscribe'] =1;
					$sub['subscribe_time']=time();
					M('vip')->where(array('openid'=>$vip['openid']))->setField($sub);
				}
			}else{
				header("location: ".self::$SET['wxsuburl']);
			}
		}
	}
	
	public function index(){//$snsapi =$_GET['snsapi'];dump($snsapi);die;
			dump($this->userinfo());
	}
	
	public function userinfo(){
		if(self::$_wecha_id){
			$user =self::$_user->where('openid='.self::$_wecha_id)->find();
			return $user?$user:FALSE;
		}else{
			echo utf8error('openid参数错误！');
		}			
	}
	
	//返回全局配置
	public function checkSet(){
		$set=M('Set')->find();
		return $set?$set:utf8error('系统全局设置未定义！');
	}
}//API类结束