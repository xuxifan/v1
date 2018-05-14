<?php
/**
 * Joel专属微信支付类
 */

namespace Joel\wx;
use Think\Controller;
class Wxhbsdk extends Controller
{
	const UFORDER_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';//获取预支付URL,prepayid.
	private $mchid;//微信支付商户号	
	private $mchkey;//微信支付商户KEY
	private $appid;
//	private $nonce_str;//随机字符串，丌长于 32 位
	private $mch_billno;//订单号
	private $nick_name;//提供方名称
	private $send_name;//红包发送者名称
	private $re_openid;//相对于医脉互通的openid
	private $total_amount;//付款金额，单位分
	private $min_value;//最小红包金额，单位分
	private $max_value;//最大红包金额，单位分
	private $total_num;//红包収放总人数
	private $wishing;//红包祝福诧
//	private $client_ip;//调用接口的机器 Ip 地址
	private $act_name;//活劢名称
	private $remark;//备注信息
	//动态参数
	private $parameters;
	//非必填参数，商户可根据实际情况选填
	//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
	//$unifiedOrder->setParameter("device_info","XXXX");//设备号 
	//$unifiedOrder->setParameter("attach","XXXX");//附加数据 
	//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
	//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
	//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
	//$unifiedOrder->setParameter("openid","XXXX");//用户标识
	//$unifiedOrder->setParameter("product_id","XXXX");//商品ID
	
	//微信CURL响应
	public $response;//微信返回的响应
	public $result;//返回参数，类型为关联数组
	
	//
	private $prepay_id;//获取prepay_id

	public function __construct($options)
	{			
		$this->appid = isset($options['appid'])?$options['appid']:'';
		$this->mchid = isset($options['mchid'])?$options['mchid']:'';
		$this->mchkey = isset($options['mchkey'])?$options['mchkey']:'';//var_dump($this->mchid);die;	
//		$this->nonce_str = isset($options['nonce_str'])?$options['nonce_str']:'';
		$this->mch_billno = isset($options['mch_billno'])?$options['mch_billno']:'';		
		$this->nick_name = isset($options['nick_name'])?$options['nick_name']:'';
		$this->send_name = isset($options['send_name'])?$options['send_name']:'';
		$this->re_openid = isset($options['re_openid'])?$options['re_openid']:'';
		$this->total_amount = isset($options['total_amount'])?$options['total_amount']:'';
		$this->min_value = isset($options['min_value'])?$options['min_value']:'';
		$this->max_value = isset($options['max_value'])?$options['max_value']:'';
		$this->total_num = isset($options['total_num'])?$options['total_num']:'';		
		$this->wishing = isset($options['wishing'])?$options['wishing']:'';
//		$this->client_ip = isset($options['client_ip'])?$options['client_ip']:'';
		$this->act_name = isset($options['act_name'])?$options['act_name']:'';
		$this->remark = isset($options['remark'])?$options['remark']:'';		
	}
	
	
	/**
	 * 获取prepay_id
	 */
	function getCode()
	{	return 1;
		$postXml = $this->createXml();
        $responseXml = $this->postXmlSSLCurl(UFORDER_URL, $postXml);
		$responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $responseObj;//->return_code;	
	}
	
	/**
	 * 	作用：设置JSAPI_prepay_id
	 */
	
	function setPrepayId($prepayId)
	{
		$this->prepay_id = $prepayId;
	}
	
	/**
	 * 	作用：设置请求参数
	 */
	function setParameter($parameter, $parameterValue)
	{
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	
	
	/**
	 * 	作用：设置标配的请求参数，生成签名，生成接口参数xml
	 */
	function createXml()
	{	
		try {
			$this->parameters["wxappid"] = $this->appid;//公众账号ID
			$this->parameters["mch_id"] = $this->mchid;//商户号
			$this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
			$this->parameters["client_ip"] = '182.254.153.77';//$_SERVER['REMOTE_ADDR'];//终端ip	
			$this->parameters["sign"] = $this->getSign($this->parameters);//签名
			return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}	
	}
	/**
	 * 	作用：post请求xml
	 */
	function postXml($url)
	{
	    $xml = $this->createXml();
		$this->response = $this->postXmlCurl($xml,$url,$this->curl_timeout);
		return $this->response;
	}
	
		
	//常用工具
	function trimString($value)
	{
		$ret = null;
		if (null != $value) 
		{
			$ret = $value;
			if (strlen($ret) == 0) 
			{
				$ret = null;
			}
		}
		return $ret;
	}
	
	/**
	 * 	作用：产生随机字符串，不长于32位
	 */
	public function createNoncestr( $length = 32 ) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		}  
		return $str;
	}
	
	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode)
	{
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v)
		{
		    if($urlencode)
		    {
			   $v = urlencode($v);
			}
			//$buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen($buff) > 0) 
		{
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
	
	/**
	 * 	作用：生成签名
	 */
	public function getSign($Parameters)
	{		
		try {
			foreach ($Parameters as $k => $v)
			{
				$Parameters[$k] = $v;
			}
/*			if (null == PARTNERKEY || "" == PARTNERKEY ) {
				throw new SDKRuntimeException("密钥不能为空！" . "<br>");
			}
*/			
			//签名步骤一：按字典序排序参数
			ksort($Parameters);
			$String = $this->formatBizQueryParaMap($Parameters, false);
			//echo '【string1】'.$String.'</br>';
			//签名步骤二：在string后加入KEY
			$String = $String."&key=".$this->mchkey;
			//echo "【string2】".$String."</br>";
			//签名步骤三：MD5加密
			$String = md5($String);
			//echo "【string3】 ".$String."</br>";
			//签名步骤四：所有字符转为大写
			$result_ = strtoupper($String);
			//echo "【result】 ".$result_."</br>";
			return $result_;
		}catch (SDKRuntimeException $e){
			die($e->errorMessage());
		}
	}
	
	/**
	 * 	作用：array转xml
	 */
	function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
	
	/**
	 * 	作用：将xml转为array
	 */
	public function xmlToArray($xml)
	{		
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $array_data;
	}

	/**
	 * 	作用：以post方式提交xml到对应的接口url
	 */
	public function postXmlCurl($xml,$url,$second=30)
	{		
        //初始化curl        
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOP_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
        $data = curl_exec($ch);
		curl_close($ch);
		//返回结果
		if($data)
		{
			curl_close($ch);
			return $data;
		}
		else 
		{ 
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>"; 
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}
	
	/**
	 * POST 请求
	 * @param string $url
	 * @param array $param
	 * @param boolean $post_file 是否文件上传
	 * @return string content
	 */
	private function http_post($url,$xml,$post_file=false){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		if (is_string($param) || $post_file) {
			$strPOST = $param;
		} else {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}

	/**
	 * 	作用：使用证书，以post方式提交xml到对应的接口url
	 */
	function postXmlSSLCurl($xml,$url,$second=30,$aHeader=array())
	{  //return dirname(__FILE__).DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'apiclient_cert.pem';
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		//这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch,CURLOPT_HEADER,FALSE);
		// 只信任CA颁布的证书
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
		// 检查证书中是否设置域名，并且是否与提供的主机名匹配
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		//设置证书
		//使用证书：cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT, dirname(__FILE__).DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'apiclient_cert.pem');
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY, dirname(__FILE__).DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'apiclient_key.pem');
		curl_setopt($ch,CURLOPT_CAINFO,dirname(__FILE__).DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'rootca.pem');
		
		if( count($aHeader) >= 1 ){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}
		
		//post提交方式
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		}
		else { 
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>"; 
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}
	
}
