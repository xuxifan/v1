<?php
// 支付宝（及时收款接口）改写 
// 基于版本 V4.3
// By Joel 2014-6-10
namespace Home\Controller;
use Think\Controller;
class AlipayController extends Controller {
	//Joel全局相关
	public static $_url;	
	public static $_logs='./logs/alipay/';//log地址	
	public static $_opt;//参数缓存
	public static $_set;//全局配置参数
	
	
	//支付宝全局相关	
	var $alipay_config;//支付宝基本配置
	var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';//支付宝网关
	var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';//HTTPS形式消息验证地址
	var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';//HTTP形式消息验证地址
	public function __construct($options){
		
		//Joel自定义全局		
		
		parent::__construct();
		header("Content-type: text/html; charset=utf-8");
		//Joel处理多用户代码
		self::$_set=M('Set')->find();
		//Joel配置支付宝基本参数
		//合作身份者id，以2088开头的16位纯数字
		$this->alipay_config['partner']		= self::$_set['tbpartner'];
		//安全检验码，以数字和字母组成的32位字符
		$this->alipay_config['key']			= self::$_set['tbkey'];
		//签名方式 不需修改
		$this->alipay_config['sign_type']    = strtoupper('MD5');
		//字符编码格式 目前支持 gbk 或 utf-8
		$this->alipay_config['input_charset']= strtolower('utf-8');
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$this->alipay_config['cacert']    = getcwd().'\\alipaycacert.pem';
		
		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$this->alipay_config['transport']    = 'http';
		
		self::$_url="http://".$_SERVER['HTTP_HOST'];
	}
	//支付宝业务逻辑 By Joel.
	public function index(){
	
		echo "haha";
    
	}//index类结束
	
	//支付出口
	//Joel 2014.6.9
	//无返回值，接受订单参数并转向到支付宝支付接口
	public function pay(){
		$opt=I('get.');
	
		self::$_opt['oid']=$oid=$_GET['oid'];
		self::$_opt['price']=$price=$_GET['price'];
		
		if($oid=='' || $price==''){
			$msg='订单参数不完整！';
			die($msg);
		}
		$this->orderCheck($oid);
		
		$_SESSION['alipaysid']=$sid;//将支付宝跳转SID缓存到跳转
		/************************************************************/
		//支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url = self::$_url."/Home/Alipay/nd/";
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        //页面跳转同步通知页面路径
        $return_url = self::$_url."/Home/Alipay/payback/";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
        //卖家支付宝帐户
        $seller_email = self::$_set['tbemail'];//$_POST['WIDseller_email'];
        //必填
        //商户订单号
        $out_trade_no = $oid;//$_POST['WIDout_trade_no'];
        //商户网站订单系统中唯一订单号，必填
        //订单名称
        $subject = $oid;//$_POST['WIDsubject'];
        //必填
        //付款金额
        $total_fee = $price;//$_POST['WIDtotal_fee'];
        //必填
        //订单描述
        $body = $oid;//$_POST['WIDbody'];
        //商品展示地址//订单中心
        $show_url = self::$_url.'/Home/Vip/order/';//$_POST['WIDshow_url'];
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数
        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1
        /************************************************************/
        //构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "create_direct_pay_by_user",
				"partner" => trim($this->alipay_config['partner']),
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"seller_email"	=> $seller_email,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"body"	=> $body,
				"show_url"	=> $show_url,
				"anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//dump($this->alipay_config);
		//dump($parameter);
		//die('a');
		/************************************************************/
		//建立请求
		//$alipaySubmit =$this->AlipaySubmit($this->$alipay_config);
		echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
		$html_text = $this->buildRequestForm($parameter,"get", "确认");
		//dump($html_text);
		
		echo $html_text;
		//die('aa');
		
	}

	
	
	//当支付成功后的返回控制器
	public function payback(){
		$verify_result = $this->verifyReturn();
		if($verify_result) {
			$sta='0';
			$msg='';	
			//验证成功
			$out_trade_no = $_GET['out_trade_no'];//支付宝交易号
			$trade_no = $_GET['trade_no'];//支付宝交易号
			$trade_status = $_GET['trade_status'];//交易状态
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
					$sta='1';
					$msg='支付成功!';
					$url=self::$_url.'/Home/Vip/order/';
					header('Location:'.$url);
		    }
		    else {
		      echo "trade_status=".$_GET['trade_status'];
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
		    die('验证失败');
		    //echo "验证失败";
		}
		//$this->display();
	}
	
