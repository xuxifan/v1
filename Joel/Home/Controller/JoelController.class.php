<?php
//Joel测试类
namespace Home\Controller;
use Think\Controller;
class JoelController extends Controller {
    public function index(){
    	
		$this->display();   
	}
	
	//获取二维码
	public function getQR(){
		$set=M('Set')->find();
		//缓存微信API模型类
		$options['appid']= $set['wxappid'];
		$options['appsecret']= $set['wxappsecret'];
		$wx=new \Joel\wx\Wechat($options);
		$re=$wx->getQRCode(1,1);
		dump($re);
		$rs=$wx->getQRUrl($re['ticket']);
		dump($rs);
		echo 'ok';
	}
}