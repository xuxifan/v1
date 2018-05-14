<?php
/**
 * 定制砸金蛋
 */
namespace Wap\Controller;
use Wap\Controller\BaseController;
class DzpController extends BaseController {
	// 首页的刷新条目数
	public static $psizee = 3;
	private $fxtimes=0;

	public function _initialize() {
		header("Content-type:text/html;charset=utf-8");
		parent::_initialize();

		// dzp
		$shopset=M('Shop_set')->where("id=1")->find();
		// DZP这里就可以取到uid
		if($shopset['pic']){
			$listpic=$this->getPic($shopset['pic']);
			$shopset['sharepic']=$listpic['imgurl'];
		}
		if($shopset){
			self::$WAP['shopset']=$_SESSION['WAP']['shopset']=$shopset;
			$this->assign('shopset',$shopset);
		}else{
			$this->diemsg(0,'您还没有进行商城配置！');
		}
		//追入分享特效
		self::$SET=$_SESSION['SET']=$this->checkSet();
		self::$_wxappid=self::$SET['wxappid'];
		self::$_wxappsecret=self::$SET['wxappsecret'];
		$options['appid']= self::$_wxappid;
		$options['appsecret']= self::$_wxappsecret;
		$wx = new \Joel\wx\Wechat($options);
		//生成JSSDK实例
		$opt['appid']= self::$_wxappid;
		$opt['token']=$wx->checkAuth();
		// dump($opt['token']);die;
		$opt['url']="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$jssdk=new \Joel\wx\Jssdk($opt);
		$jsapi=$jssdk->getSignPackage();
		// dump($jsapi['signature']);die;
		if(!$jsapi){
			die('未正常获取数据！');
		}
		$this->assign('jsapi',$jsapi);
		
		// 判断后台是否开启需要关注
		// 获取大转盘id
		$map['id']=I("id");
		$map['status']=1;
		$dzp=M("Dzp")->where($map)->find();
		// 是否得到大转盘数据
		if($dzp){
			if($dzp['needsubscribe']){
				// 需要关注
				// 判断用户是否关注
				if(!$_SESSION['WAP']['vip']['subscribe']){
					// 如果没有关注，则跳转到关注页面
					$url=M("Set")->where("id=1")->getField("wxsuburl");
					$this->redirect($url);
				}
			}
		}else{
			header("Content-type: text/html; charset=utf-8");
			die('没有这个活动！');
		}
	}
	
