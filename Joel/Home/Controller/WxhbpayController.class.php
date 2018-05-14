<?php
// 微信支付JSAPI版本 
// 基于版本 V3
// By Joel 2015-1-20
namespace Home\Controller;
use Think\Controller;
class WxhbpayController extends Controller {
	//Joel全局相关
	public static $_url;//动态刷新
	public static $_opt;//参数缓存	
	public static $_logs='';//log地址
	//JOELCMS设置缓存
	protected static $SET;
	//微信缓存
	protected static $_wx;
	protected static $_wxappid;
	protected static $_wxappsecret;
	
	public function __construct(){
		//Joel自定义全局		
		parent::__construct();
		header("Content-type: text/html; charset=utf-8");
		//刷新全局地址
		self::$_url="http://".$_SERVER['HTTP_HOST'];
		//获取全局配置
		self::$SET=M('Set')->find();
		if(!self::$SET){
			die('系统未配置！');
		}
		//全局缓存微信
		self::$_wxappid=self::$SET['wxappid'];
		self::$_wxappsecret=self::$SET['wxappsecret'];
		$options['appid']= self::$_wxappid;
		$options['appsecret']= self::$_wxappsecret;
		self::$_wx = new \Joel\wx\Wechat($options);
	}
	//支付宝业务逻辑 By Joel.
	public function index(){
	
		echo "This is a Wxpay Class written by Joel!";
    
	}//index类结束
	
