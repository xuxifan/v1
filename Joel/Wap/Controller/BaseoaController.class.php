<?php
namespace Wap\Controller;
use Think\Controller;
class BaseoaController extends Controller {
	public static $SET;//全局静态配置	
	public static $WAP;//CMS全局静态变量
	//微信缓存
	protected static $_wxappid;
	protected static $_wxappsecret;
	
    //初始化验证模块	
    protected function _initialize(){
    	//缓存全局SET
    	self::$SET=$_SESSION['SET']=$this->checkSet();
	self::$_wxappid=self::$SET['wxappid'];
	self::$_wxappsecret=self::$SET['wxappsecret'];
    	//刷新全局会员配置
    	self::$WAP['vipset']=$_SESSION['WAP']['vipset']=$this->checkVipSet();
		//刷新全局商城配置
    	self::$WAP['shopset']=$_SESSION['WAP']['shopset']=$this->checkShopSet();
		//微信授权
		if(strpos($_SERVER["HTTP_USER_AGENT"],"MicroMessenger")){
					if(I('code')){
						//第二次鉴权
						//dump(I('get'));
						if(I('code') != 'authdeny'){
							//用户授权
							$options['appid']= self::$_wxappid;
							$options['appsecret']= self::$_wxappsecret;
							$wx = new \Joel\wx\Wechat($options);
							$re=$wx->getOAJoel(I('code'));//获取access_token和openid
							//dump($re);
							$access_token=$re['access_token'];
							$openid=$re['openid'];
							if($re){
								$_SESSION['sqmode']='wecha';
								$_SESSION['sqopenid']=$openid;								
							}
							$user=$wx->getOauthUserinfo($access_token, $openid);
							if($user){
								//容错，防止ppid不存在
								$ppid=$_SESSION['oappid']?$_SESSION['oappid']:0;
								$oasid=$_SESSION['oasid']?$_SESSION['oasid']:0;
								$mvip=M('vip');
								//容错，防止重复注册VIP
								$vip=$mvip->where(array('openid'=>$openid))->find();
								if($vip){
									if($_SESSION['oacname']=='shop'){
										$this->redirect('/Wap/Shop/index');	
									}else{
										$this->redirect('/Wap/Fxshop/index');	
									}																
								}
								//处理父亲
								$old=$mvip->where(array('id'=>$ppid))->find();
								//dump($ppid);
								//dump($old);
								//die('系统调试！');
								if($old){
									//==纯在父亲-执行发送模板消息给当前注册会员的父亲================================
									$tp=new \bb\template();
									$array=array(
										'url'=> 	self::$SET['wxurl'].U('/wap/vip/message'),
										'username'=>$old['nickname'],
										'name'=>$user['nickname'],
										'date'=>date("Y-m-d H:i:s",time())
									);
									
									$templatedata=$tp->enddata('xiaxianuser',$old['openid'],$array);		//组合模板数据
									$rtn = $wx->sendTemplateMessage($templatedata);		//发送模板
									//=====================================
									$tj_score=self::$WAP['vipset']['tj_score'];
									$tj_exp=self::$WAP['vipset']['tj_exp'];
									$tj_money=self::$WAP['vipset']['tj_money'];
									if($tj_score|| $tj_exp || $tj_money){
										$msg="推荐新用户[下线]奖励：<br>新用户：".$user['nickname']."<br>奖励内容：<br>";
										$mglog="获得新下线注册奖励:";
										if($tj_score){
											$old['score']=$old['score']+$tj_score;
											$msg=$msg.$tj_score."个积分<br>";
											$mglog=$mglog.$tj_score."个积分；";
										}
										if($tj_exp){
											$old['exp']=$old['exp']+$tj_exp;
											$msg=$msg.$tj_exp."点经验<br>";
											$mglog=$mglog.$tj_exp."点经验；";
										}
										if($tj_money){
											$old['money']=$old['money']+$tj_money;
											$msg=$msg.$tj_money."元余额<br>";
											$mglog=$mglog.$tj_money."元余额；";
										}
										$msg=$msg."此奖励已自动打入您的帐户！感谢您的支持！";
										$rold=$mvip->save($old);
										if(FALSE !== $rold){
											$data_msg['pids']=$old['id'];
											$data_msg['title']="你获得一份推荐奖励！";
											$data_msg['content']=$msg;
											$data_msg['ctime']=time();
											$rmsg=M('vip_message')->add($data_msg);
											$data_mglog['vipid']=$old['id'];
											$data_mglog['nickname']=$old['nickname'];
											$data_mglog['xxnickname']=$user['nickname'];
											$data_mglog['msg']=$mglog;
											$data_mglog['ctime']=time();
											$rmglog=M('fx_log_tj')->add($data_mglog);											
										}
									}
									//三层上线追溯统计	
									$old['total_xxlink']=$old['total_xxlink']+1;		//会员数+1
									$r1=$mvip->save($old);									
//									if($old['pid']){
//										$oldold=$mvip->where('id='.$old['pid'])->find();
//										$oldold['total_xxlink']=$oldold['total_xxlink']+1;
//										$r2=$mvip->save($oldold);
//										if($oldold['pid']){
//											$oldoldold=$mvip->where('id='.$oldold['pid'])->find();
//											$oldoldold['total_xxlink']=$oldoldold['total_xxlink']+1;
//											$r3=$mvip->save($oldoldold);
//										}
//									}
									//三层上线渠道追溯统计
									if($old['sid']){
										$mfxs=M('Fxs_user');
										$fx1=$mfxs->where('id='.$old['sid'])->find();
										$refx1=$mfxs->where('id='.$old['sid'])->setInc('total_xxlink',1);
										if($fx1['pid']){
											$fx2=$mfxs->where('id='.$fx1['pid'])->find();
											$refx2=$mfxs->where('id='.$fx1['pid'])->setInc('total_xxlink',1);
											if($fx2['pid']){
												$fx3=$mfxs->where('id='.$fx2['pid'])->find();
												$refx3=$mfxs->where('id='.$fx2['pid'])->setInc('total_xxlink',1);											
											}
										}
									}							
								}
								//强制渠道定义
								if($old['sid']){
									$data['sid']=$old['sid'];
								}else{
									$data['sid']=$_SESSION['oasid']?$_SESSION['oasid']:0;
								}
								//系统追入path
								if($old['id']){
									$data['pid']=$old['id'];
									$data['path']=$old['path'].'-'.$old['id'];
									$data['plv']=$old['plv']+1;
								}else{
									$data['pid']=0;
									$data['path']=0;
									$data['plv']=1;
								}
								if(substr($data['path'], 0,1)!='0'){
									$data['path']='0'.$data['path'];
								}
								//最低分销
								$data['openid']=$user['openid'];
								$data['nickname']=$user['nickname'];
								$data['sex']=$user['sex'];
								$data['city']=$user['city'];
								$data['province']=$user['province'];
								$data['country']=$user['country'];
								$data['headimgurl']=$user['headimgurl'];
								$data['score']=self::$WAP['vipset']['reg_score'];
								$data['exp']=self::$WAP['vipset']['reg_exp'];
								$data['cur_exp']=self::$WAP['vipset']['reg_exp'];
								$level=$this->getLevel($data['exp']);
								$data['levelid']=$level['levelid'];
								$data['ctime']=time();	
								$data['cctime']=time();
								//设置分销渠道
								if(!self::$WAP['shopset']['vipfxneed']){
									$data['isfx']=1;
								}
								$rvip=$mvip->add($data);
								if($rvip){
									//赠送操作
									if (self::$WAP['vipset']['isgift']) {
										$gift=explode(",", self::$WAP['vipset']['gift_detail']);
										$cardnopwd = $this->getCardNoPwd();
										$data_card['type']=$gift[0];
										$data_card['vipid']=$rvip;
										$data_card['money']=$gift[1];
										$data_card['usemoney']=$gift[3];
										$data_card['cardno']=$cardnopwd['no'];
										$data_card['cardpwd']=$cardnopwd['pwd'];
										$data_card['status']=1;
										$data_card['stime']=$data_card['ctime']=time();
										$data_card['etime']=time()+$gift[2]*24*60*60;
										$rcard=M('Vip_card')->add($data_card);	
									}
									//发送注册通知消息
									//记录日志
									$data_log['ip'] = get_client_ip();
									$data_log['vipid'] = $rvip;
									$data_log['ctime'] = time();
									$data_log['openid'] = $data['openid'];
									$data_log['nickname'] = $data['nickname'];
									$data_log['event'] = "会员注册";
									$data_log['score'] = $data['score'];
									$data_log['exp'] = $data['exp'];
									$data_log['type'] = 4;
									$rlog=M('Vip_log')->add($data_log);
									//正常处理完成，返回原链接
									/* 2016-2-2 郑伊凡 防止强制跳转 */
									// if($_SESSION['oacname']=='shop'){
									// 	$this->redirect(U('Wap/Shop/index'));										
									// }else{
									// 	$this->redirect(U('Wap/Fxshop/index'));	
									// }	
									//$rurl=$_SESSION['oaurl'];
									//$this->redirect($rurl);
									/* 2016-2-2 郑伊凡 防止强制跳转 */

									//跳转回去重新执行一边
									$rurl=$_SESSION['oaurl'];
									session(null);
									$this->redirect($rurl);
								}else{
									//跳转回去重新执行一边
									$rurl=$_SESSION['oaurl'];
									session(null);
									$this->redirect($rurl);
								}
							}else{
								//跳转回去重新执行一边
								$rurl=$_SESSION['oaurl'];
								session(null);
								$this->redirect($rurl);
							}
							
						}else{
							//用户未授权
							$this->diemsg(0,'本应用需要您的授权才可以使用!');
						}
					}else{
						$_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
						$options['appid']= self::$_wxappid;
						$options['appsecret']= self::$_wxappsecret;
						$wx = new \Joel\wx\Wechat($options);
						$squrl=$wx->getOauthRedirect($_url,'1','snsapi_userinfo');
						header("Location:".$squrl);
					}

			}else{
				//其他浏览器不做授权跳出
				$this->diemsg(0,'请使用微信浏览器访问本应用！');
			}
		
	}	
	
	public function index(){
		//目前什么都不做
	}
		//返回全局配置
	public function checkShopSet(){
		$set=M('shop_set')->find();
		return $set?$set:utf8error('商城设置未定义！');
	}
	//返回全局配置
	public function checkSet(){
		$set=M('Set')->find();
		return $set?$set:utf8error('系统全局设置未定义！');
	}
	//返回VIP配置
	public function checkVipSet(){
		$set=M('vip_set')->find();
		return $set?$set:utf8error('会员设置未定义！');
	}
	
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
			return false;
		}
		return $level;
	}
	
	public function getCardNoPwd(){  
		$dict_no = "0123456789";
		$length_no = 10;
		$dict_pwd = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$length_pwd = 10;
		$card['no'] = "";
		$card['pwd'] = "";
		for($i=0;$i<$length_no;$i++){    $card['no'].=$dict_no[rand(0,(strlen($dict_no)-1))];    } 
		for($i=0;$i<$length_pwd;$i++){    $card['pwd'].=$dict_pwd[rand(0,(strlen($dict_pwd)-1))];    }   
		return $card;
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
}