	/**
	 *  首页
	 */
	public function index() {
		$vipid=$_SESSION['WAP']['vipid'];
		// 大转盘ID
		$cjdata['id']=$id = I('id');
		$this->assign('dzpid',$id);
		$this->assign('vipid',$_SESSION['WAP']['vipid']);
		
		//表单字段显示
		$acform=M("acform");
		$acfield=M("acform_fields");
		$acdata=M("acform_data");
		$where['dzpid']=$id;
		$cache=$acform->where($where)->find();
		$fields=$cache['fields'];
		$fields=explode(',', $fields);
		$ac=array();
		foreach($fields as $k =>$v){
			$fwh['id']=$v;
			$re=$acfield->where($fwh)->find();
			if($re){
				$ac[]=$re;
			}
		}
		//显示表单信息
		$openid=$_SESSION['WAP']['vip']['openid'];
		$redata=$acdata->where(array('openid'=>$openid,'dzpid'=>$id))->select();
		if($redata){
			foreach($ac as $k =>$v){
				$acv['openid']=$openid;
				$acv['value']=$v['fields'];
				$re=$acdata->where($acv)->find();
				$ac[$k]['v']=$re;
			}
		}
		$this->assign('ac',$ac);
		
		// 将奖品遍历上去
		$p=M("Dzp_prize");
		$map['dzpid']=$id;
		$prize=$p->where($map)->order('level')->select();
		// 显示大转盘的总设置
		$d=M("Dzp");
		$dzp=$d->where('id='.$id)->find();
		// 处理活动说明换行
		$content=$dzp['content'];
		$content=trim($content,';;');
		$content=explode(";;",$content);
		$content=implode("<br>",$content);
		$dzp['content']=$content;
		// 处理活动说明换行
		if(!$dzp){
			header("Content-type:text/html;charset=utf-8");
			die("没有这个活动！");
		}
		// 遍历大转盘的图片
		$cjdata['cjimg1']=$this->getPic($dzp['dzpimg1']);
		$cjdata['cjimg2']=$this->getPic($dzp['dzpimg2']);
		// 遍历大转盘的图片
		// 1-15 改变兑奖的时间戳
		$dzp['kstime']=date("Y-m-d H:i:s",$dzp['kstime']);
		$dzp['jstime']=date("Y-m-d H:i:s",$dzp['jstime']);
		// 1-15 改变兑奖的时间戳
		// 1-14 是否需要填写信息
		$cjdata['isform']=$dzp['isform'];
		// 1-14 是否需要填写信息

		// 1-14 在抽奖前填写还是在抽奖后填写
		if($cjdata['isform']){
			// 1为抽奖后  0为抽奖前
			$cjdata['isgz']=$dzp['isgz'];
			// 判断用户是否已经填写过个人信息
			$map_acform['dzpid']=$id;
			$map_acform['openid']=$_SESSION['sqopenid'];
			if(M("Acform_data")->where($map_acform)->find()){
				// 填写过信息
				$cjdata['hastx']=1;
			}else{
				$cjdata['hastx']=0;
			}
		}

		// 1-13  插如图片
		//插如图片
		$dzp['addrpic']=$this->getPic($dzp['addrpic']);

		//分享图片
		$listpic=$this->getPic($dzp['sharesrc']);
		$picurl=$listpic['imgurl'];
		$this->assign('picurl',$picurl);
		
		$dz=M("Dzp_zj");
		$zj=$dz->where($map)->limit("0,".self::$psizee)->select();
		// 统计用户的私人数据
		// 今日剩余次数
		$getNum=$this->getNum($dzp);
		$cjdata['title']=$dzp['name'];
		$cjdata['tel']=$dzp['tel'];
		$cjdata['address']=$dzp['address'];
		$cjdata['num']=$getNum['num'];
		$cjdata['ist']=$getNum['ist'];
		if($cjdata['ist']){
			$cjdata['lnum']=$dzp['daytimes'];
		}else{
			$cjdata['lnum']=$dzp['cjtimes'];
		}

		// 1-13 zyf 分享次数
		if($dzp['isfx']){
			// 如果开启了分享
			$fx=$this->countShare($dzp);
			if($fx['times']){
				// 有分享获得的抽奖次数
				$cjdata['fx']=1;
			}else{
				// 没有分享获得的抽奖次数
				$cjdata['fx']=0;
			}
		}

		$cjdata['count']=$this->getCount();

		// 于用户相关的信息
		$user['id']=$_SESSION['WAP']['vip'];
		// 判断用户是否已经中过奖
		$zj_map['vipopenid']=$_SESSION['sqopenid'];
		$zj_map['dzpid']=$id;
		$zj_res=$dz->where($zj_map)->select();
		if($zj_res){
			//已中奖
			$user['openprize']=1;
			if($zj_res['address'] && $zj_res['mobile'] && $zj_res['viptruename']){
				// 用户信息已填写完整
				$user['is_info']=1;
			}
		}
		$user['sn']=$zj_res['sn'];
		$user['prizename']=$zj_res['prizename'];
		$user['id']=$zj_res['id'];
		// 免费次数用完后的抽奖消耗物品
		$pay['type']=$dzp['paytype'];
		// 如果参数出错则不让抽奖
		$pay['name']=$this->getPayName($pay['type']);
		if(!$pay['name']){
			$pay['type']=0;
		}else{
			$pay['num']=$dzp[$pay['type']];
		}
		// token机制
		$_SESSION['dzpToken']=$this->getToken();
		// 免费次数用完后的抽奖消耗物品
		if ($id && $_SESSION['sqopenid']) {
			$this->assign('zj',$zj);
			$this->assign('zjrepeat',$dzp['zjrepeat']);
			$this->assign("dzpToken",$_SESSION['dzpToken']);
			$this->assign("zj_res",$zj_res);
			$this->assign("pay",$pay);
			$this->assign("cjdata",$cjdata);
			$this->assign("prize",$prize);
			$this->assign("dzp",$dzp);
			$this->assign("dzp_zj",$user);
			$this->assign("user",$user);
			// 这里将会进行cjmode的判断
			$this->display();
		} else {
		 	header("Content-type: text/html; charset=utf-8");
			die('网络超时！请刷新页面重试！');
			 // $this->display();
		}
	}

	// 产生唯一的token
	protected function getToken(){
		return uniqid();
	}

	protected function getPayName($type){
		switch($type){
			case "sj":
				return "水晶";
				break;
			case "jf":
				return "积分";
				break;
		}
	}

	// 获得刷新次数
	protected function getCount(){
		$num=M("Dzp_zj")->count();
		return ceil($num/self::$psizee);
	}

	// 今日剩余次数
	protected function getNum($dzp){
		// 判断用户的免费抽奖次数是否足够
		// 判断他总抽奖次数是否足够
		$Dzp_log_map["openid"]=$_SESSION['sqopenid'];
		$Dzp_log_map["dzpid"]=$dzp['id'];
		$all_times=M("Dzp_log")->where($Dzp_log_map)->count();
		// 如果总抽奖次数有限制
		if($dzp['cjtimes']){
			if($all_times>=$dzp['cjtimes'] && $dzp['cjtimes']!=0){
				$info["status"]=1;
				$info['info']="您已经没有抽奖次数了哦~";
				$cjdata['num']=0;
				$cjdata['ist']=0;
				$cjdata['info']=$info;
				return $cjdata;
				// $this -> ajaxReturn($info);
			}else{
				// 剩下的总次数
				$res_num1=$dzp['cjtimes']-$all_times;
			}
		}else{
			// 无总抽奖次数限制
			$res_num1="NaN";
		}
		
		// 判断他每日抽奖次数是否足够
		// 判断是否开启无限抽
		if($dzp['daytimes']){
			// 一日之晨
			$stime=strtotime(date("Y-m-d",time())."00:00:01");
			// 一日之末
			$etime=strtotime(date("Y-m-d",time())."23:59:59");
			$day_map['dzpid']=$dzp['id'];
			$day_map['openid']=$_SESSION['sqopenid'];
			$day_map['ctime']=array(array('gt',$stime),array('lt',$etime),'and');
			$day_times=M("Dzp_log")->where($day_map)->count();
			// 超出的每日的抽奖次数
			if($day_times>=$dzp['daytimes']){
				$info["status"]=1;
				$info['info']="您今天的免费次数已经抽完了哦~";
				$cjdata['num']=0;
				$cjdata['ist']=1;
				$cjdata['info']=$info;
				return $cjdata;
				// $this -> ajaxReturn($info);
			}else{
				// 剩下的总次数
				$res_num2=$dzp['daytimes']-$day_times;
			}
		}else{
			$res_num2="NaN";
		}
		if($res_num1=="NaN"){
			// 无总抽奖次数限制
			// 用户剩余的抽奖次数
			if($res_num2=="NaN"){
				$cjdata['num']="无限";
			}else{
				$cjdata['num']=$res_num2;
			}
		}else{
			// 有总抽奖次数限制
			// 用户剩余的抽奖次数
			// 如果当日抽奖次数无限
			if($res_num2=="NaN"){
				$cjdata['num']=$res_num1;
			}else{
				$cjdata['num']=min($res_num1,$res_num2);
			}
		}
		return $cjdata;
	}

