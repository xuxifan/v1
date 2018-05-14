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
class WxController extends Controller {	
	//全局相关	
	public static $_set;//缓存全局配置
	public static $_shopset;//缓存商城全局配置
	public static $_wx;//缓存微信对象	
	public static $_ppvip;//缓存会员通信证模型
	public static $_ppvipmessage;//缓存会员消息模型
	public static $_fx;//缓存会员分销模型
	public static $_fxlog;//缓存会员分销新用户推广模型	
	public static $_fxs;//缓存分销模型
	public static $_fxslog;//缓存分销新用户推广模型	qd(渠道)=1为朋友圈，2为渠道场景二维码
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
			//检测token是否合法
			$tk=$_GET['token'];
			if($tk<>self::$_token){
				die('token error');
			}
			//缓存微信API模型类
			$options['token']=self::$_token;
			$options['appid']= self::$_set['wxappid'];
			$options['appsecret']= self::$_set['wxappsecret'];
			self::$_wx=new \Joel\wx\Wechat($options);
			//换存商城配置
			self::$_shopset=M('Shop_set')->find();
			//缓存通行证数据模型
			self::$_ppvip=M('Vip');
			self::$_ppvipmessage=M('Vip_message');
			self::$_fx=M('Vip');
			self::$_fxlog=M('Fx_log_sub');
			self::$_fxs=M('Fxs_user');
			self::$_fxslog=M('Fxs_log_sub');
			
			
			//判断验证模式
			if(IS_GET){
				self::$_wx->valid();
			}else{
				if(!self::$_wx->valid(true)){die('no access!!!');}
				//读取微信平台推送来的信息类型存全局
				self::$_revtype=self::$_wx->getRev()->getRevType();
				//读取微型平台推送来的信息存全局
				self::$_revdata=self::$_wx->getRevData();
				self::$_wecha_id=self::$_wx->getRevFrom();
				//读取用户地理信息
				//self::$_location=self::$_wx->getRevData();
				foreach(self::$_revdata as $k=>$v){
					$str=$str.$k."=>".$v.'  ';
				}
				file_put_contents('Joel_rev.txt','收到请求:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
				
			}
			
			
		}
		
	public function index(){
		
		$this->go();
    
	}//index类结束
	
	/*微信访问判断主路由控制器by Joel
	  return 
	*/
	public function go(){
		//全局刷新用户头像
		//$reUP=$this->updateUser(self::$_wecha_id);
		switch(self::$_revtype) {
			case \Joel\wx\Wechat::MSGTYPE_TEXT:
				  $this->checkKeyword(self::$_revdata['Content']);
				  //self::$_wx->text(self::$_revdata['Content'])->reply();
			break;
			case \Joel\wx\Wechat::MSGTYPE_EVENT:
				 $this->checkEvent(self::$_revdata['Event']);
			break;
			case \Joel\wx\Wechat::MSGTYPE_IMAGE:
				//$this -> checkImg();
				self::$_wx->text('本系统暂不支持图片信息！')->reply();
			break;
			default:
			self::$_wx->text("本系统暂时无法识别您的指令！")->reply();
		}
		
	}//end go
	
