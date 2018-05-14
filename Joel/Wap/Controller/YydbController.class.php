<?php
// 积分商城控制器
// 本类由系统自动生成，仅供测试用途
namespace Wap\Controller;
use Wap\Controller\BasehdController;
class YydbController extends BaseController {
	
	
	public function _initialize() {
		header("Content-type: text/html; charset=utf-8");
		
		//你可以在此覆盖父类方法	
		parent::_initialize();
		$shopset=M('Shop_set')->find();
		if($shopset['yydbimg']){
			$listpic=$this->getPic($shopset['yydbimg']);
			$shopset['sharepic']=$listpic['imgurl'];
		}
		if($shopset){
			self::$WAP['shopset']=$_SESSION['WAP']['shopset']=$shopset;
			$this->assign('shopset',$shopset);
			
		}else{
			$this->diemsg(0,'您还没有进行商城配置！');
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
//		echo $_SESSION['WAP']['shopset']['yydbimg'];
		// 分享
//		$shops=M('Shop_set')->find();
//		$this->assign('shops',$shops);
//		echo $shopset['yydbname'];
//		echo $shopset['yydbmsg'];
		
		//轮播
		$indexalbum=M('Shop_ads')->where('ispc=0')->select();
		foreach($indexalbum as $k=>$v){
			$listpic=$this->getPic($v['pic']);
			$indexalbum[$k]['imgurl']=$listpic['imgurl'];
		}
		$this->assign('indexalbum',$indexalbum);
		// 轮播
		
		$mlabel=M("shop_label");
		$label=$mlabel->select();
		foreach($label as $k=>$v){
			$lpic=$this->getPic($v['lpic']);
			$label[$k]['limgurl']=$lpic['imgurl'];			
			$listpic=$this->getPic($v['pic']);
			$label[$k]['imgurl']=$listpic['imgurl'];
		}
		$this->assign('indexcache',$label);
				
		$m=M("yydb_goods");
		$cache=$m->select();
		foreach($cache as $k=>$v){ 
			$img=$this->getPic($v['listpic']);
			$cache[$k]['imgurl']=$img['imgurl'];
			
			$cache[$k]['have']=$v['num']-$v['sells'];//剩余
			
			$selnum=$v['sells'] / $v['num'];//已用
			$selnum=round($selnum,2);
			$cache[$k]['selnum'] = $selnum*100;
		}
		$this->assign('cache',$cache);
		$this->display();
	}
	
	//商品详情
	public function goods(){
		//一元夺宝商品
		$id=I('id');
		$map=array('id'=>$id);
		$m=M("yydb_goods");
		$cache=$m->where($map)->find();
		//详情
		$sm=M("shop_goods");
		$content=$sm->where(array('id'=>$cache['goodsid']))->getField('content');
		$this->assign('content',$content);
		
		//绑定图片
		if($cache['listpic']){
			$joelpic=$this->getPic($cache['listpic']);
			$cache['imgurl']=$joelpic['imgurl'];
		}
		$yhave=$cache['num']-$cache['sells'];//剩余
		$selnum=$cache['sells'] / $cache['num'];//已用
		$selnum=round($selnum,2);
		$selnum = $selnum*100;//剩余百分比
		$this->assign('yhave',$yhave);//剩余次数
		$this->assign('selnum',$selnum);//剩余百分比
		
		$gyydb=M('yydb_log');//夺宝记录
		$zjyydb=M('yydb_zj');//中奖记录
		$sorder=M('shop_order');
		
		//生成中奖人
		//先判断是否有中奖记录
//		$iszj=$zjyydb->where(array('yid'=>$cache['id']))->find();
//		if(!$iszj){
//
//			//当库存数等于购买数时
//			if($cache['num'] == $cache['sells']){
//				
//				//是否指定派奖
//				if($cache['isvip']=='1'){
//					
//					$vipid=$cache['vipid'];//中奖人ID
//					$nickname=$cache['nickname'];//中奖人昵称
//					$whe['vipid']=$vipid;
//					$whe['goodsid']=$cache['id'];
//					$maxcode=$gyydb->where($whe)->find();
//					//如果有抽奖记录，则派奖，否则随机抽奖
//					if($maxcode){
//						$win=$maxcode['code'];
//						
//					}else{
//						//查询当前产品记录数，当数量不满100时，用实际数量，如果满了100，则用100计算
//						$count=$gyydb->where(array('yid'=>$cache['id']))->count();
//						if($count>100){
//							$snum=100;
//						}else{
//							$snum=$count;
//						}
//						//去购买此产品的全部购买时间相加，除以全部参与人数
//						$stime=$gyydb->where(array('yid'=>$cache['id']))->select();
//						$stimes=0;
//						for($i=0;$i<count($stime);$i++){
//							
//							$stimes+=$stime[$i]['ctime'];
//						}
//						//取余
//						$win=fmod($stimes,$snum);
//					}
//				}else{
//					
//					//查询当前产品记录数，当数量不满100时，用实际数量，如果满了100，则用100计算
//					$count=$gyydb->where(array('yid'=>$cache['id']))->count();
//					if($count>100){
//						$snum=100;
//					}else{
//						$snum=$count;
//					}
//					//去购买此产品的全部购买时间相加，除以全部参与人数
//					$stime=$gyydb->where(array('yid'=>$cache['id']))->select();
//					$stimes=0;
//					for($i=0;$i<count($stime);$i++){
//						
//						$stimes+=$stime[$i]['ctime'];
//					}
//					//取余
//					$win=fmod($stimes,$snum);
//					
//				}
//				$win='100000001'+$win;
//				//添加yydb_zj中奖人信息
//				$where['code']=$win;
//				$where['goodsid']=$cache['id'];
//				$mas=$gyydb->where($where)->find();
//				
//				$headimg=M('vip')->where(array('id'=>$mas['vipid']))->getField('headimgurl');
//				$zjs['yid']=$cache['id'];
//				$zjs['oid']=$mas['id'];
//				$zjs['sid']=$mas['sid'];
//				$zjs['vipid']=$mas['vipid'];
//				$zjs['nickname']=$mas['vipname'];
//				$zjs['vipopenid']=$mas['vipopenid'];
//				$zjs['headimg']=$headimg;
//				$zjs['code']=$win;
//				$zjs['ctime']=time();
//				$zjyydb->add($zjs);
//				$res=$zjyydb->where($zjs)->find();
//
//				//添加订单信息 shop_order
//				$logs=$gyydb->where(array('id'=>$res['oid']))->find(); 
				
//				$lwhere['goodsid']=$cache['id'];
//				$lwhere['vipid']=$mas['vipid'];
//				$l=$gyydb->where($lwhere)->select(); 
//				for($i=0;$i<count($l);$i++){
//					
//					$totalprice+=$l[$i]['totalprice'];
//					$totalnum+=$l[$i]['totalnum'];
//					$payprice+=$l[$i]['payprice'];
//				}
//
//				$logs['status']='2';//已付款
//				$logs['yydb']='1';//订单为一元夺宝订单
//				$logs['oid']=$logs['oid'];
//				$logs['totalprice']=$totalprice;//总金额
//				$logs['totalnum']=$totalnum;//总数量
//				$logs['payprice']=$payprice;//支付金额
//				unset($logs['goodsid']);
//				unset($logs['iswin']);
//				$sorder->add($logs);
//				
//			}
//		}
		// 判断是否需要认证身份
		$ischeckid=M('Shop_set')->getField("ischeckid");
		$this->assign("ischeckid",$ischeckid);

		$this->assign('gnum',$cache['num']);
		$this->assign('cache',$cache);
		$this->assign('goodsid',$cache['id']);
		
		$loginback=U('Wap/Vip/login',array('backurl'=>$backurl));
		$this->assign('loginback',$loginback);
		
		$this->display();
	}
	
	/////////夺宝记录
	public function yydblog(){
		$goodsid=I('goodsid');
		$type=I('type');
		$vip=M('vip');
		$ylog=M('yydb_log');
		if($type=='zj'){
			
			$iszj=M('yydb_zj')->where('yid='.$goodsid)->find();
			if($iszj){
				$logs=$ylog->where(array('vipid'=>$iszj['vipid'],'goodsid'=>$goodsid))->field(array("count(vipid)"=>"countvip","vipid","vipname", "vipopenid","ctime","paytime","code"))->group('vipid')->select();
				foreach($logs as $v=>$k){
					$headimgurl=$vip->where('id='.$k['vipid'])->find();
					$logs[$v]['headimgurl']=$headimgurl['headimgurl'];
					$logs[$v]['nickname']=$headimgurl['nickname'];
					$logs[$v]['jxsj']=$iszj['ctime'];
				}
				
			}
		}else if($type == 'gr'){
			//夺宝记录
			$logs=$ylog->where(array('goodsid'=>$goodsid,'vipid'=>$_SESSION['WAP']['vipid']))->field(array("vipid","vipname", "vipopenid","ctime","paytime","code"))->order('paytime desc')->select();
			foreach($logs as $v=>$k){
				$headimgurl=$vip->where('id='.$k['vipid'])->find();
				$logs[$v]['headimgurl']=$headimgurl['headimgurl'];
				$logs[$v]['nickname']=$headimgurl['nickname'];
			}
		}else{
			
			//夺宝记录
			$logs=$ylog->where('goodsid='.$goodsid)->field(array("count(vipid)"=>"countvip","id", "vipid","vipname", "vipopenid","ctime","paytime"))->group('vipid')->order('paytime desc')->select();
			foreach($logs as $v=>$k){
				$headimgurl=$vip->where('id='.$k['vipid'])->find();
				$logs[$v]['headimgurl']=$headimgurl['headimgurl'];
				$logs[$v]['nickname']=$headimgurl['nickname'];
			}
		}
		$this->assign('type',$type);
		$this->assign('cache',$logs);
		$this->assign('goodsid',$goodsid);
		$this->display();
	}
	
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
	
	//订单地址跳转
	public function orderAddress(){
		$sid=I('sid');
		$lasturlencode=I('lasturl');
		if($lasturlencode){
			$backurl=base64_decode($lasturlencode);
		}else{
			$backurl=U('Wap/yydb/orderMake');
		}
		// $backurl=U('Wap/Shop/orderMake',array('sid'=>$sid,'lasturl'=>$lasturlencode));
		$_SESSION['WAP']['orderURL']=$backurl;
		$this->redirect(U('Wap/Vip/address',array('ptgty'=>'yydb')));
	}
	
	//确认订单
	public function ordermake(){
		// 作者：郑伊凡 2016-1-20 母版本 功能：防止用户入口进入
//		if($_SESSION['WAP']['shopset']['ischeckid']){
//			// 如果开启身份验证
//			$vip=M("Vip")->where(array("id"=>$_SESSION['WAP']['vipid']))->find();
//			if(!$vip['isidentify']){
//				$this->error('您的身份尚未完善，请先填写信息！',U("wap/vip/info"),3);
//			}
//		}
		/********提货方式    zxg ******/
		$iszts=M('express_set')->find();
		$iszt=$iszts['iszt'];
		$isztcon=$iszts['content'];
		$this->assign('iszt',$iszt);
		$this->assign('isztcon',$isztcon);
		
		// 作者：郑伊凡 2016-1-20 母版本 功能：防止用户入口进入
		if(IS_POST){
			$morder=M('yydb_log');
			$ygoods=M('yydb_goods');
			$data=I('post.');
			$ids = $data['goodsid'];//一元夺宝ID
			$data['goodsid']=$ids;//商品ID
			$data['items']=stripslashes(htmlspecialchars_decode($data['items']));
			$data['ispay']=0;
			$data['status']=1;//订单成功，未付款
			
			
			//邮费逻辑
			// 如果用户选择自提
//			if($data['tqtype']=="ziti"){
				$data['yf'] = 0;
//			}
			$ynum=$data['totalnum'];
			$yu=$ygoods->where(array('id'=>$ids))->find();
			$sy=$yu['num']-$yu['sells'];
			if($sy<$ynum){
				
				$this->error('库存不足！请重新尝试！');
			}
			
			//中奖算法
			
			$data['totalprice']=$data['totalprice'];
			$data['payprice']=$data['totalprice'];
			$data['yid']=$ids;
			$data['sid']=$_SESSION['WAP']['vip']['sid'];
			
			//修改商品库存
			$ygoo = $ygoods->where(array('id'=>$ids))->find();
					
			//生成云购码
			$nums = $data['totalnum'];
			for($i=0;$i<$nums;$i++){

				$data['totalprice']=$ygoo['yprice'];
				$data['payprice']=$ygoo['yprice'];
				$data['totalnum']='1';
				
				//精算到毫秒
				$a=explode(" ", microtime());
				$time = $a [1] . ($a [0] * 1000);  
				$time2 = explode ( ".", $time );  
				$time = $time2 [0];
				$data['ctime']=$time;
				$re=$morder->add($data);
				$res[]=$re;
			}
			$arr = serialize($res);
			if($res){
				for($j=0;$j<count($res);$j++){
					$olds=$morder->where('id='.$res[$j])->setField('oid',date('YmdHis').'-'.$res[$j]);
				}
				if(FALSE !== $olds){
					
					$sells=$ygoo['sells']+$ynum;
					$ygoods->where(array('id'=>$ids))->setField('sells',$sells);
					
					//后端日志
					$mlog=M('Shop_order_syslog');
					$dlog['oid']=$re;
					$dlog['msg']='订单创建成功';
					$dlog['type']=1;
					$dlog['ctime']=time();
					$rlog=$mlog->add($dlog);
					//清空购物车
					$rbask=M('Shop_basket')->where(array('sid'=>$data['sid'],'vipid'=>$data['vipid'],'type'=>'yydb'))->delete();
					$this->redirect(U('Wap/Yydb/pay/',array('sid'=>$data['sid'],'orderid'=>$arr)));
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
			// $sid=0;//sid可以为0
			//清空临时地址
			unset($_SESSION['WAP']['orderURL']);
			//$_SESSION['WAP']['vipid']="11910";
			//已登陆
			$m=M('Shop_basket');
			$mgoods=M('yydb_goods');
			$g=M('Shop_goods');
			$msku=M('Shop_goods_sku');
			$cache=$m->where(array('vipid'=>$_SESSION['WAP']['vipid'],'type'=>'yydb'))->select();
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
				$goods['status']=$g->where('id='.$goods['goodsid'])->getField('status');
				//sku模型
				$pic=$this->getPic($goods['listpic']);
				if($goods['status']){
					if($goods['num']){
						//调整购买量
						$cache[$k]['goodsid']=$goods['id'];
						$cache[$k]['skuid']=0;
						$cache[$k]['name']=$goods['gname'];
						$cache[$k]['price']=$goods['price'];
						$cache[$k]['yprice']=$goods['yprice'];
						$cache[$k]['skuattr']=$sku['skuattr'];
 					    $cache[$k]['total']=$v['num']*$goods['yprice'];
						$cache[$k]['pic']=$pic['imgurl'];
						$totalnum=$totalnum+$cache[$k]['num'];
						$totalprice=$totalprice+$goods['yprice']*$cache[$k]['num'];
						
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
			$this->assign('goods',$goods);//产品积分机制价格
			$this->display();
		}
	}
	
	//订单支付
	public function pay(){
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$sid=$_SESSION['WAP']['vip']['sid']<>''?$_SESSION['WAP']['vip']['sid']:$this->diemsg(0, '缺少SID参数');
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		$type=I('type');
		$bkurl=U('Wap/Yydb/pay',array('sid'=>$sid,'orderid'=>$orderid,'type'=>$type));		
		$backurl=base64_encode($orderdetail);
		$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		
		//已登陆
		$m=M('yydb_log');
		$ygoods=M('yydb_goods');
		$sorder=M('shop_order');
		$zjyydb=M('yydb_zj');//中奖记录
		$arr = unserialize($orderid);//反序列化 订单号
		$order=$m->where('id='.$arr['0'])->find();
		
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
				$pp=$vip['money']-$order['payprice'];
				if($pp>=0){
					$re=$mvip->where('id='.$_SESSION['WAP']['vipid'])->setField('money',$pp);
					if($re){
						
						//添加码
						$order['paytype']='money';
						$order['ispay']=1;
						$order['paytime']=time();
						$order['status']=2;
						$order['vipname']=$vip['nickname'];
						
						//判断表内同款商品ID的数量
						$where['goodsid']=$order['goodsid'];
						$where['id']=array('not in',$arr);
						$count = $m->where($where)->count();
						
					    $code='100000000'+ $count;
						for($i=0;$i<count($arr);$i++){
							$or=$m->where('id='.$arr[$i])->find();
							$order['oid']=$or['oid'];
							$order['id']=$arr[$i];
							$code+=1;
							$order['code']=$code;
							$rod=$m->where(array('id'=>$arr[$i]))->save($order);
						}
						//中奖流程 zxg 2016.3.14
						$iszj=$zjyydb->where(array('yid'=>$order['goodsid']))->find();
						if(!$iszj){
							$ygs=$ygoods->where(array('id'=>$order['goodsid']))->find();
							if($ygs['sells']==$ygs['num']){
								//是否指定派奖
								if($ygs['isvip']=='1'){
									
									$vipid=$ygs['vipid'];//中奖人ID
									$nickname=$ygs['nickname'];//中奖人昵称
									$whe['vipid']=$vipid;
									$whe['goodsid']=$ygs['id'];
									$maxcode=$m->where($whe)->find();
									//如果有抽奖记录，则派奖，否则随机抽奖
									if($maxcode){
										$win=$maxcode['code'];
										
									}else{
										//查询当前产品记录数，当数量不满100时，用实际数量，如果满了100，则用100计算
										$count=$m->where(array('yid'=>$ygs['id']))->count();
										if($count>100){
											$snum=100;
										}else{
											$snum=$count;
										}
										//去购买此产品的全部购买时间相加，除以全部参与人数
										$stime=$m->where(array('yid'=>$ygs['id']))->select();
										$stimes=0;
										for($i=0;$i<count($stime);$i++){
											
											$stimes+=$stime[$i]['ctime'];
										}
										//取余
										$win=fmod($stimes,$snum);
									}
								}else{
									//查询当前产品记录数，当数量不满100时，用实际数量，如果满了100，则用100计算
									$count=$m->where(array('yid'=>$ygs['id']))->count();
									if($count>100){
										$snum=100;
									}else{
										$snum=$count;
									}
									//去购买此产品的全部购买时间相加，除以全部参与人数
									$stime=$m->where(array('yid'=>$ygs['id']))->select();
									$stimes=0;
									for($i=0;$i<count($stime);$i++){
										
										$stimes+=$stime[$i]['ctime'];
									}
									//取余
									$win=fmod($stimes,$snum);
									
								}
								$win='100000001'+$win;
								//添加yydb_zj中奖人信息
								$wheno['code']=$win;
								$wheno['goodsid']=$ygs['id'];
								$mas=$m->where($wheno)->find();
								$vips=M('vip')->where(array('id'=>$mas['vipid']))->find();
								$zjs['yid']=$ygs['id'];
								$zjs['sid']=$mas['sid'];
								$zjs['vipid']=$mas['vipid'];
								$zjs['nickname']=$vips['nickname'];
								$zjs['vipopenid']=$vips['openid'];
								$zjs['headimg']=$vips['headimgurl'];
								$zjs['code']=$win;
								$zjs['ctime']=time();
								$zjyydb->add($zjs);
								$res=$zjyydb->where($zjs)->find();
				
								//添加订单信息 yydb_log
								$logs=$m->where(array('yid'=>$res['yid']))->find(); 
								$lwhere['goodsid']=$ygs['id'];
								$lwhere['vipid']=$mas['vipid'];
								$l=$m->where($lwhere)->select(); 
								for($i=0;$i<count($l);$i++){
									
									$totalprice+=$l[$i]['totalprice'];
									$totalnum+=$l[$i]['totalnum'];
									$payprice+=$l[$i]['payprice'];
								}
				
								$logs['status']='2';//已付款
								$logs['yydb']='1';//订单为一元夺宝订单
								$logs['oid']=$logs['oid'];
								$logs['totalprice']=$totalprice;//总金额
								$logs['totalnum']=$totalnum;//总数量
								$logs['payprice']=$payprice;//支付金额
								$logs['ptgid']='0';
								$logs['integpay']='0';
								$logs['ctime']=time();
								$logs['paytime']=time();
								unset($logs['goodsid']);
								unset($logs['iswin']);
								$sres=M('shop_order')->add($logs);
								$res=$zjyydb->where($zjs)->save(array('oid'=>$sres));
							}
						}
						//中奖流程 zxg 2016.3.14
						if(FALSE !== $rod){
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
							$dlog['msg']='余额-付款成功';
							$dlog['ctime']=time();
							$rlog=$mlog->add($dlog);
							//后端日志
							$mlog=M('Shop_order_syslog');
							$dlog['type']=2;
							$rlog=$mlog->add($dlog);
							
							$this->success('余额付款成功！',U('Wap/Yydb/yydblog',array('goodsid'=>$order['goodsid'],'type'=>'gr')));
							
							
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
							$this->error('余额付款失败！请联系客服！');
						}
					}else{
						//后端日志
						$mlog=M('Shop_order_syslog');
						$dlog['oid']=$order['id'];
						$dlog['msg']='积分扣除失败';
						$dlog['type']=-1;
						$dlog['ctime']=time();
						$this->error('余额支付失败，请重新尝试！');
					}
				}else{
					$this->error('余额不足，请使用其它方式付款！');
				}
				break;
//			case 'alipaywap':
//				$this->redirect(U('/Home/Alipaywap/pay',array('sid'=>$sid,'price'=>$order['payprice'],'oid'=>$order['oid'],'mode'=>$mode)));
//				break;
			case 'wxpay':
				$_SESSION['wxpaysid']=$_SESSION['WAP']['vip']['sid'];
				$_SESSION['wxpayopenid']=$_SESSION['WAP']['vip']['openid'];//追入会员openid
				$this->redirect(U('/Home/YydbWxpay/pay',array('oid'=>$arr['0'],'oids'=>$orderid)));
			break;
			default:
				$this->error('支付方式未知！');
				break;
		}
		
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
			$sid=0;
			//前端必须保证登陆状态
			$vipid=$_SESSION['WAP']['vipid'];
			$re=$m->where(array('sid'=>$sid,'vipid'=>$vipid))->delete();
			$data['type']="yydb";
			//区分SKU模式
//			if($data['sku']){
//				$rold=$m->add($data);
//				if($rold){
//						$info['status']=1;
//						$info['msg']='库存检测通过！2秒后自动生成订单！';
//					}else{						
//						$info['status']=0;
//						$info['msg']='通讯失败，请重新尝试！';
//				}
//			}else{
				$rold=$m->add($data);
				if($rold){
						$info['status']=1;
						$info['msg']='库存检测通过！2秒后自动生成订单！';
				}else{						
						$info['status']=0;
						$info['msg']='通讯失败，请重新尝试！';
				}
//			}
			$this->ajaxReturn($info);
		}else{
			$this->diemsg(0, '禁止外部访问！');
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