	// 内用测试用方法
	private function testNum(){
		// 模拟测试人数
		$num=I("num");
		for($i=0;$i<$num;$i++){
			$this-> saveCjResult();
		}
	}

	public function saveCjResult(){                                   
		if(IS_AJAX){
			// 判断Token是否正确
			if(I('token')!=$_SESSION['dzpToken']){
				$info['status']=1;
				$info['info']="非法访问~";
				$this->ajaxReturn($info);
			}else{
				// 清楚session中的token
				unset($_SESSION['dzpToken']);
			}
			if(!I("id")){
				// 参数错误
				$info['status']=1;
				$info['info']="非法访问~";
				$this->ajaxReturn($info);
			}
			$map['id']=$zj_data['dzpid']=I("id");
			$zj_data['nickname']=$_SESSION['WAP']['vip']['nickname'];
			$zj_data['ctime']=time();
			$d=M("Dzp");
			$dzp=$d->where($map)->find();
			unset($map);
			$now=time();
			$etime=$dzp['etime'];
			$stime=$dzp['stime'];
			// 判断活动是否开始
			if($now<$stime){
				$info['status']=1;
				$info['info']="本次活动还没开始，赶紧邀请好友碰碰运气吧~";
				$info['token']=$_SESSION['dzpToken']=$this->getToken();
				$this->ajaxReturn($info);
			}
			// 判断活动是否结束
			if($now>$etime){
				$info['status']=1;
				$info['info']="本次活动已经结束了，请关注我们的下次活动吧~";
				$info['token']=$_SESSION['dzpToken']=$this->getToken();
				$this->ajaxReturn($info);
			}
			// 判断活动是否开启
			if(!$dzp['status']){
				$info['status']=1;
				$info['info']="这个活动还没开始哦，请时刻关注我们吧~";
				$info['token']=$_SESSION['dzpToken']=$this->getToken();
				$this->ajaxReturn($info);
			}
			// 获取用户的基本信息
			// 获取用户的openid
			$data['openid']=$zj_data['vipopenid']=$_SESSION['sqopenid'];
			// 获取用户的ip地址
			$log_data['ip']=$data['ip']=get_client_ip();
			// 1-13 是否允许重复中奖
			// 如果没有开启，则判断是否已经中奖
			if(!$dzp['zjrepeat']){
				// 判断该用户是否已经中过奖
				$zj_map['dzpid']=I("id");
				$zj_map['vipopenid']=$_SESSION['sqopenid'];
				$re=M("Dzp_zj")->where($zj_map)->find();
				if($re){
					// 如果中过奖
					$info['status']=1;
					$info['info']="您已中过奖了~";
					$info['token']=$_SESSION['dzpToken']=$this->getToken();
					$this->ajaxReturn($info);
				}
			}
			// 1-13 是否允许重复中奖

			// 判断是否为  手动派奖用户
			$dvip=M("Dzp_vip");
			// 实例化
			$p=M("Dzp_prize");
			$vip_map['openid']=$_SESSION['sqopenid'];
			$vip_map['dzpid']=I("id");
			$vip_map['status']=0;
			$Dzp_vip=$dvip->where($vip_map)->find();
			if($Dzp_vip){
				// 开启事务
				$dvip->startTrans();
				// 为手动派奖用户
				// 手动派奖用户不需要减去库存
				// 直接写入log表和zj表
				$map_p['id']=$Dzp_vip['prizeid'];
				$vip_prize=$p->where($map_p)->find();
				// 不能排除奖品已被删除
				if($vip_prize){
					// 保存到dzp_log
					$log_data['dzpid']=I("id");
					$log_data['openid']=$_SESSION['sqopenid'];
					$log_data['ip']=get_client_ip();
					$log_data['vipid']=$_SESSION['WAP']['vipid'];
					$log_data['result']="获得".$vip_prize['lname'];
					$log_data['ctime']=time();
					$re=M("Dzp_log")->add($log_data);
					// 保存到dzp_zj
					$zj_data['dzpid']=I("id");
					$zj_data['vipid']=$_SESSION['WAP']['vipid'];
					$zj_data['nickname']=$_SESSION['WAP']['vip']['nickname'];
					$zj_data['vipopenid']=$_SESSION['sqopenid'];
					$zj_data['prizeid']=$vip_prize['id'];
					$zj_data['ip']=get_client_ip();
					$zj_data['prizename']=$vip_prize['pname'];
					$zj_data['ctime']=time();
					$zj_data['sn']=uniqid();
					$ree=M('Dzp_zj')->add($zj_data);
					// 手动派奖用户成功中奖
					$Dzp_vip['status']=1;
					$reee=$dvip->save($Dzp_vip);
					if($re && $ree && $reee){
						// 成功
						$dvip->commit();
						$res['id']=$vip_prize['level'];
						$res['token']=$_SESSION['dzpToken']=$this->getToken();
						$this->ajaxReturn($res);
					}else{
						$dvip->rollback();
						$info['status']=1;
						$info['info']="未知错误~";
						$info['token']=$_SESSION['dzpToken']=$this->getToken();
						$this->ajaxReturn($info);
					}
				}else{
					$dvip->rollback();
					$info['status']=1;
					$info['info']="未知错误~";
					$info['token']=$_SESSION['dzpToken']=$this->getToken();
					$this->ajaxReturn($info);
				}
			}
		
		
			// 根据用户的消费方式判断用户是否可以进行抽奖
			// 消费方式
			$paytype=I("paytype");
			if($paytype){
				// 判断总抽奖次数是否足够
				$Dzp_log_map["openid"]=$_SESSION['sqopenid'];
				$Dzp_log_map['dzpid']=I('id');
				$all_times=M("Dzp_log")->where($Dzp_log_map)->count();
				if($dzp['cjtimes']<=$all_times){
					$info["status"]=1;
					$info['info']="您已经没有抽奖次数了哦~";
					$this->ajaxReturn($info);
				}
				switch($paytype){
					// 水晶
					case "sj":
						$xmoney=$_SESSION['WAP']['vip']['xmoney'];
						if($xmoney>=$dzp['sj']){
							$xmoney-=$dzp['sj'];
							$_SESSION['WAP']['vip']['xmoney']=$xmoney;
							$vipmap['openid']=$_SESSION["sqopenid"];
							$re=M('Vip')->where($vipmap)->setField('xmoney',$xmoney);
							if(!$re){
								$info['status']=1;
								$info['info']="扣除水晶失败~";
								$info['token']=$_SESSION['dzpToken']=$this->getToken();
								$this->ajaxReturn($info);
							}
						}else{
							$info['status']=1;
							$info['info']="您的水晶不足哦，快去充值水晶吧~";
							$info['token']=$_SESSION['dzpToken']=$this->getToken();
							$this->ajaxReturn($info);
						}
						break;
					// 积分
					case "jf":
						$score=$_SESSION['WAP']['vip']['score'];
						if($score>=$dzp['jf']){
							$score-=$dzp['jf'];
							$_SESSION['WAP']['vip']['xmoney']=$score;
							$vipmap['openid']=$_SESSION["sqopenid"];
							$re=M('Vip')->where($vipmap)->setField('score',$score);
							if(!$re){
								$info['status']=1;
								$info['info']="扣除积分失败~";
								$info['token']=$_SESSION['dzpToken']=$this->getToken();
								$this->ajaxReturn($info);
							}else{
								// 计入日志
								$point_data['sid']=$_SESSION['WAP']['vip']['sid'];
								$point_data['vipid']=$_SESSION['WAP']['vipid'];
								$point_data['openid']=$_SESSION['WAP']['vip']['openid'];
								$point_data['type']="dzp";
								$point_data['goodsid']=$dzp['id'];
								$point_data['name']=$dzp['name'];
								$point_data['integpay']=$dzp['jf'];
								$img=$this->getPic($dzp['sharesrc']);
								$point_data['pic']=$img['imgurl'];
								$point_data['ctime']=time();
								$re=M("Points_log")->add($point_data);
								if(!$re){
									$info['status']=1;
									$info['info']="扣除积分失败~";
									$info['token']=$_SESSION['dzpToken']=$this->getToken();
									$this->ajaxReturn($info);
								}
							}
						}else{
							$info['status']=1;
							$info['info']="您的积分不足哦，快去充值兑换吧~";
							$info['token']=$_SESSION['dzpToken']=$this->getToken();
							$this->ajaxReturn($info);
						}
						break;
					default:
						$info['status']=1;
						$info['info']="出错啦~";
						$info['token']=$_SESSION['dzpToken']=$this->getToken();
						$this->ajaxReturn($info);
						break;
				}
			}else{
				// // 判断用户的免费抽奖次数是否足够
				// // 判断他总抽奖次数是否足够
				$getnum=$this->getNum($dzp);
				$fx=$this->countShare($dzp);
				$getnum['num']--;
				if($getnum['info']){
					// 1-13 zyf 分享获得次数
					if($fx['status']){
						// 开启了分享
						if($fx['times']){
							// 如果可以使用分享得到的次数
							$vip_fx_map['dzpid']=$dzp['id'];
							$vip_fx_map['openid']=$_SESSION['sqopenid'];
							$vip_fx_map['ctime']=$fx['ctime'];
							$re=M("Dzp_vip_fx")->where($vip_fx_map)->setInc("usetimes");
							$fx['times']--;
							if(!$re){
								$info['status']=1;
								$info['info']="出错啦，请刷新重试~";
								$info['token']=$_SESSION['dzpToken']=$this->getToken();
								$this->ajaxReturn($info);
							}else{
								$res['num']=$fx['times'];
							}
						}else{
							// 如果次数不够
							$info['status']=1;
							$info['info']="次数不够啦，您可以通过分享来获得抽奖次数哦~";
							$info['token']=$_SESSION['dzpToken']=$this->getToken();
							$this->ajaxReturn($info);
						}
					}else{
						$getnum['info']['token']=$_SESSION['dzpToken']=$this->getToken();
						$this->ajaxReturn($getnum['info']);
					}
					// 1-13 zyf 分享获得次数
					// 这里判断
				}else{
					// 剩余次数加上
					$res['num']=$getnum['num']+$fx['times'];
				}
			}
			
			// 判断是否开启可控
			if($dzp['ifcontrol']){
				$res['id']=$this->getRandIsControl(I("id"));
			}else{
				$res['id']=$this->getRand(I("id"));
			}

			$log_data['dzpid']=I('id');
			$log_data['vipid']=$_SESSION['WAP']['vipid'];
			$log_data['openid']=$_SESSION['sqopenid'];
			$log_data['result']=$this->rank($res['id']);
			$log_data['ctime']=time();
			$re=M('Dzp_log')->add($log_data);
			$res['id']=$res['id']?$res['id']:0;
			$res['token']=$_SESSION['dzpToken']=$this->getToken();
			$this -> ajaxReturn($res);
		}else{
			header("Content-type: text/html; charset=utf-8");
			//die("非法访问！");
		}
	}

