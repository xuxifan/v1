<?php
$wOpt=$_GET;
$wOpt['package'] = 'prepay_id='.$wOpt['package'];
  header("Content-Type:text/html;charset=utf-8");
//var_dump("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
?>
<script type="text/javascript">
document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
	WeixinJSBridge.invoke('getBrandWCPayRequest', {
		'appId' : '<?php echo $wOpt['appId'];?>',
		'timeStamp': '<?php echo $wOpt['timeStamp'];?>',
		'nonceStr' : '<?php echo $wOpt['nonceStr'];?>',
		'package' : '<?php echo $wOpt['package'];?>',
		'signType' : '<?php echo $wOpt['signType'];?>',
		'paySign' : '<?php echo $wOpt['paySign'];?>'
	}, function(res) {
		if(res.err_msg == 'get_brand_wcpay_request:ok') {
			alert("您已成功成功充值！ ");
			window.location.href='<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/Wap/Shop/index/';?>';
		} else {
			WeixinJSBridge.call('closeWindow');
			//alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
			window.location.href='<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/Wap/vip/index/';?>';
		}
	});
}, false);
</script>