<?php
// 本类由系统自动生成，仅供测试用途
namespace Wap\Controller;
use Wap\Controller\BasehkController;
class HkController extends BasehkController {
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
	public function index(){		
		$set=M('Set')->find();
		//追入分享特效
		$options['appid']= $set['wxappid'];
		$options['appsecret']= $set['wxappsecret'];
		$wx = new \Joel\wx\Wechat($options);
		//生成JSSDK实例
		$opt['appid']= $set['wxappid'];
		$opt['token']=$wx->checkAuth();
		$opt['url']="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$jssdk=new \Joel\wx\Jssdk($opt);
		$jsapi=$jssdk->getSignPackage();
		if(!$jsapi){
			die('未正常获取数据！');
		}
		$this->assign('jsapi',$jsapi);
		$this->display();
    }	
	
	public function edit(){		
		$set=M('Set')->find();
		//追入分享特效
		$options['appid']= $set['wxappid'];
		$options['appsecret']= $set['wxappsecret'];
		$wx = new \Joel\wx\Wechat($options);
		//生成JSSDK实例
		$opt['appid']= $set['wxappid'];
		$opt['token']=$wx->checkAuth();
		$opt['url']="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$jssdk=new \Joel\wx\Jssdk($opt);
		$jsapi=$jssdk->getSignPackage();
		if(!$jsapi){
			die('未正常获取数据！');
		}
		$this->assign('jsapi',$jsapi);
		$map['type']='y2111';
		$map['openid']=$_SESSION['sqopenid'];
		$cache=M('Hk_data')->where($map)->find();
		$this->assign('cache',$cache);
		$this->display();
	}
	
	public function save(){
		$set=M('Set')->find();
		//追入分享特效
		$options['appid']= $set['wxappid'];
		$options['appsecret']= $set['wxappsecret'];
		$wx = new \Joel\wx\Wechat($options);
		$data=I('post.');
		if($data['upimg']){
			$token=$wx->checkAuth();
			if($token){
				$dt['imgname']=$this->_geturl($token, $data['upimg']);
				$dt['imgurl']=$imgurl = 'http://'.$_SERVER['HTTP_HOST'].'/Upload/Hk/'.$dt['imgname'].'.jpg';
			}
		}
		$dt['type']=I('type');
		$dt['openid']=I('openid');
		$dt['music']=I('music');
		$dt['message']=I('message');
		$dt['upimg']=I('upimg');
		$m=M('Hk_data');
		$map['type']=$dt['type'];
		$map['openid']=$dt['openid'];
		$old=$m->where($map)->find();
		if($old){
			$re=$m->where($map)->save($dt);
			if(FALSE!==$re){
				$hk=$m->where($map)->find();
				$this->success('保存成功！',U('Wap/Hk/show',array('id'=>$old['id'],'bg'=>$hk['imgname'])));
			}else{
				$this->error('保存失败！请重新尝试！');
			}
		}else{
			$re=$m->where($map)->add($dt);
			if($re){
				$this->success('保存成功！',U('Wap/Hk/show',array('id'=>$re,'bg'=>$dt['imgname'])));
			}else{
				$this->error('保存失败！请重新尝试！');
			}
		}
	}
	
	public function show(){		
		$set=M('Set')->find();
		//追入分享特效
		$options['appid']= $set['wxappid'];
		$options['appsecret']= $set['wxappsecret'];
		$wx = new \Joel\wx\Wechat($options);
		//生成JSSDK实例
		$opt['appid']= $set['wxappid'];
		$opt['token']=$wx->checkAuth();
		$opt['url']="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$jssdk=new \Joel\wx\Jssdk($opt);
		$jsapi=$jssdk->getSignPackage();
		if(!$jsapi){
			die('未正常获取数据！');
		}
		$this->assign('jsapi',$jsapi);
		$id=I('id');
		$cache=M('Hk_data')->where('id='.$id)->find();
		$this->assign('cache',$cache);
		//取新图片名
		$bgimg=I('bg');
		$this->assign('bgimg',$bgimg);
		$this->display();
	}
	
	//获取图片地址 msq
	private function _geturl($token,$imgid){
		//下载图片	
        $url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token.'&media_id='.$imgid;
        $fileInfo = $this -> downloadWeixinFile($url);
        $filename = $imgid.'.jpg';
        $this ->saveWeixinFile($filename, $fileInfo["body"]);
		//图片目录构成
		rename($imgid.'.jpg','Upload/Hk/'.substr($imgid,0,16).'.jpg');
		$imgname=substr($imgid, 0,16);
		//$imgurl = 'http://'.$_SERVER['HTTP_HOST'].'/Upload/Hk/'.substr($imgid,0,16).'.jpg';
		return $imgname ;
	}
	
	//下载图片 msq
	public function downloadWeixinFile($url){
	    $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);    
        curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $package = curl_exec($ch);
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
        $imageAll = array_merge(array('header' => $httpinfo), array('body' => $package)); 
        return $imageAll;
    }
 
 //保存文件 msq
   public function saveWeixinFile($filename, $filecontent){
        $local_file = fopen($filename, 'w');
        if (false !== $local_file){
            if (false !== fwrite($local_file, $filecontent)) {
                fclose($local_file);
            }
        }
    } 
}