	// 计算是否开启分享，和分享后剩余的抽奖次数
	protected function countShare($dzp){
		// 1-13 zyf 判断是否开启分享获得次数
		if($dzp['isfx']){
			$info['status']=1;
			$time=time();
			// 得到在今天的有效时间内分享了几次，计算出今日获得的额外抽奖次数
			// 今天刷新时间的时间戳
			$refreshtime=strtotime(date("Y-m-d",$time)." ".$dzp['refreshtime']);
			// 比较区间
			if($time>=$refreshtime){
				$map['ctime']=strtotime(date("Y-m-d",$time));
			}else{
				$map['ctime']=strtotime(date("Y-m-d",$time))-60*60*24;
			}
			$info['ctime']=$map['ctime'];
			// 得到今日分享了几次
			$map['dzpid']=$dzp['id'];
			$map['openid']=$_SESSION['sqopenid'];
			$vip_fx=M("Dzp_vip_fx")->where($map)->find();
			if($vip_fx){
				// 已经分享过
				// 分享后获得的机会// $vip_fx['gettimes'];
				// 今天使用的次数  // $vip_fx['usetimes'];
				// 今天剩余的次数
				$info['times']=$vip_fx['gettimes']-$vip_fx['usetimes'];
			}else{
				// 没有分享过
				$info['times']=0;
			}
		}else{
			$info['status']=0;
		}
		return $info;
		// 1-13 zyf 判断是否开启分享获得次数
	}


