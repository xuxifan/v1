<?php
// 本类由系统自动生成，仅供测试用途
namespace Wap\Controller;
use Wap\Controller\BaseController;
class FxController extends BaseController {
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
	public function index(){
		$data = self::$WAP['vip'];
		$this->assign('data',$data);
		$mvip=M('Vip');
		//$this->getcates();
		
		//首次关注分销佣金
		$mapfirst['pid']=$data['id'];
		$firstsub=$mvip->field('id')->where($mapfirst)->select();
		$subarr="";
		foreach($firstsub as $k=>$v){
			$subarr=$subarr.$v['id'].',';
		}
		$subarr=array_filter(explode(',', $subarr));
		$shopset=M('Shop_set')->find();
		$vipfx1rate=$shopset['vipfx1rate'];
		if(count($subarr) && $vipfx1rate){
			$maporder['ispay']=1;
			$maporder['status']=array('in','2,3');
			$maporder['vipid']=array('in',$subarr);
			$total=M('Shop_order')->where($maporder)->sum('payprice');
			$total=$total?$total:0;
			$fx1total=$total*($vipfx1rate/100);
			$data['fxmoney']=number_format($fx1total,2);
		}else{
			$data['fxmoney']=0.00;
		}		
		
		//待审佣金
		$maptx['vipid']=$data['id'];
		$maptx['status']=1;
		$txtotal=M('Vip_tx')->where($maptx)->sum('txprice');
		if($txtotal>0){
			$data['txmoney']=number_format($txtotal,2);
		}else{
			$data['txmoney']=number_format(0,2);
		}
		$this->assign('data',$data);
		//下线总数
		$vipid=$_SESSION['WAP']['vipid'];
		$count=$mvip->where('pid='.self::$WAP['vipid']." or (path LIKE '%-".$vipid."-%' and plv=".($_SESSION['WAP']['vip']['plv']+2).")")->count();
		$this->assign('count',$count);
		//已关注下线
		$countsub=$mvip->where('pid='.self::$WAP['vipid']." or (path LIKE '%-".$vipid."-%' and plv=".($_SESSION['WAP']['vip']['plv']+2).") and subscribe=1")->count();
		$this->assign('countsub',$countsub);
		//已购买下线
		$d=$mvip->where('pid='.self::$WAP['vipid']." or (path LIKE '%-".$vipid."-%' and plv=".($_SESSION['WAP']['vip']['plv']+2).")")->select();
		$ids='0';
		foreach ($d as $k => $v) {
			$ids=$ids.",".$v['id'];
		}
		$buy=M('shop_order')->where("vipid in (".$ids.")")->group('vipid')->select();
		$this->assign('countbuy',count($buy));
		$this->display();
    }	
	
	public function paihang(){
		$m=M('Vip');
		//$map['isfx']=1;
		//$map['total_xxyj']=array('gt',0);
		$cache=$m->where($map)->limit(50)->order('total_xxlink desc')->select();
		$this->assign('cache',$cache);
		$this->display();
	}
	
	public function myqrcode(){
		$this->display();
	}	
	public function getqrcode(){
		/*$url='http://'.$_SERVER['HTTP_HOST'].'/Wap/Shop/index/ppid/'.self::$WAP['vipid'];
		$QR=new \Joel\QRcode();
		$QR::png($url);*/
		
		$data = self::$WAP['vip'];
		$bj=I('bj');
		$cache=M('represent')->find();
		
		$bjpic=$this->getPic($cache['img']);
		
		$tjimg=$bjpic['imgurl'];
		$name=$data['nickname'];
//		$img=$_SERVER['DOCUMENT_ROOT']."/Public/tmp/tx.jpg";
		$tjimg=$_SERVER['DOCUMENT_ROOT'].$tjimg;
//		$url=$_SESSION['SYS']['set']['wxurl'].'/wap/shop/index';
		$url='http://'.$_SERVER['HTTP_HOST'].'/Wap/Shop/index/ppid/'.self::$WAP['vipid'];
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
		$headimgurl=$data['headimgurl'];
//		if(!file_exists($txath)){
			$this->download_remote_file($headimgurl,$txath);
//		}
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
	
	public function myuser(){
		$m=M('vip');	
		$type=intval(I('type'))?intval(I('type')):1;
		$vipid=$_SESSION['WAP']['vipid'];
		if($type==1){
			$this->assign('type','一');
			$cache=$m->where("path LIKE '%-".$vipid."' and plv=".($_SESSION['WAP']['vip']['plv']+1))->order('ctime desc')->limit(50)->select();
			$total=$m->where("path LIKE '%-".$vipid."' and plv=".($_SESSION['WAP']['vip']['plv']+1))->count();
		}
		
		if($type==2){
			$this->assign('type','二');
			$cache=$m->where("path LIKE '%-".$vipid."-%' and plv=".($_SESSION['WAP']['vip']['plv']+2))->order('ctime desc')->limit(50)->select();
			$total=$m->where("path LIKE '%-".$vipid."-%' and plv=".($_SESSION['WAP']['vip']['plv']+2))->count();
		}
		//三级有问题
		if($type==3){
			$this->assign('type','三');
			$arr=array();
			$tmp=$m->field('id')->where(array('pid'=>$vipid))->select();
			foreach($tmp as $v){
				array_push($arr,$v['id']);
			}
			$tmp2=$m->field('id')->where(array('pid'=>array('in',$arr)))->select();
			$arr2=array();
			foreach($tmp2 as $v){
				array_push($arr2,$v['id']);	
			}
			$cache=$m->where(array('pid'=>array('in',$arr2)))->order('ctime desc')->limit(50)->select();
			$total=$m->where(array('pid'=>array('in',$arr2)))->count();
		}
		$this->assign('total',$total);
		$this->assign('cache',$cache);
		$this->display();
	}
	
	public function dslog(){
		$m=M('fx_dslog');
		$map['to']=$_SESSION['WAP']['vipid'];
		$map['status']=1;
		$cache=$m->where($map)->limit(50)->order('ctime desc')->select();
		$this->assign('cache',$cache);
		$this->display();
	}
	
	public function fxlog(){
		$m=M('fx_syslog');
		$map['to']=$_SESSION['WAP']['vipid'];
		$map['status']=1;
		$cache=$m->where($map)->limit(50)->order('ctime desc')->select();
		$this->assign('cache',$cache);
		$this->display();
	}
	
	public function tjlog(){
		$m=M('fx_log_tj');
		$map['vipid']=$_SESSION['WAP']['vipid'];
		$cache=$m->where($map)->limit(50)->order('ctime desc')->select();
		$this->assign('cache',$cache);
		$this->display();
	}
	
	public function about(){
		$this->display();
	}
	
	//下载网络图片
	function download_remote_file($file_url, $save_to){
		$content = file_get_contents($file_url);
		file_put_contents($save_to, $content);
	}
}