	public function info(){
		
	}
	//支付出口
	//Joel 2015.1.20
	//无返回值，接受订单参数并转向到支付宝支付接口
	public function pay(){
		//$opt=I('get.');
		self::$_opt['openid']=$openid='o-_G9uJW7DYQ5EdeuYthtpqolFt4';//$_SESSION['wxpayopenid'];
//		$sid=$_SESSION['WAP']['vip']['sid'];
/*		if($sid==''){
			$this->diemsg(0, '为获取SID!请重新尝试或联系客服！');
		}*/		
		if(!$openid){
			$this->diemsg(0,'未获取会员数据，请重新尝试！');
		}
		
		//微信支付封装
		$options['appid']= self::$_wxappid;
		$options['mchid']= self::$SET['wxmchid'];
		$options['mchkey']= self::$SET['wxmchkey'];
		$wxHongBaoHelper= new \Joel\wx\Wxhbsdk($options);
		//自定义订单号，此处仅作举例
		$mch_billno =$options['mchid'].date('YmdHis').rand(1000, 9999);
        $wxHongBaoHelper->setParameter("mch_billno", $mch_billno);//订单号
        $wxHongBaoHelper->setParameter("nick_name", '红包');//提供方名称
        $wxHongBaoHelper->setParameter("send_name", '红包');//红包发送者名称
        $wxHongBaoHelper->setParameter("re_openid", $openid);//相对于医脉互通的openid
        $wxHongBaoHelper->setParameter("total_amount", 100);//付款金额，单位分
        $wxHongBaoHelper->setParameter("min_value", 100);//最小红包金额，单位分
        $wxHongBaoHelper->setParameter("max_value", 100);//最大红包金额，单位分
        $wxHongBaoHelper->setParameter("total_num", 1);//红包収放总人数
        $wxHongBaoHelper->setParameter("wishing", '恭喜发财');//红包祝福诧
//        $wxHongBaoHelper->setParameter("client_ip", '127.0.0.1');//调用接口的机器 Ip 地址
        $wxHongBaoHelper->setParameter("act_name", '红包活动');//活劢名称
        $wxHongBaoHelper->setParameter("remark", '快来抢！');//备注信息
//		dump($wxHongBaoHelper);die;
        $postXml = $wxHongBaoHelper->createXml();
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $responseXml = $wxHongBaoHelper->postXmlSSLCurl($postXml,$url);
		$responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);//var_dump($responseObj->return_msg);die;
		$Code =$responseObj->return_code;	
		if($Code=='SUCCESS'){
			file_put_contents(self::$_logs.'Joel_wxhbpayok.txt','支付成功:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$responseObj->return_msg.PHP_EOL.'订单号:'.$openid.'->'.$mch_billno.PHP_EOL.'红包结果:发送成功'.PHP_EOL.PHP_EOL,FILE_APPEND);
		}else{
			file_put_contents(self::$_logs.'Joel_wxhbpayerr.txt','发送失败:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$responseObj->return_msg.PHP_EOL.'订单号:'.$openid.'->'.$mch_billno.PHP_EOL.'红包结果:发送失败'.PHP_EOL.PHP_EOL,FILE_APPEND);
		}			
	}
	
	/*public function cz(){
		self::$_opt['oid']=$oid=$_GET['oid'];
		self::$_opt['payprice']=$payprice =$_GET['price'];
		self::$_opt['openid']=$openid=$_SESSION['wxpayopenid'];
		$sid=$_SESSION['WAP']['vip']['sid'];
		if($sid==''){
			$this->diemsg(0, '为获取SID!请重新尝试或联系客服！');
		}
		if(!$oid){
			$this->diemsg(0,'订单参数不完整！请重新尝试！');
		}		
		if(!$openid){
			$this->diemsg(0,'未获取会员数据，请重新尝试！');
		}
		
	
		//微信支付封装
		$options['appid']= self::$_wxappid;
		$options['appsecret']= self::$_wxappsecret;
		$options['mchid']= self::$SET['wxmchid'];
		$options['mchkey']= self::$SET['wxmchkey'];
		$paysdk= new \Joel\wx\Wxpaysdk($options);
		
		$paysdk->setParameter("openid",$openid);//会员openid
		$paysdk->setParameter("body","支付商品订单");//商品描述
		//自定义订单号，此处仅作举例
		$timeStamp = time();
		$paysdk->setParameter("out_trade_no",$oid);//商户订单号 
		$paysdk->setParameter("total_fee",intval($payprice*100));//总金额单位为分，不允许有小数
		$paysdk->setParameter("notify_url",self::$_url.'/Wxpay/cznd/');//交易通知地址 
		$paysdk->setParameter("trade_type","JSAPI");//交易类型
		$prepayid=$paysdk->getPrepayId();
		//dump($options);
		//die($prepayid);
		if($prepayid){
			$paysdk->setPrepayId($prepayid);
		}else{
			$this->diemsg(0,'未成功生成支付订单，请重新尝试！'.$payprice);
		}
		
		//获取前端PAYAPI
		$wOpt['appId'] = self::$_wxappid;
		$timeStamp = time();
		$wOpt['timeStamp'] = "$timeStamp";
		$wOpt['nonceStr'] = $this->createNoncestr(8);
		$wOpt['package'] = 'prepay_id='.$prepayid;
		//$wOpt['package'] = $prepayid;
		$wOpt['signType'] = 'MD5';
		ksort($wOpt, SORT_STRING);
		foreach($wOpt as $key => $v) {
			$string .= "{$key}={$v}&";
		}
		$string = $string."key=".self::$SET['wxmchkey'];
		$wOpt['paySign'] = strtoupper(md5($string));
		$wOpt['package'] = $prepayid;
		foreach($wOpt as $key => $v) {
			$str .= "{$key}={$v}&";
		}
		$url=self::$_url."/czwxpay.php?".$str;
//		$_SESSION['tmpwxpay']=$wOpt;
		header("Location:".$url); 
		//获取JSAPI
		//生成JSSDK实例
//		$opt['appid']= self::$_wxappid;
//		$opt['token']=self::$_wx->checkAuth();
//		$opt['url']="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//		$jssdk=new \Joel\wx\Jssdk($opt);
//		$jsapi=$jssdk->getSignPackage();
//		if(!$jsapi){
//			die('未正常获取数据！');
//		}
//		$this->assign('jsapi',$jsapi);
		
		//$this->display();		
	}

	public function createNoncestr( $length = 32 ){
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		}  
		return $str;
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
	
	//用户中断支付的跳转地址
	public function paycancel(){
		$sid=$_SESSION['WAP']['vip']['sid'];
		if($sid==''){
			$this->diemsg(0, '为获取SID!请重新尝试或联系客服！');
		}
		if($sid){
			$url=self::$_url.'/Wap/Fxshop/orderList/sid/'.$sid;
		}else{
			$url=self::$_url.'/Wap/Shop/orderList/sid/'.$sid;
		}
		$url=self::$_url.'/Wap/Shop/orderList/sid/'.$_SESSION['wxpaysid'];
		header('Location:'.$url);//取消支付并跳转回商城
	}
	//当支付成功后的返回控制器
	public function payback(){
		//$status=I('status');
		$sta='0';
		$msg='';
		//dump($_GET);
		$verify_result = $this->verifyReturn();
		if($verify_result) {
			//验证成功
			$out_trade_no = $_GET['out_trade_no'];//支付宝交易号
			$trade_no = $_GET['trade_no'];//支付宝交易号
			$result = $_GET['result'];//交易状态
			if($result == 'success') {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
					$sta='1';
					$msg='支付成功!';	
					//修改订单状态
					$this -> endpay($out_trade_no);
					$url=self::$_url.'/Wap/Shop/orderList/sid/'.self::$_opt['sid'];
					header('Location:'.$url);
		    }
		    else {
		      echo "支付失败";//这里永远不会调用
			  $url=self::$_url.'/Wap/Shop/orderList/sid/'.self::$_opt['sid'];
			  header('Location:'.$url);
		    }
				
			//echo "验证成功<br />";
			
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
			//$this->info($sta,$msg,$uid);
			
			//echo '支付状态：';
			//dump($_GET);
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
		    //验证失败
		    //如要调试，请看alipay_notify.php页面的verifyReturn函数
		    //echo "验证失败";
		    die('验证失败');
		}
		//$this->display();
	}
	
	//支付成功后后台接受方案
	public function nd(){
			foreach($_POST as $k=>$v){
				$str=$str.$k."=>".$v.'  ';
			}
			file_put_contents(self::$_logs.'Joel_wxpaynd.txt','响应参数:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息111:'.$str.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
		//使用通用通知接口
		$mchkey= self::$SET['wxmchkey'];
		$notify = new \Joel\wx\Wxpayndsdk($mchkey);
		//存储微信的回调
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	
		$notify->saveData($xml);
		//验证签名，并回应微信。
		//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		//尽可能提高通知的成功率，但微信不保证通知最终能成功。
		if($notify->checkSign() == FALSE){
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}else{
			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
		}
		$returnXml = $notify->returnXml();
		echo $returnXml;
	
		//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
			
		if($notify->checkSign() == TRUE)
		{
			//获取订单号
			$out_trade_no=$notify->data["out_trade_no"];
			
			if ($notify->data["return_code"] == "FAIL") {
				//此处应该更新一下订单状态，商户自行增删操作
				//$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
				file_put_contents(self::$_logs.'Joel_wxpayerr.txt','通讯出错:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单号:'.$out_trade_no.PHP_EOL.'交易结果:通讯出错'.PHP_EOL.PHP_EOL,FILE_APPEND);
			}
			elseif($notify->data["result_code"] == "FAIL"){
				//此处应该更新一下订单状态，商户自行增删操作
				//$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
				file_put_contents(self::$_logs.'Joel_wxpayerr.txt','业务出错:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单号:'.$out_trade_no.PHP_EOL.'交易结果:业务出错'.PHP_EOL.PHP_EOL,FILE_APPEND);
			}
			else{
				//此处应该更新一下订单状态，商户自行增删操作
				//$log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
				$this -> endpay($out_trade_no);
				file_put_contents(self::$_logs.'Joel_wxpayok.txt','支付成功:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单号:'.$out_trade_no.PHP_EOL.'交易结果:交易成功'.PHP_EOL.PHP_EOL,FILE_APPEND);
				
			}
			
			//商户自行增加处理流程,
			//例如：更新订单状态
			//例如：数据库操作
			//例如：推送支付完成信息
		}
	}
	
	//支付成功后后台接受方案
	public function cznd(){
			foreach($_POST as $k=>$v){
				$str=$str.$k."=>".$v.'  ';
			}
			file_put_contents(self::$_logs.'Joel_wxpay_err.txt','响应参数:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
		//使用通用通知接口
		$mchkey= self::$SET['wxmchkey'];
		$notify = new \Joel\wx\Wxpayndsdk($mchkey);
		//存储微信的回调
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	
		$notify->saveData($xml);
		//验证签名，并回应微信。
		//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		//尽可能提高通知的成功率，但微信不保证通知最终能成功。
		if($notify->checkSign() == FALSE){
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}else{
			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
		}
		$returnXml = $notify->returnXml();
		echo $returnXml;
	
		//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
			
		if($notify->checkSign() == TRUE)
		{
			//获取订单号
			$out_trade_no=$notify->data["out_trade_no"];
			
			if ($notify->data["return_code"] == "FAIL") {
				//此处应该更新一下订单状态，商户自行增删操作
				//$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
				file_put_contents(self::$_logs.'Joel_wxpayerr.txt','通讯出错:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单号:'.$out_trade_no.PHP_EOL.'交易结果:通讯出错'.PHP_EOL.PHP_EOL,FILE_APPEND);
			}
			elseif($notify->data["result_code"] == "FAIL"){
				//此处应该更新一下订单状态，商户自行增删操作
				//$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
				file_put_contents(self::$_logs.'Joel_wxpayerr.txt','业务出错:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单号:'.$out_trade_no.PHP_EOL.'交易结果:业务出错'.PHP_EOL.PHP_EOL,FILE_APPEND);
			}
			else{
				//此处应该更新一下订单状态，商户自行增删操作
				//$log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
				$this -> endcz($out_trade_no);
				file_put_contents(self::$_logs.'Joel_wxpayok.txt','支付成功:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单号:'.$out_trade_no.PHP_EOL.'交易结果:交易成功'.PHP_EOL.PHP_EOL,FILE_APPEND);
				
			}
			
			//商户自行增加处理流程,
			//例如：更新订单状态
			//例如：数据库操作
			//例如：推送支付完成信息
		}
	}
	
	//充值成功后操作
	public function endcz($oid){
		$m = M('vip_log');
		$order = $m -> where(array('opid'=>$oid)) -> find();
		if ($order) {
			if($order['status'] == 1){
				//修改状态
				$order['status'] = 2;
				$order['ctime'] = time();
				$re=$m -> save($order);
				if(FALSE !== $re){
					//修改会员账户金额、经验、积分、等级
					$zsmoney=$this->getZsmoney($order['money']);//充值活动赠送
					$addmoney=$order['money']+$zsmoney;
					$data_vip['id']=$order['vipid'];
					$data_vip['money']=array('exp','money+'.$addmoney);
					$data_vip['score']=array('exp','score+'.$order['score']);
					if ($order['exp']>0) {
						$vip=M('vip')->where('id='.$order['vipid'])->find();
						$vipset=M('vip_set')->find();
						$data_vip['exp']=array('exp','exp+'.$order['exp']);
						$data_vip['cur_exp']=array('exp','cur_exp+'.$order['exp']);
						$level=$this->getLevel($vip['cur_exp']+$order['exp']);
						$data_vip['levelid']=$level['levelid'];
					}
					if(FALSE===M('vip')->save($data_vip)) {
						//记录报警信息
						$str="订单号：".$oid."充值成功但更新会员信息失败！";
						file_put_contents(self::$_logs.'Joel_error.txt','微信支付报警:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.'交易类型:TRADE_SUCCESS'.PHP_EOL.PHP_EOL,FILE_APPEND);
					}
				}else{
					//记录报警信息
					$str="订单号：".$oid."充值成功但未更新日志信息！";
					file_put_contents(self::$_logs.'Joel_error.txt','微信支付报警:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.'交易类型:TRADE_SUCCESS'.PHP_EOL.PHP_EOL,FILE_APPEND);
				}
			}
		}
	}
	
	
	
	//支付成功后后台接受方案
	public function nderr(){
			foreach($_POST as $k=>$v){
				$str=$str.$k."=>".$v.'  ';
			}
			file_put_contents(self::$_logs.'Joel_wxpay_err.txt','响应参数:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
		
	}

	//付款成功后操作
	public function endpay($oid,$buyer_email){
		$m = M('Shop_order');
		$order = $m -> where(array('oid'=>$oid)) -> find();
		if ($order) {
			if($order['status'] == 1){
					//修改状态
					$order['ispay'] = 1;
					$order['status'] = 2;
					$order['paytime'] = time();
					//$order['aliaccount'] = $buyer_email;
					$re=$m -> save($order);
					if(FALSE !== $re){
						//销量计算-只减不增
						$rsell=$this->doSells($order);
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
						
						//支付成功后设置为花蜜
						$mvip=M('Vip');
						$vip=$mvip->where('id='.$order['vipid'])->find();
						if($vip && !$vip['isfx']){
							//会员购满多少钱变成分销商
							$shopset=M('Shop_set')->find();
							if($shopset['vipfxneed']<=$vip['total_buy']){
								$rvip=$mvip->where('id='.$order['vipid'])->setField('isfx',1);
								$data_msg['pids']=$order['vipid'];
								$data_msg['title']="您成功升级为".$shopset['name']."的会员！";
								$data_msg['content']="欢迎成为".$shopset['name']."的会员，请查看会员中心的团队中心！";
								$data_msg['ctime']=time();
								$rmsg=M('vip_message')->add($data_msg);
							}
							
						}
						
						//代收花生米计算-只减不增
						$rds=$this->doDs($order);
						
						//==通知该会员的上级==即将得到的佣金=========================================================
						$items=unserialize($order['items']);
						$itemsname='';
						foreach($items as $k=>$v){
							$itemsname.=$v['name'].',';
						}
						$ds=M('fx_dslog')->where(array('oid'=>$order['id']))->find();		//查找将获得的佣金
						$vipuser=M('vip')->where(array('id'=>$ds['to']))->find(); //查找他的上级获取上级的openid
						$SET=M('Set')->find();
						$tp=new \bb\template();
						$array=array(
							'url'=>$SET['wxurl'].U('wap/fx/dslog'),
							'name'=>$ds['toname'],		//上级名字
							'fromname'=>$ds['fromname'],		//下级级名字
							'ordername'=>rtrim($itemsname,','),
							'money'=>$ds['fxprice'],		//商品价格
							'yj'=>$ds['fxyj']			//分销的佣金
						);
						$openid=$vipuser['openid'];		//发给的人
						$templatedata=$tp->enddata('collection',$openid,$array);	//组合模板数据
						self::$_wx->sendTemplateMessage($templatedata);	//发送模板
						//=============================================================
						
					}else{
						//记录报警信息
						$str="订单号：".$oid."支付成功但未更新订单状态！"."买家帐号：".$buyer_email;
						file_put_contents(self::$_logs.'Joel_error.txt','微信支付报警:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.'交易类型:TRADE_SUCCESS'.PHP_EOL.PHP_EOL,FILE_APPEND);
					}
					//发送模板===发送支付成功模办消息给会员===============================
					$items=unserialize($order['items']);
					$itemsname='';
					foreach($items as $k=>$v){
						$itemsname.=$v['name'].',';
					}
					if($order['sid']){
						$url=self::$SET['wxurl'].U('wap/shop/orderDetail',array('orderid'=>$order['id']));
					}else{
						$url=self::$SET['wxurl'].U('wap/fxshop/orderDetail',array('orderid'=>$order['id']));
					}
					$tp=new \bb\template();
					$array=array(
						'url'=>$url,
						'name'=>$order['vipname'],
						'ordername'=>rtrim($itemsname,','),
						'orderid'=>$order['oid'],
						'money'=>$order['payprice'],
						'date'=>date("Y-m-d H:i:s",time())
					);
					$templatedata=$tp->enddata('orderok',$order['vipopenid'],$array);	//组合模板数据
					$rtn = self::$_wx->sendTemplateMessage($templatedata);	//发送模板
					//======================================================================
					
					
					//发送已付款订单模板消息给商家
					//$this -> sendMobanMsmToShop($order['id'],1);
					//发送支付成功模办消息给会员
					//$this -> sendMobanMsmToVip($order['id']);
			}
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
	
	//根据充值金额计算赠送金额
	public function getZsmoney($money) {
		$vipset=M('vip_set')->find();
		$cz_rule=explode(",",$vipset['cz_rule']);
		$zsmoney=0;
		foreach ($cz_rule as $k=>$v) {
			$cz_rule[$k]=explode(":",$v);
		}
		foreach ($cz_rule as $k=>$v) {
			if ($k+1==count($cz_rule)) {
				if ($money>=$cz_rule[$k][0]) {
					$zsmoney=intval($cz_rule[$k][1]);
				}
			} else {
				if ($money>=$cz_rule[$k][0] && $money<$cz_rule[$k+1][0]) {
					$zsmoney=intval($cz_rule[$k][1]);
				}
			}
		}
		return $zsmoney;
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
	
	//根据当前经验计算等级信息
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
	
	//发送商家模板消息
	//type=0:订单新生成（未付款）
	//type=1:订单已付款
	function sendMobanMsmToShop($oid,$type,$flag=FALSE) {
		//构造消息体
		$order = M('wds_order')->where('id='.$oid)->find();
		$shop = M('shop')->where('id='.$order['sid'])->find();
		$ppid = $order['ppid'];
		if ((($type==0 && $shop['newmsg']==1) || ($type==1 && $shop['paymsg']==1)) && $shop['kfids']!='') {
			if ($order['mobile']=='') {
				$addressinfo = M('vip_address')->where('ppid='.$ppid)->order('isdefault asc')->select();
				if ($addressinfo) {
					$customerInfo = $addressinfo[0]['name'].' '.$addressinfo[0]['mobile'];
				} else {
					$customerInfo = '暂未登记';
				}
			} else {
				$customerInfo = $order['name'].' '.$order['mobile'];
			}

			if ($type==0) {
				$first = "新订单通知（暂未付款）";
			} else {
				$first = $flag?"订单通知（货到付款）":"订单通知（已付款）";
			}
			$first = $first."\\n订单编号：".$order['oid'];
			
			$arr = explode('|',$order['goods']);
			$money=0;
			$remark="\\n";
			foreach($arr as $k=>$val){
				$a = explode(',',$val);
				$money = $money + $a['5'] * $a['3'];
				$remark = $remark.$a['1']."：".$a['3'].$a['4']."\\n";
			}
		
			$template = array (
				'touser' => "",
				'template_id' => "Gk4Olsma5qlneSsdAWK3J9w1t7eRxVkNRGFcxiHfk6g",
				'url' => "",
				'topcolor' => "#70c02f",
				'data' => array (
					//标题
					'first' => array (
						'value' => urlencode($first),
						'color' => "#FF0000",
					),
					//提交时间
					'tradeDateTime' => array (
						'value' => urlencode(date('Y-m-d H:i:s',time())),
						//'color' => "#0000FF",
					),
					//订单类型
					'orderType' => array (
						'value' => urlencode($shop['name']),
						//'color' => "#0000FF",
					),
					//顾客信息
					'customerInfo' => array (
						'value' => urlencode($customerInfo),
						//'color' => "#0000FF",
					),
					//商品名称
					'orderItemName' => array (
						'value' => urlencode("订单总价"),
						//'color' => "#0000FF",
					),
					//商品规格及数量
					'orderItemData' => array (
						'value' => urlencode($order['money'].'元'),
						'color' => "#006cff",
					),
					//备注
					'remark' => array (
						'value' => urlencode($remark),
						'color' => "#565656",
					),
				)
			);
			//发送消息
			$options['appid']= "wxbe940343e78068cf";
			$options['appsecret']= "c0fd637146298928406365ec3a08ce27";
			$mx = new \Zw\Wx\Wechat($options);
			
			$shop['kfids'] = $shop['kfids']==''?'10':$shop['kfids'].',10';//发送给指定客服id：10
			
			$kfidArr = explode(",",$shop['kfids']);
			foreach($kfidArr as $k=>$val){
				$openid = M('vip')->where('id='.$val)->getField('openid');
				$template['touser'] = $openid;
				$rtn = $mx -> sendMobanMessage($template);
				file_put_contents('./logs/message/msg.txt',PHP_EOL.'发送商家模板消息成功:'.date('Y-m-d H:i:s').$rtn,FILE_APPEND);
			}
		}
	}

	//发送会员模板消息
	function sendMobanMsmToVip($oid) {
		//构造消息体
		$order = M('wds_order')->where('id='.$oid)->find();
		$shop = M('shop')->where('id='.$order['sid'])->find();
		$ppid = $order['ppid'];
		
		$openid = M('vip')->where('id='.$ppid)->getField('openid');
		
		$arr = explode('|',$order['goods']);
		$money=0;
		$goodsinfo=$shop['name']."\\n\\n";
		foreach($arr as $k=>$val){
			$a = explode(',',$val);
			$money = $money + $a['5'] * $a['3'];
			$goodsinfo = $goodsinfo.$a['1']."：".$a['3'].$a['4']."\\n";
		}
	
		$template = array (
			'touser' => $openid,
			'template_id' => "5qJqoRxrgO8W9amLF6aejYMag4mAxSUh3OpMrgnJ2cw",
			'url' => "http://www.zwwsq.com/wap/wds/orderdetail/id/".$oid,
			'topcolor' => "#70c02f",
			'data' => array (
				//标题
				'first' => array (
					'value' => urlencode(date('Y-m-d H:i:s',time())."\\n您的订单已完成付款"),
					'color' => "#FF0000",
				),
				//订单金额
				'orderProductPrice' => array (
					'value' => urlencode($order['money']."元"),
					'color' => "#006cff",
				),
				//商品详情
				'orderProductName' => array (
					'value' => urlencode($goodsinfo),
					'color' => "#565656",
				),
				//收货信息
				'orderAddress' => array (
					'value' => urlencode($order['address']."  ".$order['name']),
					//'color' => "#0000FF",
				),
				//订单编号
				'orderName' => array (
					'value' => urlencode($order['oid']."\\n验证码：".$order['pin']),
					//'color' => "#0000FF",
				),
				//备注
				'remark' => array (
					'value' => urlencode(""),
					//'color' => "#006cff",
				),
			)
		);
		//发送消息
		$options['appid']= "wxbe940343e78068cf";
		$options['appsecret']= "c0fd637146298928406365ec3a08ce27";
		$mx = new \Zw\Wx\Wechat($options);
		$rtn = $mx -> sendMobanMessage($template);
		file_put_contents('./logs/message/msg.txt',PHP_EOL.'发送会员模板消息成功:'.date('Y-m-d H:i:s').$rtn,FILE_APPEND);
	}*/

	
}//Wxhbpay类结束