	protected function getPrize($info,$dzpid){
		// 随机抽奖的产生
		$odds=$info['odds'];
		// 基数
		$maxmun=0;
		foreach($odds as $k=>$v){
			$maxmun=$maxmun?$maxmun*ceil(1/$v):ceil(1/$v);
		}
		// 产生一个随机数
		$sjs=rand(1,$maxmun);
		// 个个奖品的概率
		$count=0;
		foreach($odds as $k=>$v){
			$odds_a=$v*$maxmun;
			if($sjs>$count && $sjs<=($odds_a+$count)){
				$res['id']=$k;
				break;
			}
			$count+=$odds_a;
		}
		// 得到抽奖结果后，判断奖品的库存是否足够，不足的话设为未中奖
		// 是否获奖
		if($res['id']){
			// 开启事务
			$p=M("Dzp_prize");
			$p->startTrans();
			$map['dzpid']=$dzpid;
			$map['level']=$res['id'];
			//获奖，判断库存是否足够
			$prize_goods=$p->where($map)->find();
			$leave_num=$prize_goods['store']-$prize_goods['sell'];//<=0
			// 今天获奖数
			$now1=strtotime(date("Y-m-d",time())." 00:00:00");
			$now2=strtotime(date("Y-m-d",time())." 23:59:59");
			$zj_map['ctime']=array(array('egt',$now1),
								   array('elt',$now2),
								   'and');
			$zj_map['prizeid']=$prize_goods['id'];
			// dump($prize_goods['id']);
			// 查询中奖人数
			$day_zj=M('Dzp_zj')->where($zj_map)->count();
			// dump(floor($info['daygiven']*$odds[$res['id']]));
			// dump($day_zj);
			// 如果库存足够并且符合每日的派送数量
			$o=$leave_num/$info['leavePrize'];
			if($leave_num<=0 || $day_zj>=ceil($info['daygiven']*$o)){
				// 奖品数量都不够
				unset($res['id']);
			}else{
				// 库存足够
				// 减去库存
				$prize_goods['sell']++;
				$re=$p->save($prize_goods);
				if($re!==FALSE){
					// 将数据记录到 中奖日志中
					$zj_data['dzpid']=$dzpid;
					$zj_data['prizeid']=$prize_goods['id'];
					$zj_data['prizename']=$prize_goods['pname'];
					$zj_data['vipid']=$_SESSION['WAP']['vipid'];
					$zj_data['vipopenid']=$_SESSION['sqopenid'];
					$zj_data['nickname']=$_SESSION['WAP']['vip']['nickname'];
					$zj_data['ip']=get_client_ip();
					$zj_data['sn']=uniqid();
					$zj_data['ctime']=time();
					$re2=M("Dzp_zj")->add($zj_data);
					if(!$re2){
						$p->rollback();
						unset($res['id']);
					}else{
						$p->commit();
					}
				}else{
					$p->rollback();
					unset($res['id']);
				}
			}

		}
		// dump($res['id']);
		// $log_data['dzpid']=I('id');
		// $log_data['vipid']=$_SESSION['WAP']['vipid'];
		// $log_data['openid']=$_SESSION['sqopenid'];
		// $log_data['result']=$this->rank($res['id']);
		// $log_data['ctime']=time();
		// $log_data['ip']=get_client_ip();
		// $re=M('Dzp_log')->add($log_data);
		$res['id']=$res['id']?$res['id']:0;
		return $res['id'];
	}