	public function updateUser($openid){
		$old=self::$_ppvip->where(array('openid'=>$openid))->find();
		//if((time()-$old['cctime'])>86400){
		if((time()-$old['cctime'])>86400){
			$user=self::$_wx->getUserInfo($openid);
			//当成功拉去数据后
			if($user){
				$old['cctime']=time();
				$old['nickname']=$user['nickname'];
				$old['headimgurl']=$user['headimgurl'];
				$re=self::$_ppvip->where(array('id'=>$old['id']))->save($old);
			}else{
				$str='更新用户资料失败，用户为：'.$openid;
				file_put_contents('Joel_fail.txt','微信接口失败:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
			}
		}else{
			//1天内，直接保存最后的交互时间
			$old['cctime']=time();
			$re=self::$_ppvip->save($old);
		}
		return ture;
		
	}
	
	/*关键词指引 by Joel
	  return 
	*/
	public function checkKeyword($key){
		
		//self::$_wx->text('尊贵的客户，关键词系统调试中！')->reply();	
		//更新认证服务号的微信用户表信息（24小时内）
		//$reUP=$this->updateUser(self::$_wecha_id);
			
		//Joel调试模式
		if(substr($key,0,5)=='Joel-'){
			$this->toJoel(substr($key,5));
		}
//		if($key=='操作指导'){
//			$msg='如果有什么疑问，请点击下面链接查看：
//
//1.<a href="http://mp.weixin.qq.com/s?__biz=MzA4OTE0ODM4Mg==&mid=204007452&idx=1&sn=ca4d6b8261be53fc6ef325af00224e50#rd">如何关注</a>
//
//2.<a href="http://mp.weixin.qq.com/s?__biz=MzA4OTE0ODM4Mg==&mid=204011553&idx=1&sn=03066d4b9939468ad9f79ba317e9cb5a#rd">如何推广</a>
//
//3.<a href="http://mp.weixin.qq.com/s?__biz=MzA4OTE0ODM4Mg==&mid=203977547&idx=1&sn=a28d1c238dc6e110c71b1284d935d408#rd">如何提现</a>
//
//4.<a href="http://mp.weixin.qq.com/s?__biz=MzA4OTE0ODM4Mg==&mid=203889082&idx=1&sn=410e413fbaaa81af623c4bba42b52202#rd">如何开通微信支付</a>
//
//5.<a href="http://mp.weixin.qq.com/s?__biz=MzA5MjA2NDY3MA==&mid=203604212&idx=1&sn=1e6cb00e967fcb130a1f8c2f0eef420e#rd">如何区分花粉和花蜜</a>
//
//6.<a href="http://mp.weixin.qq.com/s?__biz=MzA4OTE0ODM4Mg==&mid=204411286&idx=1&sn=8d8ffff2a90948319d27009de79d4d1f#rd">如何购买</a>';
//		self::$_wx->text($msg)->reply();
//		}


		if($key=="商城首页"){
			$user=self::$_ppvip->where(array('openid'=>self::$_wecha_id))->find();
			$qd=$user['sid']?$user['sid']:0;
			$keyword=M('wx_keyword')->where(array('keyword'=>'商城首页'))->select();
			foreach($keyword as $k=>$v){
				$ta=$this->getPic($keyword[$k]['pic']);
				$keyword[$k]['imgurl']=$ta['imgurl'];
			}
			$news[0]['Title']=$keyword[0]['name'];
			$news[0]['Description']=$keyword[0]['summary'].$qd;
			$news[0]['PicUrl']=$keyword[0]['imgurl'];
			if($qd){
				$news[0]['Url']=self::$_url.'/Wap/Fxshop/index/ppid/'.$user['id'];
			}else{
				$news[0]['Url']=self::$_url.'/Wap/Shop/index/ppid/'.$user['id'];
			}
			self::$_wx->news($news)->reply();
		}
		//用户自定义关键词匹配
		//*********************************************************************
		if($key!='关注'){
			$mapkey['keyword']=$key;
			//用户自定义关键词
			$keyword=M('Wx_keyword');
			$ruser=$keyword->where($mapkey)->find();
			if($ruser){
				//进入用户自定义关键词回复
				$this->toKeyUser($ruser);
			}
		}
		
		//系统自定义关键词数组
		//$osWgw=array('官网','首页','微官网','Home','home','Index','index');
		//if(in_array($key,$osWgw)){$this->toWgw('index',false);}
		
		//未知关键词匹配
		//*********************************************************************
		$this->toKeyUnknow();
	}

	public function checkEvent($event){
		switch($event){
			//首次关注事件	
			case 'subscribe':
				//用户关注：判断是否已存在
				//检查用户是否已存在
				$old['openid']=self::$_revdata['FromUserName'];
				$isold=self::$_ppvip->where($old)->find();
				if($isold){
					$data['subscribe']=1;
					$re=self::$_ppvip->where($old)->setField('subscribe',1);
					if($isold['pid']){
						$fxvip=self::$_fx->where('id='.$isold['pid'])->find();
						if($fxvip){
								$dlog['from']=$isold['id'];
								$dlog['fromname']=$isold['nickname'];
								$dlog['to']=$fxvip['id'];
								$dlog['toname']=$fxvip['nickname'];
								$dlog['issub']=1;
								$dlog['ctime']=time();
								$rdlog=self::$_fxlog->add($dlog);
								$rfxs=self::$_fx->where('id='.$isold['pid'])->setInc('total_xxsub',1);//下线累计关注
						}
					}					
					//分销商关注代码
					if($isold['sid']){
						//分销商关注
						$fxs=self::$_fxs->where('id='.$isold['sid'])->find();
						if($fxs){
								$dlog['sid']=$isold['sid'];
								$dlog['from']=$isold['id'];
								$dlog['fromname']=$isold['nickname'];
								$dlog['to']=$fxs['id'];
								$dlog['toname']=$fxs['nickname'];
								$dlog['issub']=1;
								$dlog['ctime']=time();
								$rdlog=self::$_fxslog->add($dlog);
								$rfxs=self::$_fxs->where('id='.$isold['sid'])->setInc('total_xxsub',1);//下线累计关注
						}
						//容错，未搜索到分销商不予以统计
					}else{
						$dlog['sid']=0;
						$dlog['from']=$isold['id'];
						$dlog['fromname']=$isold['nickname'];
						$dlog['to']=0;
						$dlog['toname']='品牌总店';
						$dlog['issub']=1;
						$dlog['ctime']=time();
						$rdlog=self::$_fxslog->add($dlog);
					}
					
					$keyword=M('wx_keyword')->where(array('keyword'=>self::$_set['wxsummary']))->find();
					switch($keyword['type']){
						//纯文字
						case '1':
							self::$_wx->text(htmlspecialchars_decode($keyword['summary'],ENT_QUOTES))->reply();
						break;
						//单图文
						case '2':
							$ta=$this->getPic($keyword['pic']);
							$news[0]['Title']=$keyword['name'];
							$news[0]['Description']=$keyword['summary'];//.$qd;
							$news[0]['PicUrl']=$ta['imgurl'];
							$news[0]['Url']=$keyword['url'];
							self::$_wx->news($news)->reply();
						break;
						//多图文						
						case '3':
							$pagelist=M('Wx_keyword_img')->where(array('kid'=>$keyword['id']))->order('sorts desc')->select();
							$news=array();
							foreach($pagelist as $k=>$v){
								$news[$k]['Title']=$v['name'];
								$news[$k]['Description']=$v['summary'];
								$img=$this->getPic($v['pic']);
								$news[$k]['PicUrl']=$img['imgurl'];
								$news[$k]['Url']=$v['url'];
							}
							self::$_wx->news($news)->reply();
						break;
						//单图片
						case '4':
							if(!$keyword['pic']){
								self::$_wx->text($keyword['summary'])->reply();
							}
							$img =$this->getPic($keyword['pic']);				//rtrim($_SERVER['DOCUMENT_ROOT'],'/')
							$imgurl =parse_url($img['imgurl']);
							$media =str_replace('/','\\',$_SERVER['DOCUMENT_ROOT'].$imgurl['path']);
							$imgurl =array('media'=>'@'.$media);
							$va =self::$_wx->uploadMedia($imgurl,'image');
							$media_id =$va['media_id'];
							self::$_wx->image($media_id)->reply();
						break;
						//默认
						default:
							self::$_wx->text(self::$_set['wxunactive'])->reply();
						break;	
					}
				}else{
					$user=$this->apiClient(self::$_revdata['FromUserName']);
					//调试用户端口
//					foreach($user as $k=>$v){
//						$str=$str.$k."=>".$v.'  ';
//					}
//					file_put_contents('Joel_user.txt','收到请求:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
//					
					//首次关注二维码场景值
					if(self::$_revdata['EventKey']){
						$user['sid']=substr(self::$_revdata['EventKey'], 8);
					}
					unset($user['groupid']); //删除多余的
					if($user){
						//新用户注册政策
						$vipset=M('Vip_set')->find();
						$user['score']=$vipset['reg_score'];
						$user['exp']=$vipset['reg_exp'];
						$user['cur_exp']=$vipset['reg_exp'];
						$level=$this->getLevel($user['exp']);
						$user['levelid']=$level['levelid'];
						//追入首次时间和更新时间
						$user['ctime']=$user['cctime']=time();
						//追入用户层级
						$user['pid']=0;
						$user['path']=0;
						$user['plv']=1;
						//设置分销渠道
						if(!self::$_shopset['vipfxneed']){
							$user['isfx']=1;
						}
						$revip=self::$_ppvip->add($user);

						if($revip){
							//赠送操作
							if ($vipset['isgift']) {
										$gift=explode(",", $vipset['gift_detail']);
										$cardnopwd = $this->getCardNoPwd();
										$data_card['type']=$gift[0];
										$data_card['vipid']=$revip;
										$data_card['money']=$gift[1];
										$data_card['usemoney']=$gift[3];
										$data_card['cardno']=$cardnopwd['no'];
										$data_card['cardpwd']=$cardnopwd['pwd'];
										$data_card['status']=1;
										$data_card['stime']=$data_card['ctime']=time();
										$data_card['etime']=time()+$gift[2]*24*60*60;
										$rcard=M('vip_card')->add($data_card);	
									}
									//发送注册通知消息
									//记录日志
									$data_log['ip'] = 'wechat';//源自微信注册
									$data_log['vipid'] = $revip;
									$data_log['ctime'] = time();
									$data_log['openid'] = $user['openid'];
									$data_log['nickname'] = $user['nickname'];
									$data_log['event'] = "会员注册";
									$data_log['score'] = $user['score'];
									$data_log['exp'] = $user['exp'];
									$data_log['type'] = 4;
									$rlog=M('Vip_log')->add($data_log);
						}
						//存在上级渠道，保存渠道关注日志
						if($user['sid']){
							$fxs=self::$_fxs->where('id='.$user['sid'])->find();
							if($fxs){
								$dlog['sid']=$user['sid'];
								$dlog['from']=$revip;
								$dlog['fromname']=$user['nickname'];
								$dlog['to']=$fxs['id'];
								$dlog['toname']=$fxs['nickname'];
								$dlog['issub']=1;
								$dlog['ctime']=time();
								$rdlog=self::$_fxslog->add($dlog);
								$rfxs=self::$_fxs->where('id='.$user['sid'])->setInc('total_xxsub',1);//下线累计关注
							}
							//容错，未搜索到分销商不予以统计
						}else{
							$dlog['sid']=0;
							$dlog['from']=$revip;
							$dlog['fromname']=$user['nickname'];
							$dlog['to']=0;
							$dlog['toname']='品牌总店';
							$dlog['issub']=1;
							$dlog['ctime']=time();
							$rdlog=self::$_fxslog->add($dlog);
						}
						
						$keyword=M('wx_keyword')->where(array('keyword'=>self::$_set['wxsummary']))->find();
						switch($keyword['type']){
							//纯文字
							case '1':
								self::$_wx->text(htmlspecialchars_decode($keyword['summary'],ENT_QUOTES))->reply();
							break;
							//单图文
							case '2':
								$ta=$this->getPic($keyword['pic']);
								$news[0]['Title']=$keyword['name'];
								$news[0]['Description']=$keyword['summary'];//.$qd;
								$news[0]['PicUrl']=$ta['imgurl'];
								$news[0]['Url']=$keyword['url'];
								self::$_wx->news($news)->reply();
							break;
							//多图文						
							case '3':
								$pagelist=M('Wx_keyword_img')->where(array('kid'=>$keyword['id']))->order('sorts desc')->select();
								$news=array();
								foreach($pagelist as $k=>$v){
									$news[$k]['Title']=$v['name'];
									$news[$k]['Description']=$v['summary'];
									$img=$this->getPic($v['pic']);
									$news[$k]['PicUrl']=$img['imgurl'];
									$news[$k]['Url']=$v['url'];
								}
								self::$_wx->news($news)->reply();
							break;
							//单图片
							case '4':
								if(!$keyword['pic']){
									self::$_wx->text($keyword['summary'])->reply();
								}
								$img =$this->getPic($keyword['pic']);				//rtrim($_SERVER['DOCUMENT_ROOT'],'/')
								$imgurl =parse_url($img['imgurl']);
								$media =str_replace('/','\\',$_SERVER['DOCUMENT_ROOT'].$imgurl['path']);
								$imgurl =array('media'=>'@'.$media);
								$va =self::$_wx->uploadMedia($imgurl,'image');
								$media_id =$va['media_id'];
								self::$_wx->image($media_id)->reply();
							break;
							//默认
							default:
								self::$_wx->text(self::$_set['wxsummary'])->reply();
							break;	
						}
					}else{
						$data['openid']=self::$_revdata['FromUserName'];
						$data['subscribe']=1;
						//设置分销渠道
						if(!self::$_shopset['vipfxneed']){
									$data['isfx']=1;
						}
						$re=self::$_ppvip->add($data);
					}					
				} 
							
				//首次关注关键词介入
				self::$_wx->text(self::$_set['wxunactive'])->reply();
				
			break;
			//取消关注事件
			case 'unsubscribe':
				//更新库内的用户关注状态字段
				$map['openid']=self::$_revdata['FromUserName'];
				$old=self::$_ppvip->where($map)->find();
				if($old){
					$rold=self::$_ppvip->where($map)->setField('subscribe',0);
					//分销会员关注日至
					if($old['pid']){
						$fxvip=self::$_fx->where('id='.$old['pid'])->find();
						if($fxvip){
								$dlog['from']=$old['id'];
								$dlog['fromname']=$old['nickname'];
								$dlog['to']=$fxvip['id'];
								$dlog['toname']=$fxvip['nickname'];
								$dlog['issub']=0;
								$dlog['ctime']=time();
								$rdlog=self::$_fxlog->add($dlog);
								$rfxs=self::$_fx->where('id='.$old['pid'])->setInc('total_xxunsub',1);//下线累计关注
						}
					}	
					//分销商关注日志
					if($old['sid']){
						$fxs=self::$_fxs->where('id='.$old['sid'])->find();
						if($fxs){
							$dlog['sid']=$old['sid'];
							$dlog['from']=$old['id'];
							$dlog['fromname']=$old['nickname'];
							$dlog['to']=$fxs['id'];
							$dlog['toname']=$fxs['nickname'];
							$dlog['issub']=0;
							$dlog['ctime']=time();
							$rdlog=self::$_fxslog->add($dlog);
							$rfxs=self::$_fxs->where('id='.$old['sid'])->setInc('total_xxunsub',1);//下线累计取消关注
						}
					}else{
						$dlog['sid']=0;
						$dlog['from']=$old['id'];
						$dlog['fromname']=$old['nickname'];
						$dlog['to']=0;
						$dlog['toname']='品牌总店';
						$dlog['issub']=0;
						$dlog['ctime']=time();
						$rdlog=self::$_fxslog->add($dlog);
					}					
				}				
			break;
			//自定义菜单点击事件
			case 'CLICK':
				$key=self::$_revdata['EventKey'];
				//self::$_wx->text('菜单点击拦截'.self::$_revdata['EventKey'].'!')->reply();
				switch($key){
					case '#sy':
					break;
				}
				//不存在拦截命令,走关键词流程
				$this->checkKeyword($key);
				
			break;
			
		}
   
	}

	public function getqrcode(){
		/*$url='http://'.$_SERVER['HTTP_HOST'].'/Wap/Shop/index/ppid/'.self::$WAP['vipid'];
		$QR=new \Joel\QRcode();
		$QR::png($url);*/
		
		$data = self::$WAP['vip'];
		$bj=I('bj');
		$cache=M('represent')->find();
		
		$bjpic=$this->getPic($cache['img']);
		
		$tjimg=$bjpic['imgurl'];
		$name=$data['nickname'];
//		$img=$_SERVER['DOCUMENT_ROOT']."/Public/tmp/tx.jpg";
		$tjimg=$_SERVER['DOCUMENT_ROOT'].$tjimg;
//		$url=$_SESSION['SYS']['set']['wxurl'].'/wap/shop/index';
		$url='http://'.$_SERVER['HTTP_HOST'].'/Wap/Shop/index/ppid/'.self::$WAP['vipid'];
		$QR=new \Joel\QRcode();
		$mapath=$_SERVER['DOCUMENT_ROOT'].'/QRcode/ma_0.png';
		$QR::png($url,$mapath);
		
		//=====================================
		$width = 340;
		$height = 340;
		list($width_orig, $height_orig) = getimagesize($mapath);
		if ($width && ($width_orig < $height_orig)) {
		    $width = ($height / $height_orig) * $width_orig;
		} else {
		    $height = ($width / $width_orig) * $height_orig;
		}
		$ma = imagecreatetruecolor($width, $height);
		$image = imagecreatefrompng($mapath);
		imagecopyresampled($ma, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		//=============================
		$path=$_SERVER['DOCUMENT_ROOT'].'/Public/tmp/';
		//=============================
		//创建图片的实例
		$background = imagecreatefromstring(file_get_contents($tjimg));
	  	$q_width  = imagesx($ma);
	    $q_height = imagesy($ma);
		
		$fonttype = $path."jht.ttf"; //字体
		$fontcolor = imagecolorallocate($background, 96, 72, 62);
    		imagettftext($background, 28, 0, $cache['uleft'], $cache['utop'], $fontcolor, $fonttype, $name);
		imagecopymerge($background, $ma, $cache['mleft'], $cache['mtop'], 0, 0, $q_width, $q_height, 80);
		//==================================================
		$txath=$img;
		$headimgurl=$data['headimgurl'];
//		if(!file_exists($txath)){
			$this->download_remote_file($headimgurl,$txath);
//		}
		$width = 100;
		$height = 100;
		list($width_orig, $height_orig) = getimagesize($txath);
		if ($width && ($width_orig < $height_orig)) {
		    $width = ($height / $height_orig) * $width_orig;
		} else {
		    $height = ($width / $width_orig) * $height_orig;
		}
		$tx = imagecreatetruecolor($width, $height);
		$image = imagecreatefromjpeg($txath);
		imagecopyresampled($tx, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		$t_width  = imagesx($tx);
	    $t_height = imagesy($tx);
		//====================================================
		imagecopymerge($background, $tx, $cache['uimgleft'], $cache['uimgtop'], 0, 0, $t_width, $t_height, 90);
		header('Content-type: image/jpeg');

        imagejpeg($background);
		
		//销毁内存图片
		imagedestroy($ma);
		imagedestroy($image);
		imagedestroy($tx);
		imagedestroy($background);
	}
	


	/*高级调试模式 by Joel
	  $type=调试命令
	  $Joel-openid:获取用户openid
	*/
	public function toJoel($type){
		$title="Joel管理员模式：\n命令：".$type."\n结果：\n";
		
		switch($type){
			case 'dkf':
				$str="人工客服接入！";
				self::$_wx->dkf($str)->reply();
				break;
			case 'openid':
				self::$_wx->text($title.self::$_revdata['FromUserName'])->reply();
			break;
			default:
			self::$_wx->text("Joel:未知命令")->reply();
		}
		
	}

	/*自定义关键词模式 by Joel
	  $ruser=关键词记录
	*/
	public function toKeyUser($ruser){
		$type=$ruser['type'];
		switch($type){
			//文本
			case "1":
				self::$_wx->text($ruser['summary'])->reply();
			break;
			//单图文
			case "2":
				$news[0]['Title']=$ruser['name'];
				$news[0]['Description']=$ruser['summary'];
				$img=$this->getPic($ruser['pic']);
				$news[0]['PicUrl']=$img['imgurl'];
				$news[0]['Url']=$ruser['url'];
				self::$_wx->news($news)->reply();
			break;
			//多图文
			case "3":
				$pagelist=M('Wx_keyword_img')->where(array('kid'=>$ruser['id']))->order('sorts desc')->select();
				$news=array();
				foreach($pagelist as $k=>$v){
					$news[$k]['Title']=$v['name'];
					$news[$k]['Description']=$v['summary'];
					$img=$this->getPic($v['pic']);
					$news[$k]['PicUrl']=$img['imgurl'];
					$news[$k]['Url']=$v['url'];
				}
				self::$_wx->news($news)->reply();
			break;
			//图片
			case "4":
				if(!$ruser['pic']){
					self::$_wx->text($ruser['summary'])->reply();
				}
				$img =$this->getPic($ruser['pic']);				//rtrim($_SERVER['DOCUMENT_ROOT'],'/')
				$imgurl =parse_url($img['imgurl']);
				$media =str_replace('/','\\',$_SERVER['DOCUMENT_ROOT'].$imgurl['path']);
				$imgurl =array('media'=>'@'.$media);
				$va =self::$_wx->uploadMedia($imgurl,'image');
				$media_id =$va['media_id'];
				self::$_wx->image($media_id)->reply();
			default:
				self::$_wx->text(self::$_set['wxsummary'])->reply();
			break;
		}
	}
	
	/*未知关键词匹配 by Joel
	*/
	public function toKeyUnknow(){
		self::$_wx->text(self::$_set['wxunactive'])->reply();
	}

	
	/*具体微管网推送方式 by Joel
	  $type=对应应用的类型
	  $imglist=true/false 是否以多条返回/最多10条
	*/
	public function toWgw($type,$imglist){
		$wgw=F(self::$_uid."/config/wgw_set");//微官网设置缓存
		switch($type){
			case 'index':
				  //准备各项参数
				  $title=$wgw['title']?$wgw['title']:'欢迎访问'.self::$_userinfo['wxname'];
				  $summary=$wgw['summary'];
				  $picid=$wgw['pic'];
				  $picurl=$picid?$this->getPic($picid):false;
				  //封装图文信息
				  $news[0]['Title']=$title;
				  $news[0]['Description']=$summary;
				  $news[0]['PicUrl']=$picurl['imgurl']?$picurl['imgurl']:'#';
				  $news[0]['Url']=self::$_url.'/Wap/Wgw/Index/uid/'.self::$_uid;
				  //推送图文信息
				  self::$_wx->news($news)->reply();
			break;
		}
	}
	
	
	
	/*将图文信息封装为二维数组 by Joel
	  $array(Title,Description,PicUrl,Url),$return=false
	  Return:新闻数组/或直接推送
	*/
	public function makeNews($array,$return=false){
		if(!$array){die('no items!');}
		$news[0]['Title']=$array[0];
		$news[0]['Description']=$array[1];
		$news[0]['PicUrl']=$array[2];
		$news[0]['Url']=$array[3];
		if($return){
			return $news;
		}else{
			self::$_wx->news($news)->reply();
		}
	}
	
	
	/*获取单张图片 by Joel
	  return 
	*/
	public function getPic($id){
		$m=M('Upload_img');
		$map['id']=$id;
		$list=$m->where($map)->find();
		$list['imgurl']=self::$_url."/upload/".$list['savepath'].$list['savename'];
		return $list?$list:false;
	}
	//根据微信接口获取用户信息
	//return array/false 用户信息/未获取。
	public function apiClient($openid){
		$user=self::$_wx->getUserInfo($openid);
		return $user?$user:FALSE;	
	}
	/*认证服务号微信用户资料更新 by Joel
	  return 
	*/
//	public function updateUser($openid){
//		$old=self::$_ppvip->where(array('openid'=>$openid))->find();
//		if((time()-$old['cctime'])>86400){
//			$user=self::$_wx->getUserInfo($openid);
//			//当成功拉去数据后
//			if($user){
//				$user['cctime']=time();
//				$re=self::$_ppvip->where(array('id'=>$old['id']))->save($user);
//			}else{
//				$str='更新用户资料失败，用户为：'.$openid;
//				file_put_contents('Joel_fail.txt','微信接口失败:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
//			}
//		}else{
//			//1天内，直接保存最后的交互时间
//			$old['cctime']=time();
//			$re=self::$_ppvip->save($old);
//		}
//		return ture;
//		
//	}
	
	///////////////////增值方法//////////////////////////
	public function getlevel($exp) {
		$data=M('Vip_level')->order('exp')->select();
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

}//API类结束