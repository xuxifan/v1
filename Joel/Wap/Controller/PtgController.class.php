<?php
// 本类由系统自动生成，仅供测试用途
namespace Wap\Controller;
use Wap\Controller\BasehdController;
class PtgController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();	
		// 作者：郑伊凡 2016-1-25 母版本 功能：拼团购入口限制
		$isptg=M('Shop_set')->getField('isptg');
		if(!$isptg){
			$this->diemsg(0, "本网站未开启拼团购功能哦~");
		}
		
		//追入分享特效
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
		
		// 作者：郑伊凡 2016-1-25 母版本 功能：拼团购入口限制
	}
	public function index(){
		//追入商城设置
		$shopset=M('Shop_set')->find();
		$this->assign('shopset',$shopset);
		//追入分享特效
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
		// $sid=I('sid');
		$orderid=I('ptgid');
		$isptg=I('isptg');
		$this->assign('isptg',$isptg);

		$m=M('Shop_order');
		$vipid=$_SESSION['WAP']['vipid'];
		$vip=M("Vip")->where("id=".$vipid)->find();
		if(!$vip){
			session(null);
			$this->diemsg(0, "未能获取正确信息，请重新尝试！");
		}
		$this->assign('vip',$vip);
		// $map['sid']=$sid;
		// $map['id']=$orderid;
		// $cache=$m->where($map)->find();
		// echo $m->getLastSql();
		// if(!$cache){
		// 	$this->diemsg(0,'此订单不存在!');
		// }
		// $cache['items']=unserialize($cache['items']);
		// $this->assign('cache',$cache);

		
		//取出拼团日志，判断是否可进行拼团
		$ptgid=I('ptgid');
		$das=M('ptg_log')->where(array('id'=>$ptgid))->find();
		// $map['sid']=$sid?$sid:0;
		$map['oid']=$das['oid'];
		$cache=$m->where($map)->find();
		if(!$cache){
			$this->diemsg(0,'此订单不存在!');
		}
		
		$cache['items']=unserialize($cache['items']);
		$this->assign('cache',$cache);//商品信息
		$this->assign('ptgid',$ptgid);
		$ptgty='ptgad';
		$this->assign('ptgty',$ptgty);
		//读取商品表
		$goods=M("Shop_goods");
		$goodinfo = $goods->where(array('id'=>$das['goodsid']))->find();
		$groupprice = $goodinfo['groupprice'];
		$goodid = $goodinfo['id'];
		$this->assign('goodinfo',$goodinfo);
		$this->assign('groupprice',$groupprice);
		$this->assign('goodid',$goodid);
		
		//取出团购日志
		$grouplog=M('ptg_order')->where(array('ptgid'=>$ptgid))->order('ctime desc')->select();
		$totalgroup=count($grouplog);
		$this->assign('grouplog',$grouplog);
		$this->assign('totalgroup',$totalgroup);
		
		//是否是主人模式
		$isself=$cache['vipid']==$vipid?1:0;
		$this->assign('isself',$isself);
		
		//是否已团购
		if(!$isself){
			
			$isgroup=M('shop_order')->where(array('vipid'=>$vipid,'ptgid'=>$das['id']))->find();
			$isgroup=$isgroup?2:0;
			$this->assign('isgroup',$isgroup);
			$zr=M('Vip')->where('id='.$cache['vipid'])->find();
			$this->assign('zr',$zr);
			
		}else{
			$isgroup='2';
			$this->assign('isgroup',$isgroup);
			//取出主人
			$zr=M('Vip')->where('id='.$cache['vipid'])->find();
			$this->assign('zr',$zr);
		}
		//取出拼团购宣言
		if($shopset['ptgmsg']){
			$ptgmsg=array_filter(explode('##', $shopset['ptgmsg']));
			$this->assign('firstmsg',$ptgmsg[0]);
			$this->assign('ptgmsg',$ptgmsg);
//			print_r($ptgmsg);
		}else{
			$this->diemsg(0, '系统未设置拼团购宣言！');
		}
		
		$this->display();
	}		
	
	
	public function setMsg(){
		if(IS_POST){
			$data=I('post.');
			if(!$data['id']){
				$info['status']=0;
				$info['msg']='未获取订单号，请重新尝试！';
				$this->ajaxReturn($info);
			}
			if(!$data['msg']){
				$info['status']=0;
				$info['msg']='未获取砍价宣言，请重新尝试！';
				$this->ajaxReturn($info);
			}
			$re=M('Shop_order')->where('id='.$data['id'])->setField('ptgmsg',$data['msg']);
			if($re!==FALSE){
					$info['status']=1;
					$info['msg']='设置拼团购宣言成功！快去分享给朋友砍价吧！';
			}else{
					$info['status']=0;
					$info['msg']='设置拼团购宣言失败！请重新尝试！';
			}
			
			$this->ajaxReturn($info);
		}else{
			$this->diemsg(0, '非法访问！');
		}
	}
	
	
	private function randomFloat($min = 0, $max = 1) {  
    	return $min + mt_rand() / mt_getrandmax() * ($max - $min);  
	}  

	public function orderPtg(){
		$ptgid=$_POST['ptgid'];
		$morder=M('Shop_order');
		if($ptgid){
			// 为拼团购的参与者
			$ar['id']=$ptgid;
			$ptg=M('Ptg_log')->where($ar)->find();
			// 检查拼团购状态，是否符合拼团购条件
			if($ptg['status']!=1){
				// 拼团失败则调到拼团购页面
				$this->error("拼团购失败！此团购已结束！",U('Wap/Ptg/index',array("ptgid"=>$ptgid)));die;
			}
			// 检查拼团购人数是否已满
			if($ptg['num']>=$ptg['groupmax']){
				// 拼团失败则调到拼团购页面
				$this->error("拼团购失败！此团购人数已满！",U('Wap/Ptg/index',array("ptgid"=>$ptgid)));die;
			}
		}
		// 生成订单
		$data=I('post.');
		$data['items']=stripslashes(htmlspecialchars_decode($data['items']));
		$data['ispay']=0;
		$data['status']=1;//订单成功，未付款
		$data['ctime']=time();
		$data['payprice']=$data['totalprice'];

		//邮费逻辑
		// 如果用户选择自提
		if($data['tqtype']=="ziti"){
			$data['yf'] = 0;
		}
		$goods=unserialize($data['items']);
		$data['payprice']=$data['payprice']+$data['yf'];
		$re=$morder->add($data);
		if($re){
			$old=$morder->where('id='.$re)->setField('oid',date('YmdHis').'-'.$re);
			if(FALSE !== $old){
				//后端日志
				$mlog=M('Shop_order_syslog');
				$dlog['oid']=$re;
				$dlog['msg']='订单创建成功';
				$dlog['type']=1;
				$dlog['ctime']=time();
//				$dlog['uid']=$uids;
				$rlog=$mlog->add($dlog);
				//清空购物车
				$rbask=M('Shop_basket')->where(array('sid'=>$data['sid'],'vipid'=>$data['vipid']))->delete();
				$this->success('订单创建成功，转向支付界面!',U('Wap/Ptg/pay/',array('sid'=>$data['sid'],'orderid'=>$re)));
			}else{
				$old=$morder->delete($re);
				$this->error('订单生成失败！请重新尝试！');
			}
		}else{
			//可能存在代金券问题
			$this->error('订单生成失败！请重新尝试！');
		}
	}

	//订单支付
	public function pay(){
//		$uids=I('uids')<>''?I('uids'):$this->diemsg(0, '缺少UIDS参数');
		$sid=$_SESSION['WAP']['vip']['sid']<>''?$_SESSION['WAP']['vip']['sid']:0;//$this->diemsg(0, '缺少SID参数');
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		$type=I('type');
		$bkurl=U('Wap/Shop/pay',array('sid'=>$sid,'orderid'=>$orderid,'type'=>$type));		
		$backurl=base64_encode($orderdetail);
		$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$arr['id']=$orderid;
		$order=$m->where($arr)->find();
		if(!$order){
			$this->error('此订单不存在！');
		}
		if($order['status']<>1){
			$this->error('此订单不可以支付！');
		}
		$paytype=I('type')?I('type'):$order['paytype'];
		switch($paytype){
			case 'money':
				$mvip=M('Vip');
				// ptg 开启事物
				$mvip->startTrans();
				// ptg
				$p['id']=$_SESSION['WAP']['vipid'];
				$vip=$mvip->where($p)->find();
				$pp=$vip['money']-$order['payprice'];
				if($pp>=0){
					//扣钱成功
					//此处应该开启事物
					$re=$mvip->where($p)->setField('money',$pp);
					if($re){

						$order['paytype']='money';
						$order['ispay']=1;
						$order['paytime']=time();
						$order['status']=2;

						//如果是拼团购参与者，则写入ptg_log
						// ptg 如果是通过拼团购购买的商品
						if($order['isgroup']==2){
							// 此人为参加者 写入ptg_log 表
							if($order['ptgid']){
								// 检查数据是否符合拼团条件
								$mptg=M('Ptg_log');
								$tg['id']=$order['ptgid'];
								$ptg_log_data=$mptg->where('id='.$order['ptgid'])->find();
								// 检查拼团状态
								if($ptg_log_data['status']!=1){
									$mvip->rollback();
									$this->error('拼团失败！此拼团已结束！');die;
								}
								// 检查认识是否已满
								if($ptg_log_data['num']>=$ptg_log_data['groupmax']){
									//人数已满
									$mvip->rollback();
									$this->error('拼团失败！拼团人数已满！');die;
								}
								$ptgorder['ptgid']=$order['ptgid'];
								$ptgorder['oid']=$order['oid'];
								$ptgorder['vipid']=$_SESSION['WAP']['vipid'];
								$ptgorder['nickname']=$_SESSION['WAP']['vip']['nickname'];
								$ptgorder['openid']=$_SESSION['WAP']['vip']['openid'];
								$ptgorder['headimgurl']=$_SESSION['WAP']['vip']['headimgurl'];
								$ptgorder['payprice']=$order['payprice'];
								$ptgorder['ctime']=time();
								$po=M('Ptg_order');
								if(!$po->add($ptgorder)){
									$mvip->rollback();
									$this->error('拼团失败！请联系客服！');
								}else{
									$ptg_log_data['num']++;
									// 人数已满
									if($ptg_log_data['num']>=$ptg_log_data['groupmax']){
										$ptg_log_data['status']=2;
									}
									if($mptg->save($ptg_log_data)===false){
										$mvip->rollback();
										$this->error('拼团失败！请联系客服！');die;
									}else{
										$rep=$order['ptgid'];
									}
								}
							}else{
								// 此人为发起者 写入ptg_log 表
								$ptgdata['vipid']=$_SESSION['WAP']['vipid'];
								$ptgdata['sid']=$_SESSION['WAP']['vip']['sid'];
								$ptgdata['oid']=$order['oid'];
								$items=unserialize($order['items']);
								$ptgdata['goodsid']=$items[0]['goodsid'];
								$ptgdata['isgroup']=$order['isgroup'];
								$ptgdata['groupmax']=$items[0]['groupmax'];
								$ptgdata['groupprice']=$items[0]['groupprice'];
								$ptgdata['num']=1;
								$ptgdata['status']=1;//拼团购正在进行
								$ptgdata['ctime']=time();
								$mptg=M('Ptg_log');
								$rep=$mptg->add($ptgdata);
								if($rep){
									//如果失败怎么怎么的，先不做
									$order['ptgid']=$rep;
									$ptgorder['ptgid']=$rep;
									$ptgorder['oid']=$order['oid'];
									$ptgorder['vipid']=$_SESSION['WAP']['vipid'];
									$ptgorder['nickname']=$_SESSION['WAP']['vip']['nickname'];
									$ptgorder['openid']=$_SESSION['WAP']['vip']['openid'];
									$ptgorder['headimgurl']=$_SESSION['WAP']['vip']['headimgurl'];
									$ptgorder['payprice']=$order['payprice'];
//									$ptgorder['uid']=$uids;
									$ptgorder['ctime']=time();
									$po=M('Ptg_order');
									if(!$po->add($ptgorder)){
										$mvip->rollback();
										$this->error('拼团失败！请联系客服！');
									}
								}else{
									$mvip->rollback();
									$this->error('拼团失败！请联系客服！');
								}
							}
							
						}
						// ptg
						$rod=$m->save($order);
						if(FALSE !== $rod){
							//==付款成功-发送模板消息通知该会员==============================
							$mvip->commit();
							$SET=M('Set')->find();
							$items=unserialize($order['items']);
							$itemsname='';
							foreach($items as $k=>$v){
								$itemsname.=$v['name'].',';
							}
							$tp=new \bb\template();
							$array=array(
								'url'=>$SET['wxurl'].U('wap/shop/orderDetail',array('orderid'=>$order['id'])),
								'name'=>$order['vipname'],
								'ordername'=>rtrim($itemsname,','),
								'orderid'=>$order['oid'],
								'money'=>$order['payprice'],
								'date'=>date("Y-m-d H:i:s",time())
							);
							$templatedata=$tp->enddata('orderok',$order['vipopenid'],$array);	//组合模板数据
							$options['appid']= self::$_wxappid;
							$options['appsecret']= self::$_wxappsecret;
							$wx = new \Joel\wx\Wechat($options);
							$wx->sendTemplateMessage($templatedata);	//发送模板
							//============================================================
							//销量计算-只减不增
							$rsell=$this->doSells($order);
							//前端日志
							$mlog=M('Shop_order_log');
							$dlog['oid']=$order['id'];
//							$dlog['uid']=$uids;
							$dlog['msg']='余额-付款成功';
							$dlog['ctime']=time();
							$rlog=$mlog->add($dlog);
							//后端日志
							$mlog=M('Shop_order_syslog');
							$dlog['type']=2;
							$rlog=$mlog->add($dlog);
							// ptg
							if($order['isgroup']==2){
								$this->success('余额付款成功！',U('Wap/Ptg/index',array('sid'=>$sid,'ptgid'=>$rep)));
							}else{
								$this->success('余额付款成功！',U('Wap/Shop/ptgorderList',array('sid'=>$sid,'type'=>'2')));
							}
							// ptg
							
							//代收花生米计算-只减不增
							$rds=$this->doDs($order);
							
							//==通知该会员的上级==即将得到的佣金=========================================================
							$ds=M('fx_dslog')->where(array('oid'=>$order['id']))->find();		//查找将获得的佣金
							$vipuser=M('vip')->where(array('id'=>$ds['to']))->find(); //查找他的上级获取上级的openid
							$SET=M('Set')->find();
							$tp=new \bb\template();
							$array=array(
								'url'=>$SET['wxurl'].U('wap/fx/dslog'),
								'name'=>$ds['toname'],		//上级名字
								'fromname'=>$ds['fromname'],	//下级级名字
								'ordername'=>rtrim($itemsname,','),
								'money'=>$ds['fxprice'],		//商品价格
								'yj'=>$ds['fxyj']			//分销的佣金
							);
							$openid=$vipuser['openid'];		//发给的人
							$templatedata=$tp->enddata('collection',$openid,$array);	//组合模板数据
							$wx->sendTemplateMessage($templatedata);	//发送模板
							//=============================================================
							
						}else{
							//后端日志
							$mlog=M('Shop_order_syslog');
							$dlog['oid']=$order['id'];
							$dlog['msg']='余额付款失败';
							$dlog['type']=-1;
							$dlog['ctime']=time();
//							$dlog['uid']=$uids;
							$rlog=$mlog->add($dlog);
							$this->error('余额付款失败！请联系客服！');
						}
						
					}else{
						//后端日志
						$mlog=M('Shop_order_syslog');
						$dlog['oid']=$order['id'];
						$dlog['msg']='余额付款失败';
						$dlog['type']=-1;
						$dlog['ctime']=time();
						$this->error('余额支付失败，请重新尝试！');
					}
				}else{
					$this->error('余额不足，请使用其它方式付款！');
				}
				break;
			case 'alipaywap':
				$this->redirect(U('/Home/Alipaywap/pay',array('sid'=>$sid,'price'=>$order['payprice'],'oid'=>$order['oid'])));
				break;
			case 'wxpay':
				$_SESSION['wxpaysid']=$_SESSION['WAP']['vip']['sid'];
				$_SESSION['wxpayopenid']=$_SESSION['WAP']['vip']['openid'];//追入会员openid
				if($_GET['ptgid']){
					$mptg=M('Ptg_log');
					$ptg_log_data=$mptg->where('id='.$_GET['ptgid'])->find();
					// 检查拼团状态
					if($ptg_log_data['status']!=1){
						$this->error('拼团失败！此拼团已结束！');die;
					}
					// 检查认识是否已满
					if($ptg_log_data['num']>=$ptg_log_data['groupmax']){
						//人数已满
						$this->error('拼团失败！拼团人数已满！');die;
					}
				}
				$this->redirect(U('/Home/PtgWxpay/pay',array('sid'=>$sid,'oid'=>$order['oid'])));
			break;
			default:
				$this->error('支付方式未知！',U('wap/shop/ordermake'),3);
				break;
		}
		
	}

	//销量计算
	private function doSells($order){
		$mgoods=M('Shop_goods');
		$msku=M('Shop_goods_sku');
		$mlogsell=M('Shop_syslog_sells');
		//封装dlog
		$dlog['oid']=$order['id'];
		$dlog['vipid']=$order['vipid'];
		$dlog['vipopenid']=$order['vipopenid'];
		$dlog['vipname']=$order['vipname'];
		$dlog['ctime']=time();
		$items=unserialize($order['items']);
		$tmplog=array();
		foreach($items as $k=>$v){
			//销售总量
			$dnum=$dlog['num']=$v['num'];
			if($v['skuid']){
				$rg=$mgoods->where('id='.$v['goodsid'])->setDec('num',$dnum);
				$rg=$mgoods->where('id='.$v['goodsid'])->setInc('sells',$dnum);
				$rg=$mgoods->where('id='.$v['goodsid'])->setInc('dissells',$dnum);
				$rs=$msku->where('id='.$v['skuid'])->setDec('num',$dnum);
				$rs=$msku->where('id='.$v['skuid'])->setInc('sells',$dnum);
				//sku模式
				$dlog['goodsid']=$v['goodsid'];
				$dlog['goodsname']=$v['name'];
				$dlog['skuid']=$v['skuid'];
				$dlog['skuattr']=$v['skuattr'];
				$dlog['price']=$v['price'];
				$dlog['num']=$v['num'];
				$dlog['total']=$v['total'];
			}else{
				$rg=$mgoods->where('id='.$v['goodsid'])->setDec('num',$dnum);
				$rg=$mgoods->where('id='.$v['goodsid'])->setInc('sells',$dnum);
				$rg=$mgoods->where('id='.$v['goodsid'])->setInc('dissells',$dnum);
				//纯goods模式
				$dlog['goodsid']=$v['goodsid'];
				$dlog['goodsname']=$v['name'];
				$dlog['skuid']=0;
				$dlog['skuattr']=0;
				$dlog['price']=$v['price'];
				$dlog['num']=$v['num'];
				$dlog['total']=$v['total'];
			}
			array_push($tmplog,$dlog);
		}
		if(count($tmplog)){
			$rlog=$mlogsell->addAll($tmplog);
		}
		return true;
	}

	//代收花生米计算
	public function doDs($order){
			//分销佣金计算
			$vipid=$order['vipid'];
			$mvip=M('vip');
			$vip=$mvip->where('id='.$vipid)->find();
			if(!$vip && !$vip['pid']){
				return FALSE;
			}
			//初始化 
			$pid=$vip['pid'];
			$mfxlog=M('fx_dslog');
//			$shopset=M('Shop_set')->where('uid='.self::$UID)->find();//追入商城设置
			$fxlog['oid']=$order['id'];
			$fxlog['fxprice']=$fxprice=$order['payprice']-$order['yf'];
			$fxlog['ctime']=time();
			$fx1rate=$shopset['vipfx1rate']/100;
			$fxtmp=array();//缓存3级数组
			if($pid){
				//第一层分销
				$fx1=$mvip->where('id='.$pid)->find();
				if($fx1['isfx'] && $fx1rate){
					$fxlog['fxyj']=$fxprice*$fx1rate;				
					$fxlog['from']=$vip['id'];
					$fxlog['fromname']=$vip['nickname'];
					$fxlog['to']=$fx1['id'];
					$fxlog['toname']=$fx1['nickname'];
					$fxlog['status']=1;
					//单层逻辑					
					//$rfxlog=$mfxlog->add($fxlog);
					//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
					array_push($fxtmp,$fxlog);
				}
				
				//多层分销
				if(count($fxtmp)>=1){
					$refxlog=$mfxlog->addAll($fxtmp);
					if(!$refxlog){
						file_put_contents('Joel_fx_error.txt','错误日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'错误纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
					}
				}
												
			}
		return true;
		//逻辑完成
	}

	public function ptglist(){
		header("content-type:text/html;charset=utf-8");
		//轮播
		$indexalbum=M('Shop_ads')->where('ispc=0')->select();
		foreach($indexalbum as $k=>$v){
			$listpic=$this->getPic($v['pic']);
			$indexalbum[$k]['imgurl']=$listpic['imgurl'];
		}
		$this->assign('indexalbum',$indexalbum);

		$pageSize=self::$WAP['shopset']['pagesize'];
		$pageCount=1;
		$mlabel=M("shop_label");
		$label=$mlabel->select();
		foreach($label as $k=>$v){
			$lpic=$this->getPic($v['lpic']);
			$label[$k]['limgurl']=$lpic['imgurl'];			
			$listpic=$this->getPic($v['pic']);
			$label[$k]['imgurl']=$listpic['imgurl'];
		}
		$this->assign('indexcache',$label);

		$m=M("Shop_goods");
		$map['isgroup']=1;
		$map['iscut']=0;
		$map['status']=1;
		$cache=$m->where($map)->field('id,oprice,name,listpic,summary,groupprice,groupmax,unit')->select();
		foreach ($cache as $k=>$v){
			$imgurl=$this->getPic($v['listpic']);
			$cache[$k]['imgurl']=$imgurl['imgurl'];
		}
		$this->assign('cache',$cache);
		$this->display();
	}

	public function ptggoods(){
		$id=I('id');
		$map=array(
					'id'=>$id,
					'isgroup'=>1,
					'iscut'=>0,
					'status'=>1
					);
		$m=M("Shop_goods");
		$cache=$m->where($map)->find();
		// 判断是否需要认证身份
		$ischeckid=M('Shop_set')->getField("ischeckid");
		$this->assign("ischeckid",$ischeckid);
		//绑定图片
		if($cache['pic']){
			$joelpic=$this->getPic($cache['listpic']);
			$cache['imgurl']=$joelpic['imgurl'];
		}
		$this->assign('sid',$_SESSION["WAP"]['vip']['sid']);
		$this->assign('cache',$cache);
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
	public function ordermake(){
		/********提货方式    zxg   16.2.16******/
		$iszts=M('express_set')->find();
		$iszt=$iszts['iszt'];
		$isztcon=$iszts['content'];
		$this->assign('iszt',$iszt);
		$this->assign('isztcon',$isztcon);
		/********提货方式    zxg   16.2.16******/

		//非提交状态
		$sid=$_SESSION['WAP']['vip']['sid'];
		$this->assign('sid',$sid);
		//清空临时地址
		unset($_SESSION['WAP']['orderURL']);
		//已登陆
		$m=M('Shop_basket');
		$mgoods=M('Shop_goods');
		$cache=$m->where(array('sid'=>$sid,'vipid'=>$_SESSION['WAP']['vipid']))->select();
		//错误标记
		$errflag=0;
		//等待删除ID
		$todelids='';
		//totalprice
		$totalprice=0;
		//总重
		$heavy=0;
		//totalnum
		$totalnum=0;
	//2016-1-21 ptg
		$ids=I('ids');
		$ptgid=I('ptgid');
		$this->assign('ids',$ids);
		$this->assign('ptgid',$ptgid);
	//2016-1-21 ptg
		$allmy=1;
		foreach($cache as $k=>$v){
			$goods=$mgoods->where('id='.$v['goodsid'])->find();	
			//sku模型
		//2016-1-21 ptg
			if(count($cache)==1){
				$this->assign('isgroup',$goods['isgroup']);
			}
		//2016-1-21 ptg
			$pic=$this->getPic($goods['listpic']);
			if($goods['status']){
				if($goods['num']){
					//调整购买量
					$cache[$k]['goodsid']=$goods['id'];
					$cache[$k]['skuid']=0;
					$cache[$k]['name']=$goods['name'];
					$cache[$k]['skuattr']=$sku['skuattr'];
					$cache[$k]['num']=1;// 拼团购限购1件
					// 作者：郑伊凡 2016-1-25 母版本 拼团购模块
					$cache[$k]['price']=$goods['groupprice'];
					// $cache[$k]['total']=$v['num']*$goods['price'];
					$cache[$k]['total']=$v['num']*$cache[$k]['price'];
					// 作者：郑伊凡 2016-1-25 母版本 拼团购模块
					$cache[$k]['pic']=$pic['imgurl'];
					$totalnum=$totalnum+$cache[$k]['num'];
					$totalprice=$totalprice+$cache[$k]['price']*$cache[$k]['num'];
					if($goods['iscut']){
							$cache[$k]['iscut']=$goods['iscut'];
							$cache[$k]['cutlow']=$goods['cutlow'];
							$cache[$k]['cuttop']=$goods['cuttop'];
							$cache[$k]['cutmax']=$goods['cutmax'];
							$totaliscut=$totaliscut+$goods['iscut']*$cache[$k]['num'];
							$totalcutlow=$totalcutlow+$goods['cutlow']*$cache[$k]['num'];
							$totalcuttop=$totalcuttop+$goods['cuttop']*$cache[$k]['num'];
							$totalcutmax=$totalcutmax+$goods['cutmax']*$cache[$k]['num'];
					}
					if($goods['ismy']==0){
						$heavy=$heavy+$goods['heavy']*$v['num'];
						$allmy=0;
					}
				//ptg--是否开启拼团购
						if($goods['isgroup']){
							$cache[$k]['isgroup']=$goods['isgroup'];
							$cache[$k]['groupmax']=$goods['groupmax'];
							$cache[$k]['groupprice']=$goods['groupprice'];
						}
				//ptg	
				}else{
					//无库存删除
					$todelids=$todelids.$v['id'].',';
					unset($cache[$k]);
				}
			}else{
				//下架删除
				$todelids=$todelids.$v['id'].',';
				unset($cache[$k]);
			}
			
		}
		if($todelids){
			$rdel=$m->delete($todelids);
			if(!$rdel){
				$this->error('购物车获取失败，请重新尝试！');
			}	
		}
		//将商品列表
		sort($cache);
		$allitems=serialize($cache);
		$this->assign('allitems',$allitems);
		//VIP信息
		$vipadd=I('vipadd');
		if($vipadd){
			$vip=M('Vip_address')->where('id='.$vipadd)->find();
		}else{
			$vip=M('Vip_address')->where('vipid='.$_SESSION['WAP']['vipid'])->find();
		}
		$ptemp=M('location_province')->where('id='.$vip['province'])->find();
		$vip['provtext']=$ptemp['name'];
		$this->assign('vip',$vip);
		//根据重量计算邮费
		if($allmy==1){
			$heavy=-1;
		}
		$yf=$this->_getpostage($vip['province'],$heavy);
		$this->assign('yf',$yf);
		$this->assign('heavy',$heavy);
		//可用代金券
		$mdjq=M('Vip_card');
		$mapdjq['type']=2;
		$mapdjq['vipid']=$_SESSION['WAP']['vipid'];
		$mapdjq['status']=1;//1为可以使用
		$mapdjq['usetime']=0;
		$mapdjq['etime']=array('gt',time());
		$mapdjq['usemoney']=array('lt',$totalprice);
		$djq=$mdjq->field('id,money')->where($mapdjq)->select();
		$this->assign('djq',$djq);
		//邮费逻辑
		//是否可以用余额支付
		$useryue=$_SESSION['WAP']['vip']['money'];
		$isyue=$_SESSION['WAP']['vip']['money']-$totalprice>=0?0:1;
		$this->assign('isyue',$isyue);
		//
		$this->assign('ntime',$ntime);
		$this->assign('cache',$cache);
		$this->assign('totalprice',$totalprice);
		$this->assign('totalnum',$totalnum);
		//积分换算
		$this->assign('jfdk',self::$WAP['shopset']['jfdk']);
		$this->assign('jfdh',self::$WAP['shopset']['jfdh']);
		$dhscore=$totalprice*self::$WAP['shopset']['jfdk']*0.01*self::$WAP['shopset']['jfdh'];
		$this->assign('myscore',$_SESSION['WAP']['vip']['score']);
		$this->assign('dhscore',$dhscore);
		// 作者：郑伊凡 2016-1-25 母版本 功能：控制前台的聚友杀、拼团购
		$this->assign('isptg',self::$WAP['shopset']['isptg']);
		// 作者：郑伊凡 2016-1-25 母版本 功能：控制前台的聚友杀、拼团购
		$this->assign('firstmsg',$jysmsg[0]);
		$this->assign("lasturlencode",base64_encode(U('wap/ptg/ordermake',array('ids'=>$ids,'ptgid'=>$ptgid))));
		$this->display();
	}

	public function fastbuy(){
		if(IS_AJAX){
			$m=M('Shop_basket');
			$data=I('post.');
			if(!$data){
				$info['status']=0;
				$info['msg']='未获取数据，请重新尝试';
				$this->ajaxReturn($info);
			}
			//清除购物车
			$sid=$_SESSION['WAP']['vip']['sid'];
			//前端必须保证登陆状态
			$vipid=$_SESSION['WAP']['vipid'];
			$re=$m->where(array('sid'=>$sid,'vipid'=>$vipid))->delete();
			//区分SKU模式
			$rold=$m->add($data);
			if($rold){
					$info['status']=1;
					$info['msg']='库存检测通过！3秒后自动生成订单！';
			}else{						
					$info['status']=0;
					$info['msg']='通讯失败，请重新尝试！';
			}
			$this->ajaxReturn($info);
		}else{
			$this->diemsg(0, '禁止外部访问！');
		}
	}
	/**
	 * 计算邮费
	 * post请求
	 * prid 省份ID
	 * heavy 商品ID ,隔开
	 * return 邮费
	 */
	private function _getpostage($prid,$heavy){
		if(!$_SESSION['WAP']['shopset']['isyf']||$heavy==-1){
			return 0;
		}
		//获取所属区域的信息
		$area=M('express_area')->where("provids like '%|".$prid."|%'")->find();

		//计算邮费价格
		$heavylist=array_filter(explode(',', $area['heavylist']));
		$moneylist=array_filter(explode(',', $area['moneylist']));
		$yf=0;
		$isin=true;
		foreach ($heavylist as $k => $v) {
			if($isin){
				if($heavylist[$k-1]){
					$yf=$yf+($heavylist[$k]-$heavylist[$k-1])*$moneylist[$k];
				}else{
					$yf=$yf+$heavylist[$k]*$moneylist[$k];
				}
			}
			if($heavy<=$v){
				$isin=false;
			}
		}
		if($isin){
			$yf=$yf+round($heavy-end($heavylist)+0.5)*end($moneylist);
		}
		return $yf;
	}
}