	//支付成功后后台接受方案
	public function nd(){
			foreach($_POST as $k=>$v){
				$str=$str.$k."=>".$v.'  ';
			}
			$verify_result = $this->verifyNotify();			
			if($verify_result) {
			//验证成功
			$out_trade_no = $_POST['out_trade_no'];//支付宝交易号
			$trade_no = $_POST['trade_no'];//交易编号		
			$trade_status = $_POST['trade_status'];//交易状态
			$buyer_email = $_POST['buyer_email'];
			//测试模式下打印响应信息
		
		    if($_POST['trade_status'] == 'TRADE_FINISHED') {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
						
				//注意：
				//该种交易状态只在两种情况下出现
				//1、开通了普通即时到账，买家付款成功后。
				//2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
		
		        //调试用，写文本函数记录程序运行情况是否正常
		        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		        // 交易处理成功--此处可以对交易做分润等处理
		        
				$this -> endpay($out_trade_no,$buyer_email);
				file_put_contents(self::$_logs.'Joel_nd.txt','支付成功:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单状态:'.$order.PHP_EOL.'交易类型:TRADE_FINISHED'.PHP_EOL.PHP_EOL,FILE_APPEND);
		    }
		    else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
						
				//注意：
				//该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
		
		        //调试用，写文本函数记录程序运行情况是否正常
		        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		        // 交易处理成功--此处可以对交易做分润等处理
				$this -> endpay($out_trade_no,$buyer_email);
				file_put_contents(self::$_logs.'Joel_nd.txt','支付成功:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单状态:'.$order.PHP_EOL.'交易类型:TRADE_SUCCESS'.PHP_EOL.PHP_EOL,FILE_APPEND);
		    }
		
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		        
			echo "success";		//请不要修改或删除
			
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
		    //验证失败
		    echo "fail";
			file_put_contents(self::$_logs.'Joel_nd.txt','签名验证失败:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
		    //调试用，写文本函数记录程序运行情况是否正常
		    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
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
					$order['aliaccount'] = $buyer_email;
					$order['paytype'] = 'alipay';
					$order['paytime'] = time();
					$re=$m -> save($order);
					if(FALSE !== $re){
						//记录日志
						$mlog=M('Shop_order_log');
						$dlog['oid']=$order['id'];
						$dlog['msg']='支付宝-付款成功';
						$dlog['ctime']=time();
						$mlog->add($dlog);						
						
					}else{
						//记录报警信息
						$str="订单号：".$oid."支付成功但未更新订单状态！"."买家帐号：".$buyer_email;
						file_put_contents(self::$_logs.'Joel_error.txt','支付宝移动支付报警:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.'交易类型:TRADE_SUCCESS'.PHP_EOL.PHP_EOL,FILE_APPEND);
					}
					//发送已付款订单模板消息给商家
					//$this -> sendMobanMsmToShop($order['id'],1);
					//发送支付成功莫办消息给会员
					//$this -> sendMobanMsmToVip($order['id']);
			}
		}
	}
	
	
	//判断是否为特价商品
	function checkTJgoods($id) {
		$shopset=M('Shop_set')->where('id=1')->find();
		$tjgoods=M('shop_group')->where('id='.$shopset['indexgroup_1'])->getField('goods');
		$tjgoodsArr=explode(',', $tjgoods);
		return in_array($id,$tjgoodsArr);
	}
	
