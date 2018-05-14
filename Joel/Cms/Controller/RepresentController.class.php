<?php
namespace Cms\Controller;
use Cms\Controller\BaseController;
class RepresentController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
	//代言设置
	public function dyset(){
		$id=I('id');
		$m=M('represent');
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'系统设置',
				'url'=>U('Cms/User/#')
			),
			'1'=>array(
				'name'=>'我要代言',
				'url'=>U('Cms/Represent/dyset')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			if($id){
				$re=$m->save($data);
				if(FALSE!==$re){
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				$re=$m->add($data);
				if($re){
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}
			$this->ajaxReturn($info);
		}
		//处理编辑界面
		$cache=$m->find();
		$this->assign('cache',$cache);
		$this->display();
	}
	
	
	public function tgimg(){
		$bj=I('bj');
		$cache=M('represent')->find();
		
		$bjpic=$this->getPic($cache['img']);
		
		$tjimg=$bjpic['imgurl'];
		$name="昵称";
		$img=$_SERVER['DOCUMENT_ROOT']."/Public/Wap/img/tx.jpg";
		$tjimg=$_SERVER['DOCUMENT_ROOT'].$tjimg;
		$url=$_SESSION['SYS']['set']['wxurl'].'/wap/shop/index';
		
		$QR=new \Joel\QRcode();
		$mapath=$_SERVER['DOCUMENT_ROOT'].'/QRcode/ma_0.png';
		$QR::png($url,$mapath);
		
		//=====================================
		$width = 340;
		$height = 340;
		list($width_orig, $height_orig) = getimagesize($mapath);
		if ($width && ($width_orig < $height_orig)) {
		    $width = ($height / $height_orig) * $width_orig;
		} else {
		    $height = ($width / $width_orig) * $height_orig;
		}
		$ma = imagecreatetruecolor($width, $height);
		$image = imagecreatefrompng($mapath);
		imagecopyresampled($ma, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		//=============================
		$path=$_SERVER['DOCUMENT_ROOT'].'/Public/tmp/';
		//=============================
		//创建图片的实例
		$background = imagecreatefromstring(file_get_contents($tjimg));
	  	$q_width  = imagesx($ma);
	    $q_height = imagesy($ma);
		
		$fonttype = $path."jht.ttf"; //字体
		$fontcolor = imagecolorallocate($background, 96, 72, 62);
    		imagettftext($background, 28, 0, $cache['uleft'], $cache['utop'], $fontcolor, $fonttype, $name);
		imagecopymerge($background, $ma, $cache['mleft'], $cache['mtop'], 0, 0, $q_width, $q_height, 80);
		//==================================================
		$txath=$img;
		if(!file_exists($txath)){
			$this->download_remote_file($headimgurl,$txath);
		}
		$width = 100;
		$height = 100;
		list($width_orig, $height_orig) = getimagesize($txath);
		if ($width && ($width_orig < $height_orig)) {
		    $width = ($height / $height_orig) * $width_orig;
		} else {
		    $height = ($width / $width_orig) * $height_orig;
		}
		$tx = imagecreatetruecolor($width, $height);
		$image = imagecreatefromjpeg($txath);
		imagecopyresampled($tx, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		$t_width  = imagesx($tx);
	    $t_height = imagesy($tx);
		//====================================================
		imagecopymerge($background, $tx, $cache['uimgleft'], $cache['uimgtop'], 0, 0, $t_width, $t_height, 90);
		header('Content-type: image/jpeg');

        imagejpeg($background);
		
		//销毁内存图片
		imagedestroy($ma);
		imagedestroy($image);
		imagedestroy($tx);
		imagedestroy($background);
		
		
	}
}