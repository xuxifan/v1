<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<!--Head-->
<head>
    <meta charset="utf-8" />
    <title>用户注册</title>

    <meta name="description" content="register page" />
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
    <link href="/Public/Cms/css/animate.min.css" rel="stylesheet" />
    <link id="skin-link" href="" rel="stylesheet" type="text/css" />

    <!--Skin Script: Place this script in head to load scripts for skins and rtl support-->
    <script src="/Public/Cms/js/skins.min.js"></script>
</head>
<!--Head Ends-->
<!--Body-->
<body>
<div class="register-container animated fadeInDown">
    <div class="registerbox bg-white">
        <div class="registerbox-title">平台注册</div>

        <div class="registerbox-caption ">请填写您的账户信息</div>
        <form id="regForm" action="<?php echo U('Cms/Public/reg');?>"  method="post">
            <input type="hidden" name="token" value="<?php echo ($_token); ?>">
            <div class="registerbox-textbox">
                <input id="username" type="text" name="username" class="form-control" placeholder="用户名" />
            </div>
            <div class="registerbox-textbox">
                <input id="password" type="password" name="password" class="form-control" placeholder="密码" />
            </div>
            <div class="registerbox-textbox">
                <input id="repassword" type="password" name="repassword" class="form-control" placeholder="确认密码" />
            </div>
            <hr class="wide" />
            <div class="registerbox-textbox">
                <input id="nickname" type="text" class="form-control" name="nickname" placeholder="昵称" />
            </div>

            <div class="registerbox-textbox no-padding-bottom">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" class="colored-primary" checked="checked">
                        <span class="text darkgray">I agree to the Company <a class="themeprimary">Terms of Service</a> and Privacy Policy</span>
                    </label>
                </div>
            </div>
            <div class="loginbox-submit">
                <input class="btn btn-primary btn-block" value="注册" type="submit">
            </div>
        </form>
    </div>
    <div class="logobox">
    </div>
</div>

<!--Basic Scripts-->
<script src="/Public/Cms/js/jquery-2.0.3.min.js"></script>
<script src="/Public/Cms/js/bootstrap.min.js"></script>

<!--Beyond Scripts-->
<script src="/Public/Cms/js/beyond.min.js"></script>
</body>
<script>
    var f2 = false;
    $('#regForm').on('submit',function(){
        var username = $('#username');
        var password = $('#password');
        var repassword = $('#repassword');
        var nickname = $('#nickname');

        if(!$(username).val()){
            Notify('用户名不能为空!', 'top-right', '5000', 'danger', 'fa-bolt', true);
            $(username).focus();
            return false;
        }
        if(!$(username).val()){
            Notify('用户名不能为空!', 'top-right', '5000', 'danger', 'fa-bolt', true);
            $(username).focus();
            return false;
        }
        if(!$(password).val()){
            Notify('用户密码不能为空!', 'top-right', '5000', 'danger', 'fa-bolt', true);
            $(password).focus();
            return false;
        }
        if(!$(repassword).val()){
            Notify('用户密码不能为空!', 'top-right', '5000', 'danger', 'fa-bolt', true);
            $(repassword).focus();
            return false;
        }
        if(!$(password).val()!= $(repassword).val()){
            Notify('两次密码不相等!', 'top-right', '5000', 'danger', 'fa-bolt', true);
            $(password).focus();
            return false;
        }
        if(!$(nickname).val()){
            Notify('昵称不能为空', 'top-right', '5000', 'danger', 'fa-bolt', true);
            $(password).focus();
            return false;
        }
        if(f2===false){
            return false;
        }

        return true;

    });
    $("#username").blur(function(){
        if($("#username").val()!=""){
            var uname =  $("#username").val();
            if(uname.length>=8&&uname.length<=20){
                $(this).css("border-color","green");
                f2 = true;
            }else{
                $(this).css("border-color","red");
                alert("您的昵称可以由小写英文字母、中文、数字组成，长度8-20个字符，一个汉字为两个字符");
                f2 = false;
            }
            if(f2==true){
                $.ajax({
                    //请求方式
                    type:'POST',
                    //发送请求的地址
                    url:"<?php echo U('Cms/Public/checkusername');?>",
                    //服务器返回的数据类型
                    dataType:'json',
                    //发送到服务器的数据，对象必须为key/value的格式，jquery会自动转换为字符串格式
                    data:{username:uname},
                    success:function(data1){
                        if(data1.msg=="ok"){
                            $(this).css("border-color","green");
                            f2 = true;
                        }else{
                            $(this).css("border-color","red");
                            alert(data1.msg);
                            f2 = false;
                        }
                        //请求成功函数内容
                    },
                    error:function(jqXHR){
                        //请求失败函数内容
                    }
                });
            }

        }
    });
    $("#repassword").blur(function(){

        var psw1 = $("#password").val();
        var psw2 = $("#repassword").val();
        if(psw1.length>=6&&psw1.length<=20){
            $("#password").css("border-color","green");
            f2 = true;
        }else{
            $("#password").css("border-color","red");
            $("#repassword").css("border-color","red");
            alert("您的密码可以由大小写英文字母、数字组成，长度6-20位");
            f2 = false;
            return;
        }
        if(psw1==psw2){
            $("#password").css("border-color","green");
            $("#repassword").css("border-color","green");
            f2 = true;
        }else{
            $("#password").css("border-color","red");
            $("#repassword").css("border-color","red");
            alert("两次密码不一致");
            f2 = false;
            return;
        }
    });

</script>
<!--Body Ends-->
</html>