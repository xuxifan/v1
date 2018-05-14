<?php
namespace Wap\Controller;
use Think\Controller;
class BasehkController extends Controller {
	public static $SET;//全局静态配置	
	public static $WAP;//WAP全局静态变量	
	//微信缓存
	protected static $_wxappid;
	protected static $_wxappsecret;
	//授权Session
	protected static $_sqmode;//对应$_SESSION['sqmode']-wecha,-yicha,-wap
	//其他初始化
	protected static $_trueurl;//真实的访问路径
    //初始化验证模块	
    protected function _initialize(){
		//session(null);
		//die('session null');
		//dump($_SESSION);
		//die();
		//缓存全局SET
    	self::$SET=$_SESSION['SET']=$this->checkSet();
    	self::$_wxappid=self::$SET['wxappid'];
		self::$_wxappsecret=self::$SET['wxappsecret'];
		
		//绑定高级鉴权返回地址和高级鉴权ppid&sid
		//$_SESSION['oaurl']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		//鉴权判定流程
		//判断是否进行base鉴权
		if(!$_SESSION['sqmode']){
			
			//绑定高级鉴权返回地址和高级鉴权ppid
			//if(!$_GET['code']){
			//	$_SESSION['oappid']=intval($_GET['ppid'])?intval($_GET['ppid']):0;
			//	$_SESSION['oaurl']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			//}			
			//微信授权
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MicroMessenger")){
				
					if($_GET['code']){
						//第二次鉴权
						$_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
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
							}
							//正常处理完成，返回原链接
							//$rurl=$_SESSION['oaurl'];
							//$this->redirect($rurl);	
													
						}else{
							//用户未授权
							$this->diemsg(0,'本应用需要您的授权才可以使用!');
						}
					}else{
						$_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
						$options['appid']= self::$_wxappid;
						$options['appsecret']= self::$_wxappsecret;
						$wx = new \Joel\wx\Wechat($options);
						$squrl=$wx->getOauthRedirect($_url,'1','snsapi_base');
						header("Location:".$squrl);
					}			
				
			}else{
				//其他浏览器不做授权跳出
				$this->diemsg(0,'请使用微信浏览器访问本应用！');
			}
			
		}

			
		
		
	}	
	
	//返回全局配置
	public function checkSet(){
		$set=M('Set')->find();
		return $set?$set:utf8error('系统全局设置未定义！');
	}
	//返回VIP配置
	public function checkVipSet(){
		$set=M('Vip_set')->find();
		return $set?$set:utf8error('会员设置未定义！');
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
}