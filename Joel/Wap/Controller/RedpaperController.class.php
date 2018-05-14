<?php
/* *
 * 卡主红包砸金蛋
 * 业务逻辑：
 *	   每个用户购买红包的等级只能越来越高。
 *     例如：买了一级红包的用户不再能购买一级红包了，只能买二级红包或三级红包，同理，购买过三级红包的用户，就不在能购买1、2、3级的红包了。
 */
namespace Wap\Controller;
use Wap\Controller\BaseController;
class RedpaperController extends BaseController {

	public static $redpaperset;

	public function _initialize() {
		header("Content-type:text/html;charset=utf-8");
		parent::_initialize();
		$shopset=M('Shop_set')->where("id=1")->find();
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
		$opt['url']="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$jssdk=new \Joel\wx\Jssdk($opt);
		$jsapi=$jssdk->getSignPackage();
		if(!$jsapi){
			die('未正常获取数据！');
		}
		$this->assign('jsapi',$jsapi);
		// 判断是否开启卡主红包
		if($shopset['isredpaper']==0){
			$this->diemsg(0,'本商城未开启卡主红包活动！');
		}
		$img=$this->getPic($shopset['redpic']);
		$imgurl=$img['imgurl'];
		$this->assign('shareimg',$imgurl);
	}

	/**
	 *  首页
	 */
	public function index() {
		$vipid=$_SESSION['WAP']['vipid'];
		$vip=M('Vip')->where('id='.$vipid)->find();
		// 判断用户等级
		$red=M('Redpaper_order')->where('vipid='.$vipid)->find();
		if($red){
			// 判断是否已发红包
			if($red['status']!=0){
				// 根据红包等级计算下线人数
				$red['num']=$this->countNum($red['level']);
			}
			$red['getMoney']=number_format($red['getMoney'],2);
			$red['money']=$this->countGetRadpaper();
			$red['money']['total']=$red['money']['total']?number_format($red['money']['total'],2):"0.00";
		}
		$vip['boss']=$this->getBoss($red['level']);
		$this->assign('red',$red);
		$this->assign('vip',$vip);
		$this->display();
	}

	/******
	 *作者：郑伊凡
	 *时间：2016-1-20
	 *版本：母版本
	 *功能：当开启身份认证时，检测身份是否填写完毕
	 ******/
	public function checkidentify(){
		if(IS_AJAX){
			if($_SESSION['WAP']['shopset']['ischeckid']){
				// 如果开启身份验证
				$vip=M("Vip")->where(array("id"=>$_SESSION['WAP']['vipid']))->find();
				if($vip['isidentify']){
					$info['status']=1;
				}else{
					$info['status']=0;
					$info['msg']="您还未完善个人信息，请先填写信息！";
				}
				$this->ajaxReturn($info);
			}
		}
	}

	public function cards() {
		$m=M('Redpaper');
		$mr=M('Redpaper_order');
		$vip=$mr->where('vipid='.$_SESSION['WAP']['vipid'])->find();
		$cache=$m->find();
		$imgurl=$this->getPic($cache['pic']);
		$cache['imgurl']=$imgurl['imgurl'];
		// dump(self::$WAP['shopset']['ischeckid']);die;
		$this->assign('isidentify',$_SESSION['WAP']['vip']['isidentify']);
		$this->assign('ischeckid',self::$WAP['shopset']['ischeckid']);
		$this->assign('vip',$vip);
		$this->assign('cache',$cache);
		$this->display();
	}

