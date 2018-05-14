<?php
// +----------------------------------------------------------------------
// | 分销后台基础类--S分组PUBLIC公共类
// +----------------------------------------------------------------------
// | JoelCMS V1.0 Beta
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.JoelCMS.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Joel <2094157689@qq.com>
// +----------------------------------------------------------------------
namespace S\Controller;
use S\Controller\BaseController;
class PublicController extends BaseController {
	
	//默认跳转至登陆页面
	public function index(){
		$this->redirect(U('/S/Public/login'));
	}	
	
	//通用注册页面
	public function reg(){

        if(IS_POST){

            $data=I('post.');
            if($data['token']!=$_SESSION['S']['hpftoken']){
                $this->error("非法表单！");
            }
            $verify = new \Think\Verify();

            $user=M('Fxs_user')->where(array('username'=>$data['username']))->find();

            if($user){
                $this->error('改用户名已被注册，请重新选择');
            }else{
                $data1['username'] = $data['username'];
                $data1['userpass'] = md5($data['password']);
                $data1['nickname'] = $data['nickname'];
                $newuser = M('Fxs_user')->data($data1)->add();
                if($newuser){
                   $userinfo =  M('Fxs_user')->where(["id"=>$newuser])->find();
                    self::$S['uid']=$_SESSION['S']['uid']=$newuser;
                    self::$S['user']=$_SESSION['S']['user']=$userinfo;
                    self::$S['homeurl']=$_SESSION['S']['homeurl']=U('/S/Index/index');
                    self::$S['backurl']=$_SESSION['S']['backurl']=FALSE;

                }else{
                    $this->error("注册失败，请联系管理员");
                }


            }
        }else{
            $token = rand(100000,999999);
            $_SESSION['S']['hpftoken'] = $token;
            $this->assign("_token",$token);
        }
        if($_SESSION['S']['uid']){
            $this->redirect(U('/S/Index/index'));
        }
		$this->display();
	}

	public function checkusername()
    {
        $reg = array();
        if(IS_AJAX){
            $data = I("post.");
            $user=M('Fxs_user')->where(array('username'=>$data['username']))->find();

            if($user){
                $reg['msg'] = '该用户名已被注册，请重新选择';
            }else{
                $reg['msg'] = "ok";
            }
           echo json_encode($reg);

        }
    }
	
	//通用登陆页面
    public function login(){
    	if(IS_POST){
    		$data=I('post.');
			$verify = new \Think\Verify();
    		if(!$verify->check($data['verify'])){
    			$this->error('请正确填写验证码！');
    		}
			$user=M('Fxs_user')->where(array('username'=>$data['username'],'userpass'=>md5($data['userpass'])))->find();
    		
    		if($user){
    			if(!$user['status']){
    				$this->diemsg(0,'您的服务已停用，请联系您的上级客服！');
    			}    			    			
    			self::$S['uid']=$_SESSION['S']['uid']=$user['id'];
				self::$S['user']=$_SESSION['S']['user']=$user;
				self::$S['homeurl']=$_SESSION['S']['homeurl']=U('/S/Index/index');
				self::$S['backurl']=$_SESSION['S']['backurl']=FALSE;
    			$this->redirect(U('/S/Index/index'));
    		}else{
    			$this->error('用户不存在，或密码错误！');
    		}
		}
		if($_SESSION['S']['uid']){
			$this->redirect(U('/S/Index/index'));
		}
    	$this->display();
	}
	
	public function logout(){
		session(null);
		$this->redirect(U('/S/Public/login'));			
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