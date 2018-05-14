<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<!--Head-->
<head>
    <meta charset="utf-8" />
    <title>..::<?php echo ($_SESSION["CMS"]["set"]["name"]); ?>::..</title>

    <meta name="description" content="login page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="/Public/Cms/img/favicon.png" type="image/x-icon">

    <!--Basic Styles-->
    <link href="/Public/Cms/css/bootstrap.min.css" rel="stylesheet" />
    <link id="bootstrap-rtl-link" href="" rel="stylesheet" />
    <link href="/Public/Cms/css/font-awesome.min.css" rel="stylesheet" />

    <!--Fonts-->
    <link href="http://fonts.useso.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,700,300" rel="stylesheet" type="text/css">

    <!--Beyond styles-->
    <link id="beyond-link" href="/Public/Cms/css/beyond.min.css" rel="stylesheet" />
    <link href="/Public/Cms/css/demo.min.css" rel="stylesheet" />
     <link href="/Public/Cms/css/typicons.min.css" rel="stylesheet" />
    <link href="/Public/Cms/css/animate.min.css" rel="stylesheet" />
    <link id="skin-link" href="" rel="stylesheet" type="text/css" />

    <!--Skin Script: Place this script in head to load scripts for skins and rtl support-->
    <script src="/Public/Cms/js/skins.min.js"></script>
</head>
<!--Head Ends-->
<!--Body-->
<body>
    <div class="login-container animated fadeInDown">
        <div class="loginbox bg-white" style="height: 460px!important;">
            <div class="loginbox-title">登陆</div>
            <div class="loginbox-social">
                <div class="social-title ">品牌商城系统后台</div>
            </div>
            <div class="loginbox-or">
                <div class="or-line"></div>
                <div class="or">Login</div>
            </div>
            <form id="loginForm" action="<?php echo U('Cms/Public/login');?>"  method="post">
            <div class="loginbox-textbox">
                <input id="username" name="username" type="text" class="form-control" placeholder="用户名"/>
            </div>
            <div class="loginbox-textbox">
                <input id="userpass" name="userpass" type="password"  class="form-control" placeholder="密码" />
            </div>
            <div class="loginbox-textbox">
                <input id="verify" type="text" name="verify" class="form-control" placeholder="验证码" />              
            </div>
            <div class="loginbox-textbox">
            	<img src="./index.php?s=/Cms/Public/verify/" width="100%" id="verifyimg" />
            </div>
            <div class="loginbox-forgot">
                <!--<a href="">忘记密码?</a>-->
                 <a href="http://m.kuaidi100.com" target="_blank">快递查询</a>
            </div>
            <div class="loginbox-submit">
                <input class="btn btn-primary btn-block" value="登陆" type="submit">
            </div>
            </form>
            <div class="loginbox-signup">
                <a href="/Cms/Public/reg/">注册帐号！</a>
            </div>
        </div>
        <div class="logobox">
        </div>
       
    </div>
    <!--Basic Scripts-->
    <script src="/Public/Cms/js/jquery-2.0.3.min.js"></script>
    <script src="/Public/Cms/js/bootstrap.min.js"></script>
    <!--Beyond Scripts-->
    <script src="/Public/Cms/js/beyond.min.js"></script>
    <!--Page Related Scripts-->
    <script src="/Public/Cms/js/toastr/toastr.js"></script>
	<script>
		$('#loginForm').on('submit',function(){
			var username=$('#username');
			var userpass=$('#userpass');
			var verify=$('#verify');
			if(!$(username).val()){
				Notify('用户名不能为空!', 'top-right', '5000', 'danger', 'fa-bolt', true); 
				$(username).focus();
				return false;
			}
			if(!$(userpass).val()){
				Notify('用户密码不能为空!', 'top-right', '5000', 'danger', 'fa-bolt', true); 
				$(userpass).focus();
				return false;
			}
			if(!$(verify).val()){
				Notify('验证码不能为空!', 'top-right', '5000', 'danger', 'fa-bolt', true); 
				$(verify).focus();
				return false;
			}			
		});
		$('#verifyimg').on('click',function(){
			$(this).attr('src','./index.php?s=/Cms/Public/verify/');
		});
	</script>
</body>
<!--Body Ends-->
</html>