	public function cz(){
		$opt=I('get.');
	
		self::$_opt['oid']=$oid=$_GET['oid'];
		self::$_opt['price']=$price=$_GET['price'];
		
		if($oid=='' || $price==''){
			$msg='订单参数不完整！';
			die($msg);
		}
		$this->czorderCheck($oid);
		
		$_SESSION['alipaysid']=$sid;//将支付宝跳转SID缓存到跳转
		/************************************************************/
		//支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url = self::$_url."/Home/Alipay/cznd/";
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        //页面跳转同步通知页面路径
        $return_url = self::$_url."/Home/Alipay/czback/";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
        //卖家支付宝帐户
        $seller_email = 'zhaobaobang@163.com';//$_POST['WIDseller_email'];
        //必填
        //商户订单号
        $out_trade_no = $oid;//$_POST['WIDout_trade_no'];
        //商户网站订单系统中唯一订单号，必填
        //订单名称
        $subject = $oid;//$_POST['WIDsubject'];
        //必填
        //付款金额
        $total_fee = $price;//$_POST['WIDtotal_fee'];
        //必填
        //订单描述
        $body = $oid;//$_POST['WIDbody'];
        //商品展示地址//订单中心
        $show_url = self::$_url.'/Www/vip/cz';//$_POST['WIDshow_url'];
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数
        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1
        /************************************************************/
        //构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "create_direct_pay_by_user",
				"partner" => trim($this->alipay_config['partner']),
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"seller_email"	=> $seller_email,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"body"	=> $body,
				"show_url"	=> $show_url,
				"anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//dump($this->alipay_config);
		//dump($parameter);
		//die('a');
		/************************************************************/
		//建立请求
		//$alipaySubmit =$this->AlipaySubmit($this->$alipay_config);
		echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
		$html_text = $this->buildRequestForm($parameter,"get", "确认");
		//dump($html_text);
		
		echo $html_text;
		//die('aa');
		
	}

	//当支付成功后的返回控制器
	public function czback(){
		$verify_result = $this->verifyReturn();
		if($verify_result) {
			$sta='0';
			$msg='';	
			//验证成功
			$out_trade_no = $_GET['out_trade_no'];//支付宝交易号
			$trade_no = $_GET['trade_no'];//支付宝交易号
			$trade_status = $_GET['trade_status'];//交易状态
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
					$sta='1';
					$msg='支付成功!';
					$url=self::$_url.'/Www/vip/cz';
					header('Location:'.$url);
		    }
		    else {
		      echo "trade_status=".$_GET['trade_status'];
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
		    die('验证失败');
		    //echo "验证失败";
		}
		//$this->display();
	}
	
