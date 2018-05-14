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
namespace Home\Controller;
use Think\Controller;
class ApiController extends Controller {
	//全局相关	
	public static $_set;//缓存全局配置
	public static $_shopset;//缓存商城全局配置
	public static $_wx;//缓存微信对象	
	public static $_ppvip;//缓存会员通信证模型
	public static $_ppvipmessage;//缓存会员消息模型
	public static $_user;//缓存会员分销模型
	public static $_userlog;//缓存会员分销新用户推广模型	
	public static $_users;//缓存分销模型
	public static $_userslog;//缓存分销新用户推广模型	qd(渠道)=1为朋友圈，2为渠道场景二维码
    public static $_token;
	public static $_location;//用户地理信息
	//信息接收相关
	public static $_revtype;//微信发来的信息类型
	public static $_revdata;//微信发来的信息内容
	//信息推送相关
	public static $_url;//推送地址前缀
	public static $_wecha_id;
	public static $_actopen;
	
	public function __construct($options)
	{
		//读取用户配置存全局
		self::$_set=M('Set')->find();
		self::$_url=self::$_set['wxurl'];
		self::$_token=self::$_set['wxtoken'];
		//缓存通行证数据模型
		self::$_ppvip=M('Vip');
		self::$_ppvipmessage=M('Vip_message');
		self::$_user=M('Vip');
		
		self::$_wecha_id=$_GET['openid'];
	}
	
	public function index(){
			
	}
	
	public function userinfo(){
		if(self::$_wecha_id){
			$user =self::$_user->where('openid='.self::$_wecha_id)->find();
			echo $user?$user:FALSE;
		}else{
			header('Content-Type:text/html charset=utf-8');
			echo 'openid参数错误！';
		}			
	}
}//API类结束