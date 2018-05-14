<?php
// 本类由系统自动生成，仅供测试用途
namespace Wap\Controller;
use Wap\Controller\BasehdController;
class JysController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();	
		// 作者：郑伊凡 2016-1-25 母版本 功能：聚友杀入口限制
		$isjys=M('Shop_set')->getField('isjys');
		if(!$isjys){
			$this->diemsg(0, "本网站未开启聚友杀功能哦~");
		}
		// 作者：郑伊凡 2016-1-25 母版本 功能：聚友杀入口限制		
		
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
		$sid=I('sid');
		$orderid=I('orderid');
		
		$isptg=I('isptg');
		$this->assign('isptg',$isptg);
		$fxshop=I('fxshop');
		$this->assign('fxshop',$fxshop);

		$m=M('Shop_order');
		$vipid=$_SESSION['WAP']['vipid'];
		$vip=$_SESSION['WAP']['vip'];
		if(!$vip){
			session(null);
			$this->diemsg(0, "未能获取正确信息，请重新尝试！");
		}
		$this->assign('vip',$vip);
		$map['sid']=$sid;
		$map['id']=$orderid;
		$cache=$m->where($map)->find();
		if(!$cache){
			$this->diemsg(0,'此订单不存在!');
		}
		$cache['items']=unserialize($cache['items']);
		$goods=M('Shop_goods')->where('id='.$cache['items'][0]['goodsid'])->field('summary')->find();
		$this->assign('cache',$cache);
		
		//取出砍价日志
		$cutlog=M('Jys_log')->where(array('oid'=>$orderid))->order('ctime desc')->select();
		$totalcut=count($cutlog);
		$this->assign('cutlog',$cutlog);
		$this->assign('totalcut',$totalcut);
		$this->assign('goods',$goods);
		//是否是主人模式
		$isself=$cache['vipid']==$vipid?1:0;
		$this->assign('isself',$isself);
		//是否已砍过价
		if(!$isself){
			$iscut=M('Jys_log')->where(array('oid'=>$orderid,'vipid'=>$vipid))->find();
			$iscut=$iscut?1:0;
			$this->assign('iscut',$iscut);
			$zr=M('Vip')->where('id='.$cache['vipid'])->find();
			$this->assign('zr',$zr);
			
		}else{
			//取出主人
			$zr=M('Vip')->where('id='.$cache['vipid'])->find();
			$this->assign('zr',$zr);
		}		
		
		//取出聚友杀宣言
		if($shopset['jysmsg']){
			$jysmsg=array_filter(explode('##', $shopset['jysmsg']));
			$this->assign('firstmsg',$jysmsg[0]);
			$this->assign('jysmsg',$jysmsg);
		}else{
			$this->diemsg(0, '系统未设置聚友杀宣言！');
		}
		$this->display();
    }

	public function setMsg(){
		if(IS_POST){
			//die('aa');
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
			$re=M('Shop_order')->where('id='.$data['id'])->setField('cutmsg',$data['msg']);
			if($re!==FALSE){
					$info['status']=1;
					$info['msg']='设置砍价宣言成功！快去分享给朋友砍价吧！';
			}else{
					$info['status']=0;
					$info['msg']='设置砍价宣言失败！请重新尝试！';
			}
			
			$this->ajaxReturn($info);
		}else{
			$this->diemsg(0, '非法访问！');
		}
	}

	public function cut(){
		$orderid=I('orderid');
		$m=M('Shop_order');
		$mlog=M('Jys_log');
		$map['id']=$orderid;
		$cache=$m->where($map)->find();
		if(!$cache){
			$this->diemsg(0,'此订单不存在!');
		}
		if($cache['iscut']!=1){
			$this->diemsg(0, '此订单不允许再砍价了！');
		}
		$vipid=$_SESSION['WAP']['vipid'];
		$vip=$_SESSION['WAP']['vip'];
		if(!$vipid){
			session(null);
			$this->diemsg(0,'未正常获取会员数据，请重新尝试!');
		}
		$old=$mlog->where(array('oid'=>$cache['id'],'vipid'=>$vipid))->find();
		if($old){
			$this->error('每个人只能砍一次价哦！');
		}
		
		$islast=0;//是否是最后一次砍价
		
		$data['cutnum']=$this->randomFloat($cache['cutlow'],$cache['cuttop']);
		$data['cutnum']=number_format($data['cutnum'],2);
		if($cache['cuttotal']+$data['cutnum']>$cache['cutmax']){
			$data['cutnum']=number_format($cache['cutmax']-$cache['cuttotal'],2);
			$islast=1;
		}
		$cache['payprice']=$cache['payprice']-$data['cutnum'];
		$cache['cuttotal']=$cache['cuttotal']+$data['cuttotal'];
		if($islast){
			$cache['iscut']=2;//砍价已完成
		}
		$re=$m->save($cache);
		if(FALSE !== $re){
			//砍价成功，追加日至
			$data['oid']=$cache['id'];
			$data['vipid']=$vipid;
			$data['nickname']=$vip['nickname'];
			$data['headimgurl']=$vip['headimgurl'];
			$data['openid']=$vip['openid'];
			$data['ctime']=time();
			$rlog=$mlog->add($data);
			$this->success('砍价成功！');
		}else{
			//砍价失败
			$this->error('砍价失败！请重试！');
		}
	}
	
	private function randomFloat($min = 0, $max = 1) {  
    	return $min + mt_rand() / mt_getrandmax() * ($max - $min);  
	}  

	public function jyslist(){
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
		$map['isgroup']=0;
		$map['iscut']=1;
		$map['status']=1;
		$cache=$m->where($map)->field('id,name,listpic,summary,cutmax')->select();
		foreach ($cache as $k=>$v){
			$imgurl=$this->getPic($v['listpic']);
			$cache[$k]['imgurl']=$imgurl['imgurl'];
		}
		$this->assign('cache',$cache);
		$this->display();
	}

	public function jysgoods(){
		$id=I('id');
		$map=array(
					'id'=>$id,
					'isgroup'=>0,
					'iscut'=>1,
					'status'=>1
					);
		$m=M("Shop_goods");
		$cache=$m->where($map)->find();
		// 判断是否需要认证身份
		$ischeckid=M('Shop_set')->getField("ischeckid");
		$this->assign("ischeckid",$ischeckid);
		//绑定图集
		// if($cache['album']){
		// 	$joelalbum=$this->getAlbum($cache['album']);
		// 	if($joelalbum){
		// 		$this->assign('joelalbum',$joelalbum);
		// 	}
		// }
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

		//非提交状态
		$sid=$_SESSION['WAP']['vip']['sid'];
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
		//聚友杀逻辑
		$allmy=1;
		$totaliscut=0;
		$totalcutlow=0;
		$totalcuttop=0;
		$totalcutmax=0;
		$allmy=1;
		foreach($cache as $k=>$v){
			$goods=$mgoods->where('id='.$v['goodsid'])->find();	
			//sku模型
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
					$cache[$k]['price']=$goods['price'];
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
		//聚友杀逻辑
		$this->assign('totaliscut',$totaliscut);
		$this->assign('totalcutlow',$totalcutlow);
		$this->assign('totalcuttop',$totalcuttop);
		$this->assign('totalcutmax',$totalcutmax);
		// 作者：郑伊凡 2016-1-25 母版本 功能：控制前台的聚友杀、拼团购
		$this->assign('isjys',self::$WAP['shopset']['isjys']);
		// 作者：郑伊凡 2016-1-25 母版本 功能：控制前台的聚友杀、拼团购
		$this->assign('firstmsg',$jysmsg[0]);
		$this->assign('sid',$sid);
		$this->assign("lasturlencode",base64_encode(U('wap/jys/ordermake')));
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

	//Order 聚友杀
	public function orderJys(){
		//追入渠道
		$sid=$_SESSION['WAP']['vip']['sid'];
		$this->assign('sid',$sid);
		if(IS_POST){
			$morder=M('Shop_order');
			$data=I('post.');
			$data['items']=stripslashes(htmlspecialchars_decode($data['items']));
			$data['ispay']=0;
			$data['status']=1;//订单成功，未付款
			$data['ctime']=time();
			$data['payprice']=$data['totalprice'];
			//聚友杀模式
			$data['iscut']=1;
			$data['cutlow']=$data['cutlow'];
			$data['cuttop']=$data['cuttop'];
			$data['cutmax']=$data['cutmax'];
			//代金券流程
			if($data['djqid']){
				$mcard=M('Vip_card');
				$djq=$mcard->where('id='.$data['djqid'])->find();
				if(!$djq){
					$this->error('通讯失败！请重新尝试支付！');
				}
				if($djq['usetime']){
					$this->error('此代金券已使用！');
				}
				$djq['status']=2;
				$djq['usetime']=time();
				$rdjq=$mcard->save($djq);
				if(FALSE === $rdjq){
					$this->error('通讯失败！请重新尝试支付！');
				}
				//修改支付价格
				$data['payprice']=$data['totalprice']-$djq['money'];
			}
			//邮费逻辑
			// 如果用户选择自提
			if($data['tqtype']=="ziti"){
				$data['yf'] = 0;
			}else{
				if(self::$WAP['shopset']['isyf']){
					if($data['totalprice']>=self::$WAP['shopset']['yftop']){
						$data['yf']=0;
					}else{
						$data['yf']=self::$WAP['shopset']['yf'];
						$data['payprice']=$data['payprice']+$data['yf'];
					}
					
				}else{
					$data['yf']=0;
				}
			}
			$re=$morder->add($data);
			if($re){
				$old=$morder->where('id='.$re)->setField('oid',date('YmdHis').'-'.$re);
				if(FALSE !== $old){
//					$mlog=M('Shop_order_log');
//					$dlog['sid']=$sid;
//					$dlog['oid']=$cache['id'];
//					$dlog['msg']='订单开启聚友杀模式。';
//					$dlog['ctime']=time();
//					$rlog=$mlog->add($dlog);
					//后端日志
					$mlog=M('Shop_order_syslog');
					$dlog['sid']=$sid;
					$dlog['oid']=$re;
					$dlog['msg']='订单创建成功';
					$dlog['type']=1;
					$dlog['ctime']=time();
					$rlog=$mlog->add($dlog);
//					$mlog=M('Shop_order_syslog');
//					$dlog['sid']=$sid;
//					$dlog['oid']=$re;
//					$dlog['msg']='订单开启聚友杀模式';
//					$dlog['type']=1;
//					$dlog['ctime']=time();
//					$rlog=$mlog->add($dlog);
					//清空购物车
					$rbask=M('Shop_basket')->where(array('sid'=>$sid,'vipid'=>$data['vipid']))->delete();
					//$this->success('订单创建成功，转向支付界面!',U('Wap/Fxshop/pay/',array('sid'=>$data['sid'],'orderid'=>$re)));
					//转向聚友杀首页
					$this->redirect(U('Wap/Jys/index/',array('sid'=>$sid,'orderid'=>$re)));
				}else{
					$old=$morder->delete($re);
					$this->error('订单生成失败！请重新尝试！');
				}
			}else{
				//可能存在代金券问题
				$this->error('订单生成失败！请重新尝试！');
			}
			
		}else{
				$this->error('非法访问！');
		}
	}


//订单列表
	public function orderDetail(){
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$sid=$_SESSION['WAP']['vip']['sid']<>''?$_SESSION['WAP']['vip']['sid']:$this->diemsg(0, '缺少SID参数');
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		$bkurl=U('Wap/Jys/orderDetail',array('sid'=>$sid,'orderid'=>$orderid));		
		$backurl=base64_encode($bkurl);
		$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$vipid=$_SESSION['WAP']['vipid'];
		$map['sid']=$sid;
		$isptg=I('isptg');
		if($isptg){
			$map['oid']=$orderid;
			$this->assign('isptg',$isptg);
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
		//代金券调用
		if($cache['djqid']){
			$djq=M('Vip_card')->where('id='.$cache['djqid'])->find();
			$this->assign('djq',$djq);
		}
		//高亮底导航
		$this->assign('actname','ftorder');
		$this->display();
	}


//订单支付
	public function pay(){
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$sid=$_SESSION['WAP']['vip']['sid']<>''?$_SESSION['WAP']['vip']['sid']:$this->diemsg(0, '缺少SID参数');
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		$type=I('type');
		$bkurl=U('Wap/jys/pay',array('sid'=>$sid,'orderid'=>$orderid,'type'=>$type));		
		$backurl=base64_encode($orderdetail);
		$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		
		$mode=I('mode');
		//已登陆
		$m=M('Shop_order');
		$order=$m->where('id='.$orderid)->find();
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
						
						$order['paytype']='money';
						$order['ispay']=1;
						$order['paytime']=time();
						$order['status']=2;
						$rod=$m->save($order);
						if(FALSE !== $rod){
							//扣除用户积分
							if($order['payscore']){
								$sco =$mvip->where('id='.$_SESSION['WAP']['vipid'])->setDec('score',$order['payscore']);
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
							$dlog['msg']='余额-付款成功';
							$dlog['ctime']=time();
							$rlog=$mlog->add($dlog);
							//后端日志
							$mlog=M('Shop_order_syslog');
							$dlog['type']=2;
							$rlog=$mlog->add($dlog);

								$this->success('余额付款成功！',U('Wap/Shop/jysorderList',array('sid'=>$sid)));

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
							$this->error('余额付款失败1！请联系客服！');
						}
					}else{
						//后端日志
						$mlog=M('Shop_order_syslog');
						$dlog['oid']=$order['id'];
						$dlog['msg']='积分扣除失败';
						$dlog['type']=-1;
						$dlog['ctime']=time();
						$this->error('余额支付失败3，请重新尝试！');
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

				$this->redirect(U('/Home/JysWxpay/pay',array('oid'=>$order['oid'])));
			break;
			default:
				$this->error('支付方式未知！');
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
}