	// 开启可控后，中奖概率将由后台指定
	protected function getRandIsControl($dzpid){
		// 得到指定大转盘所有概率不为0的奖品
		$p=M("Dzp_prize");
		$map['dzpid']=$dzpid;
		$map['odds']=array('neq',0);
		$prize=$p->where($map)->select();
		$odds=array();
		$daygiven=0;
		foreach($prize as $k=>$v){
			$ar=explode('/',$v['odds']);
			$odds[$v['level']]=$ar[0]/$ar[1];
			$daygiven+=$v['store'];
		}
		asort($odds);
		$info['odds']=$odds;
		// 每日派送量，开启可控后就是奖品总数
		$info['daygiven']=$daygiven;
		$info['leavePrize']=$daygiven;
		return $this->getPrize($info,$dzpid);
	}

	/******************
	 *设计:郑伊凡
	 *说明:此算法需要一个预计抽奖人数，
	 *第一天的中奖概率将根据预计中奖人数判断，接下来的几天奖根据前一天的实际抽奖人数进行人数的预判
	 *每天中奖奖品的数量是固定的，防止奖品一次性抽完。
	 ******************/
	protected function getRand($dzpid){
		// 随机抽奖的产生
		$map['id']=$dzpid;//I('id');
		// 获取项目的信息
		$dzp=M("Dzp")->where($map)->find();
		// 判断用户是否已经设置最低人数
		$dzp['low']=$dzp['low']?$dzp['low']:10;
		$map['dzpid']=$map['id'];
		unset($map['id']);
		$prize=M("Dzp_prize")->where($map)->order('level')->select();
		// 现在的时间戳
		$now=strtotime(date("Y-m-d",time()));
		// 活动开始的时间
		$start=strtotime(date("Y-m-d",$dzp['stime'])." 00:00:00");
		//活动结束的时间
		$end=strtotime(date("Y-m-d",$dzp['etime'])." 00:00:00");
		$haveday=($now-$start)/(60*60*24);
		// 剩余天数
		$leaveday=($end-$now)/(60*60*24)+1;
		// dump($leaveday);
		// 为0的话是第一天 依次加一
		// dump($haveday);
		$haveday=($now-$start)/(60*60*24);
		$leavePrize=0;
		foreach($prize as $k=>$v){
			$leavePrize+=$v['store']-$v['sell'];
		}
		// 商品剩余的总量
		// dump($leavePrize);
		// 商品的今日应发放量
		$daygiven=ceil($leavePrize/$leaveday);
		// dump($daygiven);
		if(!$haveday){
			// 第一天的时候
			// 第一天的预计人数
			// $yjrs=$dzp['yjrs'];
			$zj_log_map['dzpid']=$dzpid;
			$zj_log_map['day']=$haveday;
			// 是否已经记录了今天的预计人数
			$yjrs=M('Dzp_zj_log')->where($zj_log_map)->find();
			if(!$yjrs){
				$zj_log_map['yjrs']=$yjrs['yjrs']=$dzp['yjrs'];
				$zj_log_map['ctime']=time();
				M('Dzp_zj_log')->add($zj_log_map);
			}
			$yjrs=$yjrs['yjrs'];
			// 总的中奖概率
			$allodds=$daygiven/$yjrs;
			// 用来计算每个奖品的中奖概率
			$everyodds=array();
			foreach($prize as $k=>$v){
				$everyodds[$v['level']]=(($v['store']-$v['sell'])/$leavePrize)*$allodds;
			}
			asort($everyodds);
		}else{
			// echo 1;
			$zj_log_map['dzpid']=$dzpid;
			$zj_log_map['day']=$haveday-1;
			// 获得昨天的预计人数
			$yjrs=M('Dzp_zj_log')->where($zj_log_map)->getField('yjrs');
			// 如果昨天没人抽奖
			$yjrs=$yjrs?$yjrs:$dzp['low'];
			// 如果不是第一天
			// 前一天的时间戳
			$qday=strtotime("-1 day");
			$qday=strtotime(date("Y-m-d",$qday));
			// 统计实际抽奖人数(前一天的)
			$log_map['ctime']=array(array('egt',$qday),array('lt',$now),'and');
			$log_map['dzpid']=$dzpid;
			// dump($log_map);
			$truenum=M("Dzp_log")->where($log_map)->count();
			// 实际的中奖人数/中奖奖品数量(前一天的)
			$zjnum=M("Dzp_zj")->where($log_map)->count();
			// dump($log_map);
			// 如果实际发放的没有预计发放的多，那么要上调中奖率;
			if($zjnum<$daygiven){
				// echo 2;
				// 如果实际抽奖的人数比预计人数少，则上调中奖率
				if($truenum<=$yjrs){
					// 计算出今天的预计人数 存表
					$new_yjrs=$truenum/$yjrs*$truenum*$dzp['updown'];
					// dump($truenum);
					// dump($yjrs);
					// dump($dzp);
					// dump($new_yjrs);die;
					$zj_log_map['day']=$haveday;
					$yjrs=M('Dzp_zj_log')->where($zj_log_map)->find();
					if(!$yjrs){
						// 没有的话就保存
						$zj_log_map['yjrs']=$new_yjrs;
						$zj_log_map['ctime']=time();
						M('Dzp_zj_log')->add($zj_log_map);
					}
					$yjrs=$yjrs['yjrs'];
					// 如果昨天没人抽奖
					$yjrs=$yjrs?$yjrs:$dzp['low'];
					// 总的中奖概率
					$allodds=$daygiven/$yjrs;
					// 用来计算每个奖品的中奖概率
					$everyodds=array();
					foreach($prize as $k=>$v){
						$everyodds[$v['level']]=(($v['store']-$v['sell'])/$leavePrize)*$allodds;
					}
					asort($everyodds);
				}else{
					// echo 3;
					// 如果实际抽奖的人数比预计人数大，则上调中奖率
					// 计算预算人数 存表
					$new_yjrs=$truenum/$yjrs*$truenum*$dzp['updown'];
					$zj_log_map['day']=$haveday;
					$yjrs=M('Dzp_zj_log')->where($zj_log_map)->find();
					if(!$yjrs){
						// 没有的话就保存
						$zj_log_map['yjrs']=$new_yjrs;
						$zj_log_map['ctime']=time();
						M('Dzp_zj_log')->add($zj_log_map);
					}
					$yjrs=$yjrs['yjrs'];
					// 如果昨天没人抽奖
					$yjrs=$yjrs?$yjrs:$dzp['low'];
					// 总的中奖概率
					$allodds=$daygiven/$yjrs;
					// 上调比率 按实际发送量和预计发送量计算
					$zjnum=max(1,$zjnum);
					$upbl=$daygiven/$zjnum*$dzp['updown'];
					$everyodds=array();
					foreach($prize as $k=>$v){
						$everyodds[$v['level']]=(($v['store']-$v['sell'])/$leavePrize)*$allodds*$upbl;
					}
					asort($everyodds);
				}
			}else{
				// echo 4;
				// 总的中奖概率
				$allodds=$daygiven/$yjrs;
				// 如果实际发放的奖品达到了预计发放的奖品数
				// 用来计算每个奖品的中奖概率
				$everyodds=array();
				foreach($prize as $k=>$v){
					$everyodds[$v['level']]=(($v['store']-$v['sell'])/$leavePrize)*$allodds;
				}
			}
		}
		// 做比例调整
		$sumodds=0;
		foreach($everyodds as $k=>$v){
			$sumodds+=$v;
		}
		if($sumodds>1){
			foreach($everyodds as $k=>$v){
				$everyodds[$k]=$v/$sumodds;
			}
		}
		$info['odds']=$everyodds;
		$info['daygiven']=$daygiven;
		$info['leavePrize']=$leavePrize;
		// dump($info);die;
		return $this->getPrize($info,$dzpid);
	}
	