	public function buy(){
		// 作者：郑伊凡 2016-1-20 母版本 功能：防止用户入口进入
		if($_SESSION['WAP']['shopset']['ischeckid']){
			// 如果开启身份验证
			$vip=M("Vip")->where(array("id"=>$_SESSION['WAP']['vipid']))->find();
			if(!$vip['isidentify']){
				$this->error('您的身份尚未完善，请先填写信息！',U("wap/vip/info"),3);
			}
		}
		// 作者：郑伊凡 2016-1-20 母版本 功能：防止用户入口进入
		$level=I('level');
		$agree=I('agree');
		$redpaper=M('Redpaper')->find();
		$arr=array(1=>$redpaper['price1'],2=>$redpaper['price2'],3=>$redpaper['price3']);
		// 判断是否有同意协议
		if($agree!=1){
			$this->diemsg(0,"您未同意本协议，交易终止！");
		}
		if($level>3 || $level<1){
			$this->diemsg(0,"错误交易，交易终止！");
		}
		$info=$this->checklevel($level);
		if(!$info['status']){
			$this->diemsg(0,"请购买等级更高的红包！");
		}
		// 判断之前的红包是否发送
		$cache=M('Redpaper_order')->where(array('vipid'=>$_SESSION['WAP']['vipid']))->find();
		if($cache){
			if(!$cache['status'])
				$this->diemsg(0,"请先发送您之前购买的红包！");
		}
		// 创建订单
		$morder=M('Shop_order');
		$data['sid']=$_SESSION['WAP']['vip']['sid'];
		$data['totalprice']=$data['payprice']=$arr[$level];
		$data['vipid']=$_SESSION['WAP']['vipid'];
		$data['vipopenid']=$_SESSION['WAP']['vip']['openid'];
		$data['totalnum']=1;
		$data['paytype']='wxpay';
		$data['yf']=0;
		$data['ispay']=0;
		$data['status']=1;
		$data['tqtype']='hongbao';
		$data['ctime']=time();
		$re=$morder->add($data);
		if($re!==false){
			$oid=date('YmdHis').'-'.$re;
			$old=$morder->where('id='.$re)->setField('oid',$oid);
			if($old!==false){
				//后端日志
				$mlog=M('Shop_order_syslog');
				$dlog['oid']=$re;
				$dlog['msg']='订单创建成功';
				$dlog['type']=1;
				$dlog['ctime']=time();
				$rlog=$mlog->add($dlog);
			}else{
				$old=$morder->delete($re);
				$this->error('订单生成失败！请重新尝试！');
			}
		}else{
			$this->error('订单生成失败！请重新尝试！');
		}
		$this->redirect(U('/Home/Redpaperpay/pay',array('oid'=>$oid,'mode'=>$mode)));
		// 测试用方法
		// $this->testpay($oid);
		// $this->redirect(U('wap/redpaper/index'));
	}

	// 删除购买失败的订单
	public function cancelOrder(){
		$map['vipid']=$_SESSION["WAP"]['vipid'];
		$map['status']=1;
		$map['tqtype']='hongbao';
		M('Shop_order')->where($map)->setField('status',0);
		$this->redirect(U('wap/redpaper/cards'));
	}

	// 判断之前是否购买过
	public function checklevel($level){
		$m=M('Redpaper_order');
		$cache=$m->where('vipid='.$_SESSION['WAP']['vipid'])->find();
		if($cache){
			if($level<=$cache['level']){
				$info['status']=0;
				$info['msg']="请购买等级更高的红包！";
			}else{
				$info['status']=1;
			}
		}else{
			$info['status']=1;
		}
		return $info;
	}

	// 测试支付，模拟微信支付
	private function testpay($oid){
		$m = M('Shop_order');
		$order = $m -> where(array('oid'=>$oid)) -> find();
		if ($order) {
			if($order['status'] == 1 && $order['tqtype']=='hongbao'){
				//修改状态
				$order['ispay'] = 1;
				$order['status'] = 5;
				$order['paytime'] = time();
				//$order['aliaccount'] = $buyer_email;
				$re=$m -> save($order);
				if(FALSE !== $re){
					//记录日志
					$mlog=M('Shop_order_log');
					$mslog=M('Shop_order_syslog');
					$dlog['oid']=$order['id'];
					$dlog['msg']='微信付款成功';
					$dlog['ctime']=time();
					$mlog->add($dlog);
					$dlog['type']=2;
					$dlog['paytype']=$cache['paytype'];
					$mslog->add($dlog);
				}
				$mred=M("Redpaper");
				$red_order=M("Redpaper_order");
				$redset=$mred->find();
				$re=$red_order->where('vipid='.$order['vipid'])->find();
				$arr=array($redset['price1']=>1,$redset['price2']=>2,$redset['price3']=>3);
				$data['vipid']=$order['vipid'];
				$data['nickname']=M('Vip')->where('id='.$order['vipid'])->getField('nickname');
				$data['openid']=$order['vipopenid'];
				$data['level']=$arr[$order['payprice']];
				$data['pay']=$order['payprice'];
				$data['status']=0;
				$data['ctime']=time();
				if($re){
					// 之前购买过
					$ree=$red_order->where('id='.$re['id'])->save($data);
				}else{
					$ree=$red_order->add($data);
				}
				if($ree===false){
					// 记录日志
					$str="用户:".$data['nickname'].",ID:".$data['vipid'].",openid:".$data['openid']."详情：购买失败。";
					file_put_contents(self::$_logs.'Joel_redpaperpayerr.txt','业务出错:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单号:'.$order['oid'].PHP_EOL.'交易结果:业务出错'.PHP_EOL.PHP_EOL,FILE_APPEND);
					die;
				}
			}
		}
	}

