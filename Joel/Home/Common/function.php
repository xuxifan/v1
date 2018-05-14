<?php
function getPicUrl($id) {
	$m = M('Upload_img');
	$map['id'] = $id;
	$list = $m -> where($map) -> find();
	if ($list) {
		$list['imgurl'] = "/upload/" . $list['savepath'] . $list['savename'];
	}
	return $list ? $list['imgurl'] : "";
}

function getweekzh($i) {
	$weeks = array('1' => '星期一', '2' => '星期二', '3' => '星期三', '4' => '星期四', '5' => '星期五', '6' => '星期六', '0' => '星期日', );
	return $weeks[date('w', $i)];
}
//分解数组
function arrayEx($arr,$st,$ind){
	if($arr){
		$res=explode($st,$arr);
		return $res[$ind];
	}else{
		return '0';
	}
}
function getOrderStatus($orderid) {
	$order = M('shop_order') -> where('id=' . $orderid) -> find();
	switch ($order['status']) {
		case '0' :
			return '已取消';
			break;
		case '1' :
			return '待付款';
			break;
		case '2' :
			return '待发货';
			break;
		case '3' :
			return '待收货';
			break;
		case '4' :
			return '退货中';
			break;
		case '5' :
			return '已完成';
			break;
		case '6' :
			return '已关闭';
			break;
		case '7' :
			return '退货完成';
			break;
		default :
			return '已取消';
			break;
	}
}
