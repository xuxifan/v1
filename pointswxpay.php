<?php
$wOpt=$_GET;
$wOpt['package'] = 'prepay_id='.$wOpt['package'];
$wOpt['host']=$_SERVER['HTTP_HOST'];
//var_dump("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
?>
<meta charset="UTF-8">
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
			alert('您已成功付款！');
				window.location.href='http://'+'<?php echo $wOpt['host'];?>'+'/Wap/points/orderList/type/2/';
		} else if(res.err_msg=='get_brand_wcpay_request:cancel'){
				window.location.href='http://'+'<?php echo $wOpt['host'];?>'+'/Wap/points/orderList/type/1/';
		}else{
			alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
				window.location.href='http://'+'<?php echo $wOpt['host'];?>'+'/Wap/points/orderList/type/1/';
		}
	});
}, false);
</script>