	//支付成功后后台接受方案
	public function cznd(){
		foreach($_POST as $k=>$v){
			$str=$str.$k."=>".$v.'  ';
		}
		$verify_result = $this->verifyNotify();		
		if($verify_result) {
			//验证成功
			$out_trade_no = $_POST['out_trade_no'];//支付宝交易号
			$trade_no = $_POST['trade_no'];//交易编号		
			$trade_status = $_POST['trade_status'];//交易状态
			$buyer_email = $_POST['buyer_email'];
			//测试模式下打印响应信息
			
		    if($_POST['trade_status'] == 'TRADE_FINISHED') {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
						
				//注意：
				//该种交易状态只在两种情况下出现
				//1、开通了普通即时到账，买家付款成功后。
				//2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
		
		        //调试用，写文本函数记录程序运行情况是否正常
		        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		        // 交易处理成功--此处可以对交易做分润等处理
		        
				$this -> endcz($out_trade_no,$buyer_email);
				file_put_contents(self::$_logs.'Joel_nd.txt','支付成功:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单状态:'.$order.PHP_EOL.'交易类型:TRADE_FINISHED'.PHP_EOL.PHP_EOL,FILE_APPEND);
		    }
		    else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
						
				//注意：
				//该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
		
		        //调试用，写文本函数记录程序运行情况是否正常
		        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		        // 交易处理成功--此处可以对交易做分润等处理
				$this -> endcz($out_trade_no,$buyer_email);
				file_put_contents(self::$_logs.'Joel_nd.txt','支付成功:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.'订单状态:'.$order.PHP_EOL.'交易类型:TRADE_SUCCESS'.PHP_EOL.PHP_EOL,FILE_APPEND);
		    }
		
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		        
			echo "success";		//请不要修改或删除
			
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
		    //验证失败
		    echo "fail";
			file_put_contents(self::$_logs.'Joel_nd.txt','签名验证失败:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
		    //调试用，写文本函数记录程序运行情况是否正常
		    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}

	//充值成功后操作
	public function endcz($oid,$buyer_email){
		$m = M('vip_log');
		$order = $m -> where(array('opid'=>$oid,'type'=>7)) -> find();
		if ($order) {
			if($order['status'] == 1){
				//修改状态
				$order['status'] = 2;
				$order['mobile'] = $buyer_email; //此处保存付款账号
				$order['ctime'] = time();
				$order['code'] = $this->getZsmoney($order['money']);//此处保存为赠送的金额
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
						file_put_contents(self::$_logs.'Joel_error.txt','支付宝移动支付报警:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.'交易类型:TRADE_SUCCESS'.PHP_EOL.PHP_EOL,FILE_APPEND);
					}
				}else{
					//记录报警信息
					$str="订单号：".$oid."充值成功但未更新日志信息！";
					file_put_contents(self::$_logs.'Joel_error.txt','支付宝移动支付报警:'.date('Y-m-d H:i:s').PHP_EOL.'通知信息:'.$str.PHP_EOL.PHP_EOL.'交易类型:TRADE_SUCCESS'.PHP_EOL.PHP_EOL,FILE_APPEND);
				}
			}
		}
	}

	public function orderCheck($oid){
		$m=M('Shop_order');
		$order=$m->where(array('oid'=>$oid))->find();
		if ($order) {
			if ($order['status'] != 1) {
				die("该订单不能支付！");
			}
		} else {
			die("该订单不存在！");
		}
	}
	
	public function czorderCheck($oid){
		$m=M('vip_log');
		$order=$m->where(array('opid'=>$oid,'type'=>7))->find();
		if ($order) {
			if ($order['status'] != 1) {
				die("该订单不能支付！");
			}
		} else {
			die("该订单不存在！");
		}
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

	//通知模板
	public function info($status,$msg){
		$status=$status?$status:'0';
		$msg=$msg?$msg:'参数不正确!';
		//dump($msg);
		$this->assign('status',$status);
		$this->assign('msg',$msg);
		$this->display('info');
	}

	//支付宝业务逻辑结束
	/////////////////////////////////////////////////////////
	//支付宝接口提交请求类 by Joel
	//基于Alipay_submit
	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	function buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkstring($para_sort);
		
		$mysign = "";
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$mysign = $this->md5Sign($prestr, $this->alipay_config['key']);
				break;
			default :
				$mysign = "";
		}
		
		return $mysign;
	}

	/**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
	function buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);

		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
		
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));
		
		return $para_sort;
	}

	/**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
	function buildRequestParaToString($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		$request_data = $this->createLinkstringUrlencode($para);
		
		return $request_data;
	}
	
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
	function buildRequestForm($para_temp, $method, $button_name) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower($this->alipay_config['input_charset']))."' method='".$method."'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

		//submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
		
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		
		return $sHtml;
	}
	
	/**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果
     * @param $para_temp 请求参数数组
     * @return 支付宝处理结果
     */
	function buildRequestHttp($para_temp) {
		$sResult = '';
		
		//待请求参数数组字符串
		$request_data = $this->buildRequestPara($para_temp);

		//远程获取数据
		$sResult = $this->getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$request_data,trim(strtolower($this->alipay_config['input_charset'])));