	public function win(){
		$id = I('id');
		$m = M('dzp_zj');
		$map =array('dzpid'=>$id,'vipopenid'=>$_SESSION['sqopenid']);
		$res=$m->where($map)->find();
		$this -> ajaxReturn($res);
	}
	
	public function zjuser(){
		$id = I('vid');
		$openid=$_SESSION['WAP']['vip']['openid'];
		$lid = I('id');
		$m = M('Dzp_zj');
		$acdata = M('acform_data');
		$data=I('post.');
		$map =array('dzpid'=>$lid,'openid'=>$openid);
		$redata=$acdata->where($map)->select();	
		if($redata){
			foreach($data as $k=>$v){
				
				$vs['dzpid']=$lid;
				$vvv['name']=$v;
				$vs['openid']=$openid;
				$vs['value']=$k;
				$re=$acdata->where($vs)->save($vvv);
				if($re===false){
					$info['status']='0';
					$info['msg']='修改失败';
					$this -> ajaxReturn($info);	
				}
			}
			$info['status']='1';
			$info['msg']='修改成功';
			$this -> ajaxReturn($info);	
		}else{
			foreach($data as $k=>$v){
				$vss['dzpid']=$lid;
				$vss['name']=$v;
				$vss['value']=$k;
				$vss['openid']=$openid;
				$res=$acdata->add($vss);
				if($res===false){
					$info['status']='0';
					$info['msg']='修改失败';
					$this -> ajaxReturn($info);	
				}
			}
			$info['status']='1';
			$info['msg']='修改成功';
			$this -> ajaxReturn($info);	
		}

		$this -> ajaxReturn($info);		
	}

	
	public function getdata(){
		$id = I('id');
		$num = I('num');
		$m = M('Dzp_zj');
		if(!$num){
			echo "参数不全";
		}
		$psize = self::$psizee;
		$map =array('dzpid'=>$id);
		$cache=$m->where($map)->page($num,$psize)->order('id desc')->select();
		foreach ($cache as $k => $v) {
			if($v['nickname']==''){
				$list[$k]['nickname'] ='***';
			}else{					
				for($i=0;$i<mb_strlen($v['nickname'],'utf-8')-1;$i++){
					$x[$k] .="*";
				}
				$list[$k]['nickname'] =mb_substr($v['nickname'], 0,1,'utf-8').$x[$k];
			}		
			$list[$k]['ctime'] =date('Y-m-d',$v['ctime']);
			$list[$k]['prize'] =$v['prizename'];			
		}
		if($cache){
			$info['status'] =1;
			$info['msg'] =$list;
		}else{
			$info['status'] =0;
			$info['msg'] ='暂无数据';
		}
		$this -> ajaxReturn($info);		
	}
	
