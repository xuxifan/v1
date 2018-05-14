<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--CMS分组PUBLIC公共类
// +----------------------------------------------------------------------
// | JoelCMS V1.0 Beta
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.JoelCMS.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Joel <2094157689@qq.com>
// +----------------------------------------------------------------------
namespace Cms\Controller;
use Cms\Controller\BaseController;
class PublicController extends BaseController {
	
	//默认跳转至登陆页面
	public function index(){
		$this->redirect(U('/Cms/Public/login'));
	}	
	
	//通用注册页面
	public function reg(){
        if(IS_POST){

            $data=I('post.');
            if($data['token']!=$_SESSION['Cms']['hpftoken']){
                $this->error("非法表单！");
            }
            $verify = new \Think\Verify();


            $user=M('User')->where(array('username'=>$data['username']))->find();

            if($user){
                $this->error('改用户名已被注册，1请重新选择!');
            }else{
                $data1['username'] = $data['username'];
                $data1['userpass'] = md5($data['password']);
                $data1['nickname'] = $data['nickname'];
                $data1['oath'] = "shop,";
                $newuser = M('User')->data($data1)->add();
                if($newuser){
                    $userinfo =  M('User')->where(["id"=>$newuser])->find();
                    self::$CMS['uid']=$_SESSION['CMS']['uid']=$newuser;
                    self::$CMS['user']=$_SESSION['CMS']['user']=$userinfo;
                    self::$CMS['homeurl']=$_SESSION['CMS']['homeurl']=U('/Cms/Index/index');
                    self::$CMS['backurl']=$_SESSION['CMS']['backurl']=FALSE;
                    //追入操作员日志
                    $adlog['uid']=$_SESSION['CMS']['uid'];
                    $adlog['admin']=$_SESSION['CMS']['user']['username'];
                    $adlog['ip']=get_client_ip();
                    $adlog['ctime']=time();
                    $adlog['event']='登入';
                    $radlog=M('Adminlog_login')->add($adlog);
                    $this->redirect(U('/Cms/Index/index'));

                }else{
                    $this->error("注册失败，请联系管理员!");
                }


            }
        }else{
            $token = rand(100000,999999);
            $_SESSION['Cms']['hpftoken'] = $token;
            $this->assign("_token",$token);
        }
        if($_SESSION['CMS']['uid']){
            $this->redirect(U('/Cms/Index/index'));
        }
        $this->display();

	}


	
	//通用登陆页面
    public function login(){
    	if(IS_POST){
    		$data=I('post.');
			$verify = new \Think\Verify();
    		if(!$verify->check($data['verify'])){
    			$this->error('请正确填写验证码！',u('public/login'));
    		}
			$user=M('User')->where(array('username'=>$data['username'],'userpass'=>md5($data['userpass'])))->find();
    		if($user){    			    			
    			self::$CMS['uid']=$_SESSION['CMS']['uid']=$user['id'];
				self::$CMS['user']=$_SESSION['CMS']['user']=$user;
				self::$CMS['homeurl']=$_SESSION['CMS']['homeurl']=U('/Cms/Index/index');
				self::$CMS['backurl']=$_SESSION['CMS']['backurl']=FALSE;
				//追入操作员日志
				$adlog['uid']=$_SESSION['CMS']['uid'];
				$adlog['admin']=$_SESSION['CMS']['user']['username'];
				$adlog['ip']=get_client_ip();
				$adlog['ctime']=time();
				$adlog['event']='登入';
				$radlog=M('Adminlog_login')->add($adlog);
    			$this->redirect(U('/Cms/Index/index'));
    		}else{
    			$this->error('用户不存在，或密码错误！');
    		}
		}
		if($_SESSION['CMS']['uid']){
			$this->redirect(U('/Cms/Index/index'));
		}
    	$this->display();    
	}

    public function checkusername()
    {
        $reg = array();
        if(IS_AJAX){
            $data = I("post.");
            $user=M('User')->where(array('username'=>$data['username']))->find();

            if($user){
                $reg['msg'] = '该用户名已被注册，请重新选择!';
            }else{
                $reg['msg'] = "ok";
            }
            echo json_encode($reg);

        }
    }


	
	public function logout(){
		//追入操作员日志
		$adlog['uid']=$_SESSION['CMS']['uid'];
		$adlog['admin']=$_SESSION['CMS']['user']['username'];
		$adlog['ip']=get_client_ip();
		$adlog['ctime']=time();
		$adlog['event']='登出';
		$radlog=M('Adminlog_login')->add($adlog);
		session(null);
		$this->redirect(U('/Cms/Public/login'));			
	}
	
	//通用验证码
	public function verify(){
    	$Verify = new \Think\Verify();
		$Verify->codeSet = '0123456789'; 
		$Verify->length   = 4;
		$Verify->imageH   = 0;
		$Verify->entry();
 	}
	
	//百度地图
	public function baiduDitu(){
		$map['address']=I('address');
		$map['lng']=I('lng');
		$map['lat']=I('lat');
		$this->assign('map',$map);
		$mb=$this->fetch();
		$this->ajaxReturn($mb);
	}
	
	
}