<?php
// 积分商城控制器
// 本类由系统自动生成，仅供测试用途
namespace Wap\Controller;
use Wap\Controller\BasehdController;
class PointsController extends BaseController {
	
	
	public function _initialize() {
		header("Content-type: text/html; charset=utf-8");
		//你可以在此覆盖父类方法	
		parent::_initialize();
		$shopset=M('Shop_set')->find();
		if(!$shopset['isteg']){
			$this->diemsg(0, "本网站未开启积分商城功能哦~");
		}
		self::$WAP['shopset']=$_SESSION['WAP']['shopset']=$shopset;
		$this->assign("ischeckid",$shopset['ischeckid']);
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
	}
	public function index(){
		// 轮播
		$indexalbum=M('Shop_ads')->where('ispc=0')->select();
		foreach($indexalbum as $k=>$v){
			$listpic=$this->getPic($v['pic']);
			$indexalbum[$k]['imgurl']=$listpic['imgurl'];
		}
		$this->assign('indexalbum',$indexalbum);
		// 轮播
		$score=$_SESSION['WAP']['vip']['score'];
		// 大转盘、砸金蛋、刮刮卡
		$mdzp=M('Dzp');
		$mzjd=M('Zjd');
		$mggk=M('Ggk');
		$a_map=array(
						"status"=>1,
						);
		$dzp=$mdzp->where($a_map)->select();
		$zjd=$mzjd->where($a_map)->select();
		$ggk=$mggk->where($a_map)->select();
		$m=M("Shop_goods");
		$map=array(
					'isgroup'=>0,
					'iscut'=>0,
					'isteg'=>1,
					'status'=>1,
					);
		$cache=$m->where($map)->select();
		foreach($cache as $k=>$v){
			$img=$this->getPic($v['listpic']);
			$cache[$k]['imgurl']=$img['imgurl'];
		}
		$active=M("Shop_set")->field('dzpid,zjdid,ggkid')->find();
		$this->assign("active",$active);
		$this->assign('score',$score);
		$this->assign('sid',$_SESSION["WAP"]['vip']['sid']);
		$this->assign('cache',$cache);
		$this->display();
	}

	public function goods(){
		$id=I('id');
		$map=array(
					'id'=>$id,
					'isgroup'=>0,
					'iscut'=>0,
					'isteg'=>1,
					'status'=>1,
					);
		$m=M("Shop_goods");
		$cache=$m->where($map)->find();
		if(!$cache){
			$this->diemsg(0, "此商品不为积分兑换商品哦~");
		}
		// 判断是否需要认证身份
		$ischeckid=M('Shop_set')->getField("ischeckid");
		$this->assign("ischeckid",$ischeckid);
		//绑定图片
		if($cache['pic']){
			$joelpic=$this->getPic($cache['listpic']);
			$cache['imgurl']=$joelpic['imgurl'];
		}
		
		$this->assign('sid',$_SESSION["WAP"]['vip']['sid']);
		$this->assign('vscore',$_SESSION["WAP"]['vip']['score']);//会员拥有积分
		$this->assign('vipid',$_SESSION["WAP"]['vipid']);//VIPID
		$this->assign('payscore',$cache['integpay']);//积分支付价格
		$this->assign('gnum',$cache['num']);
		$this->assign('cache',$cache);
		
		$loginback=U('Wap/Vip/login',array('backurl'=>$backurl));
		$this->assign('loginback',$loginback);
		
		$this->display();
	}
	
	//zxg  2.25  个人身份信息验证
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
	