	function rank($prizrid){
		switch($prizrid){
			case 1:return '获得一等奖';
					break;
			case 2:return '获得二等奖';
					break;
			case 3:return '获得三等奖';
					break;
			case 4:return '获得四等奖';
					break;
			case 5:return '获得五等奖';
					break;
			case 6:return '获得六等奖';	
					break;		
			default :return '未中奖';
					break;
		}
	}
	
	
	//分享修改抽奖次数
	public function shareInfo(){
		$dzpid=$_GET['dzpid'];
		$vipid=$_GET['vipid'];
		$vip=M('vip');
		$dzp=M('dzp');
		$dzpfx=M('dzp_fx');
		$vipfx=M('dzp_vip_fx');		
		$ret=$dzp->where(array('id'=>$dzpid))->find();
		if(!$ret['isfx']){
			$info['status']=0;
			$this->ajaxreturn($info);
		}
		//分享日志
		$openid=$vip->where(array('id'=>$vipid))->getField('openid');
		$ret=$dzp->where(array('id'=>$dzpid))->find();
		$where['openid']=$openid;
		$where['dzpid']=$dzpid;
		$where['ctime']=time();
		$list=$dzpfx->add($where);
		//echo $dzpfx->getLastSql();
		//先判断 dzp_vip_fx 是否有相关的OPENID	,
		//如果有,判断时间是否是当天时间，如果是当天时间 ,分享次数 fxtimes +1 当fxtimes满2得到一次抽奖机会
		//如果不是当天时间 新增一条数据
		//如果没有相关的OPENID	，新增
		$vipopen=$vipfx->where(array('openid'=>$openid))->find();
		$vctime=$vipopen['ctime'];
		if($vipopen){
			
			//活动刷新时间
			
			$retime=$ret['refreshtime'];
			$qtime=date("Y-m-d");
			$start=strtotime($qtime. $retime);
			
			$htime=date("Y-m-d",time()+86400);
			$end=strtotime($htime. $retime);
			
			if($vctime > $start && $vctime < $end){
				
				$map['openid']=$openid;
				$map['ctime']  = array('between',array($start,$end));
				$vips=$vipfx->where($map)->setInc('fxtimes');
				//分享几次   得到几次抽奖次数
				$fxnum=$ret['prefx'];
				$fxtimes=$ret['getfxtimes'];
				
				$vipfxtimes = $vipfx->where($map)->getField('fxtimes');
				if($vipfxtimes > $fxnum){
					$gettimes=floor($vipfxtimes/$fxnum);
					$vipfx->where($map)->setField('gettimes',$gettimes);

				}else{
					$vipfx->where($map)->setField('gettimes',0);

				}
				$info['status']=$vipopen;
				$info['msg']=$vipopen;
			}else{
				$vipop['dzpid']=$dzpid;
				$vipop['openid']=$openid;
				$vipop['ctime']=time();
				$vipop['fxtimes']=1;
				$vipop['gettimes']=0;
				$vipop['usetimes']=0;
				$re=$vipfx->add($vipop);
				if($re){
					$info['status']=1;
					$info['msg']='分享成功';
				}else{
					$info['status']=0;
					$info['msg']='分享失败';
				}
			}

		}else{
			$vipop['dzpid']=$dzpid;
			$vipop['openid']=$openid;
			$vipop['ctime']=time();
			$vipop['fxtimes']=1;
			$vipop['gettimes']=0;
			$vipop['usetimes']=0;
			$re=$vipfx->add($vipop);
			if($re){
				$info['status']=1;
				$info['msg']='分享成功';
			}else{
				$info['status']=0;
				$info['msg']='分享失败';
			}
			
		}
		$this->ajaxreturn($info);
		
	}
}