		return $sResult;
	}
	
	/**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果，带文件上传功能
     * @param $para_temp 请求参数数组
     * @param $file_para_name 文件类型的参数名
     * @param $file_name 文件完整绝对路径
     * @return 支付宝返回处理结果
     */
	function buildRequestHttpInFile($para_temp, $file_para_name, $file_name) {
		
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		$para[$file_para_name] = "@".$file_name;
		
		//远程获取数据
		$sResult = $this->getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$para,trim(strtolower($this->alipay_config['input_charset'])));

		return $sResult;
	}
	
	/**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串
	 */
	function query_timestamp() {
		$url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim(strtolower($this->alipay_config['partner']))."&_input_charset=".trim(strtolower($this->alipay_config['input_charset']));
		$encrypt_key = "";		

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
		
		return $encrypt_key;
	}
	
	/////////////////////////////////////////////////////////
	//支付宝通知接口方法 by Joel
	//基于Alipay_nodify
	/**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
	function verifyNotify(){
		if(empty($_POST)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
			
			//写日志记录
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_POST);
			//logResult($log_text);
			
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
	function verifyReturn(){
		if(empty($_GET)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($_GET["notify_id"])) {$responseTxt = $this->getResponse($_GET["notify_id"]);}
			
			//写日志记录
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n return_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_GET);
			//logResult($log_text);
			
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
	function getSignVeryfy($para_temp, $sign) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);
		
		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);
		
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkstring($para_sort);
		
		$isSgin = false;
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$isSgin = $this->md5Verify($prestr, $sign, $this->alipay_config['key']);
				break;
			default :
				$isSgin = false;
		}
		
		return $isSgin;
	}

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
	function getResponse($notify_id) {
		$transport = strtolower(trim($this->alipay_config['transport']));
		$partner = trim($this->alipay_config['partner']);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = $this->getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);
		
		return $responseTxt;
	}
	
	/////////////////////////////////////////////////////////
	
	//支付宝MD5通用方法接口修改，By Joel
	//基于Alipay_md5
	/**
	 * 签名字符串
	 * @param $prestr 需要签名的字符串
	 * @param $key 私钥
	 * return 签名结果
	 */
	function md5Sign($prestr, $key) {
		$prestr = $prestr . $key;
		return md5($prestr);
	}
	
	/**
	 * 验证签名
	 * @param $prestr 需要签名的字符串
	 * @param $sign 签名结果
	 * @param $key 私钥
	 * return 签名结果
	 */
	function md5Verify($prestr, $sign, $key) {
		$prestr = $prestr . $key;
		$mysgin = md5($prestr);
	
		if($mysgin == $sign) {
			return true;
		}
		else {
			return false;
		}
	}
	
	///////////////////////////////////////////////////
		
	//支付宝支付系统核心方法修改，By Joel
	//基于Alipay_core
	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	function createLinkstring($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".$val."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,count($arg)-2);
		
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		
		return $arg;
	}
	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	function createLinkstringUrlencode($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".urlencode($val)."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,count($arg)-2);
		
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		
		return $arg;
	}
	/**
	 * 除去数组中的空值和签名参数
	 * @param $para 签名参数组
	 * return 去掉空值与签名参数后的新签名参数组
	 */
	function paraFilter($para) {
		$para_filter = array();
		while (list ($key, $val) = each ($para)) {
			if($key == "sign" || $key == "sign_type" || $val == "")continue;
			else	$para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	/**
	 * 对数组排序
	 * @param $para 排序前的数组
	 * return 排序后的数组
	 */
	function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
	/**
	 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
	 * 注意：服务器需要开通fopen配置
	 * @param $word 要写入日志里的文本内容 默认值：空值
	 */
	function logResult($word='') {
		$fp = fopen("log.txt","a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	
	/**
	 * 远程获取数据，POST模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param $url 指定URL完整路径地址
	 * @param $cacert_url 指定当前工作目录绝对路径
	 * @param $para 请求的数据
	 * @param $input_charset 编码格式。默认值：空值
	 * return 远程输出的数据
	 */
	function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '') {
	
		if (trim($input_charset) != '') {
			$url = $url."_input_charset=".$input_charset;
		}
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
		curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
		
		return $responseText;
	}
	
	/**
	 * 远程获取数据，GET模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param $url 指定URL完整路径地址
	 * @param $cacert_url 指定当前工作目录绝对路径
	 * return 远程输出的数据
	 */
	function getHttpResponseGET($url,$cacert_url) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
		curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
		
		return $responseText;
	}
	
	/**
	 * 实现多种字符编码方式
	 * @param $input 需要编码的字符串
	 * @param $_output_charset 输出的编码格式
	 * @param $_input_charset 输入的编码格式
	 * return 编码后的字符串
	 */
	function charsetEncode($input,$_output_charset ,$_input_charset) {
		$output = "";
		if(!isset($_output_charset) )$_output_charset  = $_input_charset;
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset change.");
		return $output;
	}
	/**
	 * 实现多种字符解码方式
	 * @param $input 需要解码的字符串
	 * @param $_output_charset 输出的解码格式
	 * @param $_input_charset 输入的解码格式
	 * return 解码后的字符串
	 */
	function charsetDecode($input,$_input_charset ,$_output_charset) {
		$output = "";
		if(!isset($_input_charset) )$_input_charset  = $_input_charset ;
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset changes.");
		return $output;
	}
	

}//Alipay类结束