	//订单列表
	public function orderList(){
		
		$sid=$_SESSION['WAP']['vip']['sid']<>''?$_SESSION['WAP']['vip']['sid']:$this->diemsg(0, '缺少SID参数');
		$type=I('type')?I('type'):4;
		$this->assign('type',$type);
		$bkurl=U('Wap/Shop/orderList',array('sid'=>$sid,'type'=>$type));		
		$backurl=base64_encode($bkurl);
		$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		
//		$vipid=$_SESSION['WAP']['vipid']='11910';
//		$sid='1';
		$vipid=$_SESSION['WAP']['vipid'];
		
		switch($type){
			case '1':
				$map['status']=1;
				break;
			case '2':
				$map['status']=array('in','2,3');
				break;
			case '3':
				$map['status']=array('in','5,6');
				break;
			case '4':
				//全部
				$map['status']=array('neq','0');
				break;
			default:
				$map['status']=1;
				break;
		}
		
		//已登陆
		$m=M('shop_order');
		$p['vipid']=$vipid;
		$p['sid']=$sid;
		//$p['oid']=array('neq','0');
		//$p['ispay']=array('eq','1');
		$p['integpay']=array('gt','0');
		$cache=$m->where($p)->order('ctime desc')->select();
		if($cache){
			foreach($cache as $k=>$v){
				if($v['items']){
					$cache[$k]['items']=unserialize($v['items']);
				}
			}	
		}
		$this->assign('cache',$cache);
		$this->assign('sid',$sid);
		
		//高亮底导航
		$this->assign('actname','ftorder');
		$this->display();
	}
	
	
	//订单详情
	//订单列表
	public function orderDetail(){
		
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$sid=$_SESSION['WAP']['vip']['sid']<>''?$_SESSION['WAP']['vip']['sid']:$this->diemsg(0, '缺少SID参数');
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		$bkurl=U('Wap/Shop/orderDetail',array('sid'=>$sid,'orderid'=>$orderid));		
		$backurl=base64_encode($bkurl);
		$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$vipid=$_SESSION['WAP']['vipid'];
		$map['sid']=$sid;
		$isptg=I('isptg');
		$this->assign('isptg',$isptg);
		if($isptg){
			$map['oid']=$orderid;
			
		}else{
			$map['id']=$orderid;
		}
		$cache=$m->where($map)->find();

		if(!$cache){
			$this->diemsg('此订单不存在!');
		}
		$cache['items']=unserialize($cache['items']);
		//order日志
		$mlog=M('Shop_order_log');
		$log=$mlog->where('oid='.$cache['id'])->select();
		$this->assign('log',$log);
		if(!$cache['status']==1){
			//是否可以用余额支付
			$useryue=$_SESSION['WAP']['vip']['money'];
			$isyue=$_SESSION['WAP']['vip']['money']-$cache['payprice']>=0?0:1;
			$this->assign('isyue',$isyue);
		}
		$this->assign('cache',$cache);
		$this->assign('isptg',$isptg);
		//代金券调用
		if($cache['djqid']){
			$djq=M('Vip_card')->where('id='.$cache['djqid'])->find();
			$this->assign('djq',$djq);
		}
		//高亮底导航
		$this->assign('actname','ftorder');
		$this->display();
	}

