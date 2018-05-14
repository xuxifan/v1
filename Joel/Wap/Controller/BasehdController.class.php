<?php
namespace Wap\Controller;
use Think\Controller;
class BasehdController extends Controller {
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
		
		//绑定高级鉴权返回地址和高级鉴权ppid
		$_SESSION['oappid']=intval($_GET['ppid'])?intval($_GET['ppid']):0;
		$_SESSION['oaurl']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		
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
							$rurl=$_SESSION['oaurl'];
							$this->redirect($rurl);	
													
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
		
		//检查是否存在VIP
		if($_SESSION['sqmode'] && $_SESSION['sqopenid']){
			$openid=$_SESSION['sqopenid'];
			$vip=M('vip')->where(array('openid'=>$openid))->find();
			if(!$vip){
				$this->redirect(U('Wap/Basehdoa/index'));
			}
			self::$WAP['vipid']=$_SESSION['WAP']['vipid']=$vip['id'];
			self::$WAP['vip']=$_SESSION['WAP']['vip']=$vip;
			//注销高级鉴权缓存
			unset($_SESSION['oappid']);
			unset($_SESSION['oaurl']);
			
		}else{
			session(null);
			$this->diemsg(0,'未正常获取会员数据，请尝试重新访问！');
		}
		//全局初始化完成
		//初始化全局会员配置
		self::$WAP['vipset']=$_SESSION['WAP']['vipset']=$this->checkVipSet();
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
		
	//检查用户是否登陆,返回TRUE或跳转登陆
	public function checkLogin($backurl){
		if(!isset($_SESSION['WAP']['vipid'])){
			//$this->redirect(U('Wap/Vip/login',array('backurl'=>$backurl)));
			session(null);
			$this->diemsg(0,'未正常获取会员数据，请尝试重新访问！');
		} else {
			//$map['id']=self::$WAP['vipid']=$_SESSION['WAP']['vipid'];
			//self::$WAP['vip']=$_SESSION['WAP']['vip']=M('vip')->where($map)->find();
			$levelname=M('Vip_level')->where('id='.self::$WAP['vip']['levelid'])->getField('name');
			self::$WAP['vip']['levelname']=$_SESSION['WAP']['vip']['levelname']=$levelname;
			$this->checkMonthexp();
			return TRUE;
		}	
	}
	
	//检查上月是否已保存每月经验
	public function checkMonthexp(){
		$vipid=self::$WAP['vipid'];
		$m=M('vip_monthexp');
		$last_month=getdate(strtotime("-1 month"));
		$map['month']=$last_month['year']."-".$last_month['mon'];
		$map['vipid']=$vipid;
		$re=$m->where($map)->find();
		if (!$re) {
			//所有保存过的经验总计
			$exp_record=$m->where('vipid='.$vipid)->sum('exp');
			//需要保存的经验=总经验-保存过的经验总计
			$data['exp']=self::$WAP['vip']['exp']-$exp_record;
			$data['month']=$map['month'];
			$data['ctime']=time();
			$data['vipid']=$vipid;
			$m->add($data);
			
			//判断用户当前经验设定用户等级
			if (self::$WAP['vipset']['level_period']>0) {
				$month_arr="";
				for($i=1;$i<=self::$WAP['vipset']['level_period'];$i++){
					$temp=getdate(strtotime("-".$i." month"));
					$month_arr.=$temp['year']."-".$temp['mon'].",";
				}
				$map['month']=array('in',$month_arr);
				$cur_exp=$m->where($map)->sum('exp');
			} else {
				$cur_exp=self::$WAP['vip']['exp'];
			}
			$data_vip['cur_exp']=$cur_exp;
			$level=$this->getlevel($cur_exp);
			$data_vip['levelid']=$level['levelid'];
			$r=M('vip')->where('id='.$vipid)->save($data_vip);
			if ($r) {
				self::$WAP['vip']['cur_exp']=$_SESSION['WAP']['vip']['cur_exp']=$data_vip['cur_exp'];
				self::$WAP['vip']['levelid']=$_SESSION['WAP']['vip']['levelid']=$level['levelid'];
				self::$WAP['vip']['levelname']=$_SESSION['WAP']['vip']['levelname']=$level['levelname'];
			}
		}
	}
	
//	public function getLevel($exp) {
//		$level_rule=explode(",",self::$WAP['vipset']['level_rule']);
//		$level;
//		foreach ($level_rule as $k=>$v) {
//			$level_rule[$k]=explode(":",$v);
//		}
//		foreach ($level_rule as $k=>$v) {
//			if ($k+1==count($level_rule)) {
//				if ($exp>=$level_rule[$k][1]) {
//					$level['level']=$k;
//					$level['levelname']=$level_rule[$k][0];
//				}
//			} else {
//				if ($exp>=$level_rule[$k][1] && $exp<$level_rule[$k+1][1]) {
//					$level['level']=$k;
//					$level['levelname']=$level_rule[$k][0];
//				}
//			}
//		}
//		return $level;
//	}
	
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