	// 计算下级人数
	private function countNum($num){
		$vipid=$_SESSION['WAP']['vipid'];
		$m=M('Vip');
		$arr=array('num1'=>0,'num2'=>0,'num3'=>0);
		$str="select id,pid,path from `Joel_vip` where `path` like '%-$vipid-%' or `path` like '%-$vipid'";
		$p=$m->query($str);
		// 计算下级
		foreach($p as $k=>$v){
			if($v['pid']==$vipid){
				$arr['num1']++;
				unset($p[$k]);
				foreach($p as $kk=>$vv){
					if($vv['pid']==$v['id']){
						$arr['num2']++;
						unset($p[$kk]);
						foreach($p as $kkk=>$vvv){
							if($vvv['pid']==$vv['id']){
								$arr['num3']++;
								unset($p[$kkk]);
							}
						}
					}
				}
			}
		}
		// 优化前步骤
		// $people1=$m->where('pid='.$vipid)->field('id,path')->select();
		// $arr['num1']=count($people1);
		// if($num>=2){
		// 	foreach($people1 as $v){
		// 		$people2=$m->where('pid='.$v['id'])->field('id,path')->select();
		// 		$arr['num2']+=count($people2);
		// 		if($num>=3){
		// 			foreach($people2 as $vv){
		// 				$people3=$m->where('pid='.$vv['id'])->field('id,path')->select();
		// 				$arr['num3']+=count($people3);
		// 			}
		// 		}
		// 	}
		// }
		return $arr;
	}

	// 得到上级信息
	private function getBoss($level){
		$pid=$_SESSION["WAP"]['vip']['pid'];//259;
		$m=M('Vip');
		$boss=array();
		if($level>=1){
			$boss[1]=$m->where('id='.$pid)->field('id,nickname,pid,headimgurl,openid')->find();
			if(!$boss[1]){return false;}
		}
		if($level>=2){
			$boss[2]=$m->where('id='.$boss[1]['pid'])->field('id,nickname,pid,headimgurl,openid')->find();
			if(!$boss[2]){
				unset($boss[2]);
				return $boss;
			}
		}
		if($level>=3){
			$boss[3]=$m->where('id='.$boss[2]['pid'])->field('id,nickname,pid,headimgurl,openid')->find();
			if(!$boss[3]){
				unset($boss[3]);
				return $boss;
			}
		}
		return $boss;
	}

	// 一键发送
	public function sentAll(){
		if(IS_AJAX){
			$redset=M('Redpaper')->find();
			$mo=M('Redpaper_order');
			$red=$mo->where('vipid='.$_SESSION['WAP']['vipid'])->find();
			$boss=$this->getBoss($red['level']);
			$m=M('Redpaper_log');
			$idarr=array();
			foreach($boss as $k=>$v){
				// 如果有上级
				if($v){
					// 判断上级权限
					if(!$this->checkBossAccess($k,$v['id'],$mo)){
						unset($boss[$k]);
					}else{
						$data['fromVip']=$_SESSION['WAP']['vipid'];
						$data['fromNickname']=$_SESSION['WAP']['vip']['nickname'];
						$data['fromOpenid']=$_SESSION['WAP']['vip']['openid'];
						$data['toVip']=$v['id'];
						$data['toNickname']=$v['nickname'];
						$data['toOpenid']=$v['openid'];
						$data['money']=floor(($red['pay']*(1-$redset['manager']/100))/$red['level']);
						$data['level']=$k;
						$data['ctime']=time();
						$re=$m->add($data);
						$ree=M('Redpaper_order')->where('vipid='.$v['id'])->setInc('getMoney',$data['money']);
						if($re !== false && $ree !== false){
							$idarr[]=$re;
						}else{
							foreach($idarr as $vv){
								$m->where('id='.$vv)->delete();
							}
							$info['status']=0;
							$info['msg']="红包发送失败，请重试。";
							$this->ajaxReturn($info);
						}
					}
				}
			}
			if((!$boss || !count($boss)) && $red['level']){
				$red['status']=3;
			}else{
				$red['status']=1;
			}
			$mo->save($red);
			$info['status']=1;
			$info['msg']="红包发送成功，红包将在一个工作日内送出。";
			$this->ajaxReturn($info);
		}
	}

	/* * * * *
	 * 判断上级是否有获得红包的权限
	 */
	private function checkBossAccess($level,$vipid,$m){
		$map['vipid']=$vipid;
		$cache=$m->where($map)->field('level,status')->find();
		if($cache['level']>=$level && $cache['status']==3){
			return true;
		}else{
			return false;
		}
	}

    /* * * * *
	 * 统计获得红包金额
	 */
    private function countGetRadpaper(){
    	$vipid=$_SESSION['WAP']['vipid'];
    	$cache=M('Redpaper_order')->where("vipid=$vipid")->find();
    	$arr=array();
    	if($cache){
    		$map=array(
    					'toVip'=>$vipid,
    					'status'=>array('neq',0)
    					);
    		$arr['total']=0;
    		for($i=1;$i<=$cache['level'];$i++){
    			$map['level']=$i;
    			$arr[$i]=M('Redpaper_log')->where($map)->Sum('money');
    			$arr['total']+=$arr[$i];
    		}
    	}
    	return $arr;
    }
}