	public function ordermake(){
		// 作者：郑伊凡 2016-1-20 母版本 功能：防止用户入口进入
		if($_SESSION['WAP']['shopset']['ischeckid']){
			// 如果开启身份验证
			$vip=M("Vip")->where(array("id"=>$_SESSION['WAP']['vipid']))->find();
			if(!$vip['isidentify']){
				$this->error('您的身份尚未完善，请先填写信息！',U("wap/vip/info"),3);
			}
		}
		/********提货方式    zxg   16.2.16******/
		$iszts=M('express_set')->find();
		$iszt=$iszts['iszt'];
		$isztcon=$iszts['content'];
		$this->assign('iszt',$iszt);
		$this->assign('isztcon',$isztcon);
		/********提货方式    zxg   16.2.16******/
		
		// 作者：郑伊凡 2016-1-20 母版本 功能：防止用户入口进入
		if(IS_POST){
			$morder=M('Shop_order');
			$data=I('post.');
//			print_r($data);//die;
			$data['items']=stripslashes(htmlspecialchars_decode($data['items']));
			$data['ispay']=0;
			$data['status']=1;//订单成功，未付款
			$data['ctime']=time();
			$data['totalnum']='1';			
			
			//邮费逻辑
			// 如果用户选择自提
			if($data['tqtype']=="ziti"){
				$data['yf'] = 0;
			}
			$data['totalprice']=$data['yf'];
			$data['payprice']=$data['yf'];
			$data['yf']=$data['yf'];
			$data['integpay']=$data['integpay'];
			$data['sid']=$_SESSION['WAP']['vip']['sid'];
			$re=$morder->add($data);
//			echo $morder->getLastSql();die;
			if($re){
				$old=$morder->where('id='.$re)->setField('oid',date('YmdHis').'-'.$re);
				if(FALSE !== $old){
					//后端日志
					$mlog=M('Shop_order_syslog');
					$dlog['oid']=$re;
					$dlog['msg']='订单创建成功';
					$dlog['type']=1;
					$dlog['ctime']=time();
					$rlog=$mlog->add($dlog);
					//清空购物车
					$rbask=M('Shop_basket')->where(array('sid'=>$data['sid'],'vipid'=>$data['vipid']))->delete();
					$this->redirect(U('Wap/Points/pay/',array('sid'=>$data['sid'],'orderid'=>$re)));
				}else{
					$old=$morder->delete($re);
					$this->error('订单生成失败！请重新尝试！');
				}
			}else{
				//可能存在代金券问题
				$this->error('订单生成失败！请重新尝试！');
			}
			
		}else{
			//非提交状态
			$sid=0;//sid可以为0
			//清空临时地址
			unset($_SESSION['WAP']['orderURL']);
			//已登陆
			$m=M('Shop_basket');
			$mgoods=M('Shop_goods');
			$msku=M('Shop_goods_sku');
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
			//聚友杀逻辑
			$allmy=1;
			$totaliscut=0;
			$totalcutlow=0;
			$totalcuttop=0;
			$totalcutmax=0;
			foreach($cache as $k=>$v){
				$goods=$mgoods->where('id='.$v['goodsid'])->find();	
				//sku模型
				$pic=$this->getPic($goods['pic']);
				
				if($goods['status']){
					if($goods['num']){
						//调整购买量
						$cache[$k]['goodsid']=$goods['id'];
						$cache[$k]['skuid']=0;
						$cache[$k]['name']=$goods['name'];
						$cache[$k]['skuattr']=$sku['skuattr'];
						$cache[$k]['num']='1';
						$cache[$k]['integpay']=$goods['integpay'];
						
						// $cache[$k]['total']=$v['num']*$goods['price'];
						$cache[$k]['total']='1'*$cache[$k]['integpay'];
						$cache[$k]['pic']=$pic['imgurl'];
						$totalnum=$totalnum+$cache[$k]['num'];
						$totalprice=$totalprice+$cache[$k]['integpay']*$cache[$k]['num'];
						
						if($goods['ismy']==0){
							$heavy=$heavy+$goods['heavy']*$v['num'];
							$allmy=0;
						}
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
			$this->assign('integpay',$goods['integpay']);//产品积分机制价格
			$this->display();
		}
	}
	
	//zxg   2.25   立刻购买逻辑
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
			$sid=0;
			//前端必须保证登陆状态
			$vipid=$_SESSION['WAP']['vipid'];
			$re=$m->where(array('sid'=>$sid,'vipid'=>$vipid))->delete();
			//区分SKU模式
			if($data['sku']){
				$rold=$m->add($data);
				if($rold){
						$info['status']=1;
						$info['msg']='库存检测通过！2秒后自动生成订单！';
					}else{						
						$info['status']=0;
						$info['msg']='通讯失败，请重新尝试！';
				}
			}else{
				$rold=$m->add($data);
				if($rold){
						$info['status']=1;
						$info['msg']='库存检测通过！2秒后自动生成订单！';
				}else{						
						$info['status']=0;
						$info['msg']='通讯失败，请重新尝试！';
				}
			}
			$this->ajaxReturn($info);
		}else{
			$this->diemsg(0, '禁止外部访问！');
		}
	}

	public function exchange(){
		$m=M('Points_log');
		$map=array(
					'vipid'=>$_SESSION['WAP']['vipid'],
					'openid'=>$_SESSION['WAP']['vip']['openid'],
				);
		$cache=$m->where($map)->select();
		$this->assign('cache',$cache);
		$this->display();
	}

	public function jilu(){
		$score=$_SESSION['WAP']['vip']['score'];
		$m=M('Vip_log');
		$vipid=$_SESSION['WAP']['vipid'];
		$map=array(
					'vipid'=>$vipid,
				    'score'=>array('neq',0),
				);
		$cache=$m->where($map)->order('ctime desc')->limit('0,20')->select();
		$this->assign('cache',$cache);
		$this->assign('score',$score);
		$this->display();
	}
	
	//订单支付
	public function pay(){
		
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$sid=$_SESSION['WAP']['vip']['sid']<>''?$_SESSION['WAP']['vip']['sid']:$this->diemsg(0, '缺少SID参数');
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		$type=I('type');
		$mode=I('mode');
		$bkurl=U('Wap/Points/pay',array('sid'=>$sid,'orderid'=>$orderid,'type'=>$type));		
		$backurl=base64_encode($orderdetail);
		$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		
		$m=M('Shop_order');
		$plog=M('points_log');
//		if($mode=='integ'){
//			$maps['id']=$m->where(array('oid'=>$orderid))->getField('id');
//		}else{
			$maps['id']=$orderid;
//		}
		//已登陆
		$order=$m->where($maps)->find();
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
				$vip=$mvip->where('id='.$_SESSION['WAP']['vipid'])->find();
				$pp=$vip['money']-$order['yf'];
				if($pp>=0){
					$re=$mvip->where('id='.$_SESSION['WAP']['vipid'])->setField('money',$pp);
//					echo $mvip->getLastSql();die;
					if($re){
						
						$order['paytype']='money';
						$order['sid']=$sid;
						$order['ispay']=1;
						$order['paytime']=time();
						$order['status']=2;
						$rod=$m->save($order);
						if(FALSE !== $rod){
							
							//扣除用户积分
							if($order['integpay']){
								$sco =$mvip->where('id='.$_SESSION['WAP']['vipid'])->setDec('score',$order['integpay']);
							}
							
							
							//判断points_log表中是否有相关数据
							$pid = $plog->where(array('oid'=>$order['id']))->find();
							if($pid){
								$items=unserialize($order['items']);
								
								$porder['vipid']=$order['vipid'];
								$porder['openid']=$order['vipopenid'];
								$porder['goodsid']=$items['0']['goodsid'];
								$porder['oid']=$order['id'];
								$porder['sid']=$sid;
								$porder['integpay']=$order['integpay'];
								$porder['name']=$items['0']['name'];
								$porder['pic']=$items['0']['pic'];
								$porder['ctime']=time();
								$log=$plog->where(array('oid'=>$order['id']))->save($porder);
							}else{
								//joel_points_log
								$items=unserialize($order['items']);
								
								$porder['vipid']=$order['vipid'];
								$porder['openid']=$order['vipopenid'];
								$porder['goodsid']=$items['0']['goodsid'];
								$porder['oid']=$order['id'];
								$porder['sid']=$sid;
								$porder['integpay']=$order['integpay'];
								$porder['name']=$items['0']['name'];
								$porder['pic']=$items['0']['pic'];
								$porder['ctime']=time();
								$log=$plog->add($porder);
							}
							
							//==付款成功-发送模板消息通知该会员==============================
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
							$dlog['msg']='支付成功';
							$dlog['ctime']=time();
							$rlog=$mlog->add($dlog);
							//后端日志
							$mlog=M('Shop_order_syslog');
							$dlog['type']=2;
							$rlog=$mlog->add($dlog);

								$this->success('支付成功！',U('Wap/points/orderList',array('sid'=>$sid,'type'=>'2')));
							
							
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
							$dlog['msg']='订单状态改变失败';
							$dlog['type']=-1;
							$dlog['ctime']=time();
							$rlog=$mlog->add($dlog);
							$this->error('支付失败！请联系客服！');
						}
					}else{
						//后端日志
						$mlog=M('Shop_order_syslog');
						$dlog['oid']=$order['id'];
						$dlog['msg']='积分扣除失败';
						$dlog['type']=-1;
						$dlog['ctime']=time();
						$this->error('支付失败，请重新尝试！');
					}
				}else{
					$this->error('余额不足，请使用其它方式付款！');
				}
				break;
			case 'alipaywap':
				$this->redirect(U('/Home/Alipaywap/pay',array('sid'=>$sid,'price'=>$order['payprice'],'oid'=>$order['oid'],'mode'=>$mode,'orderid'=>$order['id'],)));
				break;
			case 'wxpay':
				$_SESSION['wxpaysid']=$_SESSION['WAP']['vip']['sid'];
				$_SESSION['wxpayopenid']=$_SESSION['WAP']['vip']['openid'];//追入会员openid
				$this->redirect(U('/Home/PointsWxpay/pay',array('oid'=>$order['oid'],'mode'=>$mode,'orderid'=>$order['id'])));
			break;
			default:
				$mvip=M('Vip');
				$vip=$mvip->where('id='.$_SESSION['WAP']['vipid'])->find();
				$pp=$vip['score']-$order['integpay'];
				$re=$mvip->where('id='.$_SESSION['WAP']['vipid'])->setField('score',$pp);
				if($re){
					
					//订单日志  shop_order
					$order['paytype']='';
					$order['ispay']=1;
					$order['paytime']=time();
					$order['status']=2;
					$order['totalprice']='';
					$rod=$m->save($order);

					//判断points_log表中是否有相关数据
							$pid = $plog->where(array('oid'=>$order['id']))->find();
							if($pid){
								$items=unserialize($order['items']);
								
								$porder['vipid']=$order['vipid'];
								$porder['openid']=$order['vipopenid'];
								$porder['goodsid']=$items['0']['goodsid'];
								$porder['oid']=$order['id'];
								$porder['sid']=$sid;
								$porder['integpay']=$order['integpay'];
								$porder['name']=$items['0']['name'];
								$porder['pic']=$items['0']['pic'];
								$porder['ctime']=time();
								$log=$plog->where(array('oid'=>$order['id']))->save($porder);
							}else{
								//joel_points_log
								$items=unserialize($order['items']);
								
								$porder['vipid']=$order['vipid'];
								$porder['openid']=$order['vipopenid'];
								$porder['goodsid']=$items['0']['goodsid'];
								$porder['oid']=$order['id'];
								$porder['sid']=$sid;
								$porder['integpay']=$order['integpay'];
								$porder['name']=$items['0']['name'];
								$porder['pic']=$items['0']['pic'];
								$porder['ctime']=time();
								$log=$plog->add($porder);
							}
					$this->success('支付成功！',U('Wap/Points/orderList',array('sid'=>$sid,'type'=>'2')));
				}else{
					$this->success('支付失败！',U('Wap/Points/orderList',array('sid'=>$sid,'type'=>'2')));
				}
				
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
			$shopset=M('Shop_set')->find();//追入商城设置
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
