<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<!-- Head -->
<head>
    <meta charset="utf-8" />
    <title>..::<?php echo ($_SESSION["CMS"]["set"]["name"]); ?>::..</title>

    <meta name="description" content="blank page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="/Public/Cms/img/favicon.png" type="image/x-icon">

    <!--Basic Styles-->
    <link href="/Public/Cms/css/bootstrap.min.css" rel="stylesheet" />
    <link id="bootstrap-rtl-link" href="" rel="stylesheet" />
    <link href="/Public/Cms/css/font-awesome.min.css" rel="stylesheet" />
    <link href="/Public/Cms/css/weather-icons.min.css" rel="stylesheet" />

    <!--Beyond styles-->
    <link id="beyond-link" href="/Public/Cms/css/beyond.min.css" rel="stylesheet" />
    <link href="/Public/Cms/css/demo.min.css" rel="stylesheet" />
    <link href="/Public/Cms/css/typicons.min.css" rel="stylesheet" />
    <link href="/Public/Cms/css/animate.min.css" rel="stylesheet" />
    <link href="/Public/Cms/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
    <link id="skin-link" href="" rel="stylesheet" type="text/css" />
    <link href="/Public/Cms/css/joelcms.css" rel="stylesheet" />

    <!--Skin Script: Place this script in head to load scripts for skins and rtl support-->
    <script src="/Public/Cms/js/skins.min.js"></script>
    
    <style>
    	.upimgwell{margin-bottom: 0px;}
    	.clear{ clear: both;}
    	.FL{ float: left;}
    	.FR{ float: right;}
    </style>
</head>
<!-- /Head -->
<!-- Body -->
<body>
    <!-- Loading Container -->
    <div class="loading-container" id="Joel-loading-wrap">
        <div class="loading-progress" id="Joel-loading">
            <div class="rotator">
                <div class="rotator">
                    <div class="rotator colored">
                        <div class="rotator">
                            <div class="rotator colored">
                                <div class="rotator colored"></div>
                                <div class="rotator"></div>
                            </div>
                            <div class="rotator colored"></div>
                        </div>
                        <div class="rotator"></div>
                    </div>
                    <div class="rotator"></div>
                </div>
                <div class="rotator"></div>
            </div>
            <div class="rotator"></div>
        </div>
    </div>
    <!--  /Loading Container -->
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-inner">
            <div class="navbar-container">
                <!-- Navbar Barnd -->
                <div class="navbar-header pull-left">
                    <a href="#" class="navbar-brand"  style="margin-left:12%;">
                        <small>
                            <img src="/Public/Cms/img/logo.png" alt="" />
                        </small>
                    </a>
                </div>
                <!-- /Navbar Barnd -->
                <!-- Sidebar Collapse -->
                <div class="sidebar-collapse" id="sidebar-collapse">
                    <i class="collapse-icon fa fa-bars"></i>
                </div>
                <!-- /Sidebar Collapse -->
                <!-- Account Area and Settings --->
                <div class="navbar-header pull-right">
                    <div class="navbar-account">
                        <ul class="account-area" >
                            
                            <li>
                                <a class="login-area dropdown-toggle" data-toggle="dropdown">
                                    <div class="avatar" title="View your public profile">
                                        <img src="/Public/Cms/img/avatars/adam-jansen.jpg">
                                    </div>
                                    <section>
                                        <h2><span class="profile"><span><?php echo ($_SESSION["CMS"]["user"]["nickname"]); ?>[ <?php echo ($_SESSION["CMS"]["user"]["username"]); ?> ]</span></span></h2>
                                    </section>
                                </a>
                                <!--Login Area Dropdown-->
                                <ul class="pull-right dropdown-menu dropdown-arrow dropdown-login-area">
                                    <li class="username"><a><?php echo ($_SESSION["CMS"]["user"]["username"]); ?></a></li>
                                    <li class="email"><a>用户邮箱预留</a></li>
                                    <!--Avatar Area-->
                                    <li>
                                        <div class="avatar-area">
                                            <img src="/Public/Cms/img/avatars/adam-jansen.jpg" class="avatar">
                                            <span class="caption">用户头像[预留]</span>
                                        </div>
                                    </li>
                                    <!--Avatar Area-->
                                    <li class="edit">
                                        <a href="#" class="pull-right">帐户设置[预留]</a>
                                    </li>
                                    <!--Theme Selector Area-->
                                    <li class="theme-area">
                                        <ul class="colorpicker" id="skin-changer">
                                            <li><a class="colorpick-btn" href="#" style="background-color:#5DB2FF;" rel="/Public/Cms/css/skins/blue.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#2dc3e8;" rel="/Public/Cms/css/skins/azure.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#03B3B2;" rel="/Public/Cms/css/skins/teal.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#53a93f;" rel="/Public/Cms/css/skins/green.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#FF8F32;" rel="/Public/Cms/css/skins/orange.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#cc324b;" rel="/Public/Cms/css/skins/pink.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#AC193D;" rel="/Public/Cms/css/skins/darkred.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#8C0095;" rel="/Public/Cms/css/skins/purple.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#0072C6;" rel="/Public/Cms/css/skins/darkblue.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#585858;" rel="/Public/Cms/css/skins/gray.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#474544;" rel="/Public/Cms/css/skins/black.min.css"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#001940;" rel="/Public/Cms/css/skins/deepblue.min.css"></a></li>
                                        </ul>
                                    </li>
                                    <!--/Theme Selector Area-->
                                    <li class="dropdown-footer">
                                        <a href="<?php echo U('Cms/Public/logout');?>">
                                            注销登录
                                        </a>
                                    </li>
                                </ul>
                                <!--/Login Area Dropdown-->
                            </li>
                            <!-- /Account Area -->
                            <!--Note: notice that setting div must start right after account area list.
                            no space must be between these elements-->
                            <!-- Settings -->
                        </ul><div class="setting">
                            <a id="btn-setting" title="Setting" href="#">
                                <i class="icon glyphicon glyphicon-cog"></i>
                            </a>
                        </div><div class="setting-container">
                            <label>
                                <input type="checkbox" id="checkbox_fixednavbar">
                                <span class="text">固定头部</span>
                            </label>
                            <label>
                                <input type="checkbox" id="checkbox_fixedsidebar">
                                <span class="text">固定左导航</span>
                            </label>
                            <label>
                                <input type="checkbox" id="checkbox_fixedbreadcrumbs">
                                <span class="text">固定面包屑导航</span>
                            </label>
                            <label>
                                <input type="checkbox" id="checkbox_fixedheader">
                                <span class="text">固定全部</span>
                            </label>
                        </div>
                        <!-- Settings -->
                    </div>
                </div>
                <!-- /Account Area and Settings -->
            </div>
        </div>
    </div>
    <!-- /Navbar -->
    <!-- Main Container -->
    <div class="main-container container-fluid">
        <!-- Page Container -->
        <div class="page-container">
            <!-- Page Sidebar -->
            <div class="page-sidebar" id="sidebar">
                <!-- Page Sidebar Header-->
                <div class="sidebar-header-wrapper">
                    <input type="text" class="searchinput" />
                    <i class="searchicon fa fa-search"></i>
                    <div class="searchhelper">搜索预留，未实现</div>
                </div>
                <!-- /Page Sidebar Header -->
                <!-- Sidebar Menu -->
                <ul class="nav sidebar-menu">
                    <!--系统首页-->
                    <li>
                        <a id="JoelHome" href="<?php echo U('Cms/Index/main');?>" data-loader="Joel-loader" data-loadername="主控面板">
                            <i class="menu-icon glyphicon glyphicon-home"></i>
                            <span class="menu-text"> 系统首页 </span>
                        </a>
                    </li>
                    <!--系统设置-->
                    <?php if(in_array(($sys), is_array($useroath)?$useroath:explode(',',$useroath))): ?><!--系统设置-->
                    <li>
                        <a href="#" class="menu-dropdown">
                            <i class="menu-icon fa fa-th"></i>
                            <span class="menu-text"> 系统设置 </span>
                            <i class="menu-expand"></i>
                        </a>
                        <ul class="submenu">
                        	<li>
                                <a href="<?php echo U('Cms/User/userList');?>" data-loader="Joel-loader" data-loadername="管理员管理">
                                    <span class="menu-text">系统管理员</span>
                                </a>
                            </li>
                           	<li>
                                <a href="<?php echo U('Cms/Wx/set');?>" data-loader="Joel-loader" data-loadername="微信设置">
                                    <span class="menu-text">微信设置</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Vip/set');?>" data-loader="Joel-loader" data-loadername="会员设置">
                                    <span class="menu-text">会员设置</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Shop/set');?>" data-loader="Joel-loader" data-loadername="商城设置">
                                    <span class="menu-text">商城设置</span>
                                </a>
                            </li>
							 <li>
                                <a href="<?php echo U('Cms/Represent/dyset');?>" data-loader="Joel-loader" data-loadername="我要代言">
                                    <span class="menu-text">我要代言</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Syscfg/index');?>" data-loader="Joel-loader" data-loadername="参数设置">
                                    <span class="menu-text">参数设置</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/express/set');?>" data-loader="Joel-loader" data-loadername="快递设置">
                                    <span class="menu-text">快递设置</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/express/area');?>" data-loader="Joel-loader" data-loadername="区域邮费">
                                    <span class="menu-text">区域邮费</span>
                                </a>
                            </li>
                        </ul>
                    </li><?php endif; ?>
                    <?php if(in_array(($wx), is_array($useroath)?$useroath:explode(',',$useroath))): ?><!--微信管理-->
                    <li>
                        <a href="#" class="menu-dropdown">
                            <i class="menu-icon fa fa-linux"></i>
                            <span class="menu-text"> 微信管理 </span>
                            <i class="menu-expand"></i>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="<?php echo U('Cms/Wx/keyword');?>" data-loader="Joel-loader" data-loadername="魔法关键词">
                                    <span class="menu-text">魔法关键词</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Diymenu/cate');?>" data-loader="Joel-loader" data-loadername="自定义菜单">
                                    <span class="menu-text">自定义菜单</span>
                                </a>
                            </li>
                             <li>
                                <a href="<?php echo U('Cms/Template/index');?>" data-loader="Joel-loader" data-loadername="自定义菜单">
                                    <span class="menu-text">微信模板通知</span>
                                </a>
                            </li>
                        </ul>
                    </li><?php endif; ?>
                    <?php if(in_array(($fxs), is_array($useroath)?$useroath:explode(',',$useroath))): ?><!--分销商管理-->
                    <li>
                        <a href="#" class="menu-dropdown">
                            <i class="menu-icon fa fa-sitemap"></i>
                            <span class="menu-text"> 分销商管理 </span>
                            <i class="menu-expand"></i>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="<?php echo U('Cms/Fxs/user');?>" data-loader="Joel-loader" data-loadername="分销商管理">
                                    <span class="menu-text">分销商管理</span>
                                </a>
                            </li>
                            <li>
                            	<a href="#" class="menu-dropdown">
		                            <span class="menu-text">分销商提现管理</span>
		                            <i class="menu-expand"></i>
                        		</a>
                        		<ul class="submenu">
                                		<li>
                                		<a href="<?php echo U('Cms/Fxs/txorder',array('status'=>'1'));?>" data-loader="Joel-loader" data-loadername="提现订单管理-新订单">
	                                    	<i class="glyphicon glyphicon-usd"></i>
	                                    	<span class="menu-text">新申请</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Fxs/txorder',array('status'=>'2'));?>" data-loader="Joel-loader" data-loadername="提现订单管理-提现完成">
	                                    	<i class="glyphicon glyphicon-ok"></i>
	                                    	<span class="menu-text">提现完成</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Fxs/txorder',array('status'=>'0'));?>" data-loader="Joel-loader" data-loadername="提现订单管理-提现取消">
	                                    	<i class="glyphicon glyphicon-remove"></i>
	                                    	<span class="menu-text">提现取消</span>
                                		</a>
                                		</li>
                               	</ul>
                          </li>
                        </ul>
                    </li><?php endif; ?>
                    <?php if(in_array(($vip), is_array($useroath)?$useroath:explode(',',$useroath))): ?><!--会员中心-->
                    <li>
                        <a href="#" class="menu-dropdown">
                            <i class="menu-icon fa fa-user"></i>
                            <span class="menu-text"> 会员中心 </span>
                            <i class="menu-expand"></i>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="<?php echo U('Cms/Vip/level');?>" data-loader="Joel-loader" data-loadername="会员等级">
                                    <span class="menu-text">会员等级</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Vip/vipList');?>" data-loader="Joel-loader" data-loadername="会员列表">
                                    <span class="menu-text">会员列表</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Vip/message');?>" data-loader="Joel-loader" data-loadername="会员消息">
                                    <span class="menu-text">会员消息</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="menu-dropdown">
                                    <span class="menu-text">卡券设置</span>
                                    <i class="menu-expand"></i>
                                </a>
                                <ul class="submenu">
                                    <li>
                                        <a href="<?php echo U('Cms/Vip/card',array('type'=>'1'));?>" data-loader="Joel-loader" data-loadername="卡券列表-充值卡">
                                            <i class="glyphicon glyphicon-credit-card"></i>
                                            <span class="menu-text">充值卡</span>
                                        </a>
                                    </li>
									<li>
                                        <a href="<?php echo U('Cms/Vip/card',array('type'=>'2'));?>" data-loader="Joel-loader" data-loadername="卡券列表-代金券">
                                            <i class="glyphicon glyphicon-euro"></i>
                                            <span class="menu-text">代金券</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                            	<a href="#" class="menu-dropdown">
		                            <span class="menu-text">会员提现管理</span>
		                            <i class="menu-expand"></i>
                        		</a>
                        		<ul class="submenu">
                                		<li>
                                		<a href="<?php echo U('Cms/Vip/txorder',array('status'=>'1'));?>" data-loader="Joel-loader" data-loadername="提现订单管理-新订单">
	                                    	<i class="glyphicon glyphicon-usd"></i>
	                                    	<span class="menu-text">新申请</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Vip/txorder',array('status'=>'2'));?>" data-loader="Joel-loader" data-loadername="提现订单管理-提现完成">
	                                    	<i class="glyphicon glyphicon-ok"></i>
	                                    	<span class="menu-text">提现完成</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Vip/txorder',array('status'=>'0'));?>" data-loader="Joel-loader" data-loadername="提现订单管理-提现取消">
	                                    	<i class="glyphicon glyphicon-remove"></i>
	                                    	<span class="menu-text">提现取消</span>
                                		</a>
                                		</li>
                               	</ul>
                          </li>
                        </ul>
                    </li><?php endif; ?>
                    <!--<?php if(in_array(($news), is_array($useroath)?$useroath:explode(',',$useroath))): ?>新闻管理
                    <li>
                        <a href="#" class="menu-dropdown">
                            <i class="menu-icon fa  fa-list-ul"></i>
                            <span class="menu-text"> 新闻管理 </span>
                            <i class="menu-expand"></i>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="<?php echo U('Cms/News/news');?>" data-loader="Joel-loader" data-loadername="新闻管理">
                                    <span class="menu-text">新闻管理</span>
                                </a>
                            </li>                            
                        </ul>
                    </li><?php endif; ?>-->
                    
                    <!--1.20 zxg 左菜单显示微活动列表-->
                    
                    <!--1.20 zxg 左菜单显示微活动列表-->
                    
                    <?php if(in_array(($active), is_array($useroath)?$useroath:explode(',',$useroath))): ?><li>
                        <a href="#" class="menu-dropdown">
                            <i class="menu-icon fa fa-users"></i>
                            <span class="menu-text"> 营销功能 </span>
                            <i class="menu-expand"></i>
                        </a>
                        <ul class="submenu">
                        	<li>
                                <a href="#" class="menu-dropdown">
                                    <span class="menu-text">大转盘设置</span>
                                    <i class="menu-expand"></i>
                                </a>
                                <ul class="submenu">
                                   
                                    <li>
	                                    <a href="<?php echo U('Cms/Active/dzp');?>" data-loader="Joel-loader" data-loadername="大转盘设置-活动列表">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">活动列表</span>
	                                    </a>
                                    </li>
                                    
                                    <li>
                                    <a href="<?php echo U('Cms/Active/acformList');?>" data-loader="Joel-loader" data-loadername="大转盘设置-活动表单列表">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">活动表单列表</span>
                                    </a>
                                    </li>
                                    
                                    <li>
                                    <a href="<?php echo U('Cms/Active/prize');?>" data-loader="Joel-loader" data-loadername="大转盘设置-奖项设置">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">奖项设置</span>
                                    </a>
                                    </li>
                                    <li>
                                    <a href="<?php echo U('Cms/Active/dzplog');?>" data-loader="Joel-loader" data-loadername="大转盘设置-抽奖日志">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">抽奖日志</span>
                                    </a>
                                    </li>
                                    <li>
                                    <a href="<?php echo U('Cms/Active/zjlog');?>" data-loader="Joel-loader" data-loadername="大转盘设置-中奖纪录">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">中奖纪录</span>
                                    </a>
                                    </li>
                                    <li>
                                    <a href="<?php echo U('Cms/Active/sdpj');?>" data-loader="Joel-loader" data-loadername="大转盘设置-手动派奖">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">手动派奖</span>
                                    </a>
                                    </li>
                                </ul>
                            </li>    
                            <li>
                                <a href="#" class="menu-dropdown">
                                    <span class="menu-text">砸金蛋设置</span>
                                    <i class="menu-expand"></i>
                                </a>
                                <ul class="submenu">
                                   
                                    <li>
	                                    <a href="<?php echo U('Cms/Zjd/dzp');?>" data-loader="Joel-loader" data-loadername="砸金蛋设置-活动列表">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">活动列表</span>
	                                    </a>
                                    </li>
                                    
                                    <li>
                                    <a href="<?php echo U('Cms/Zjd/acformList');?>" data-loader="Joel-loader" data-loadername="砸金蛋设置-活动表单列表">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">活动表单列表</span>
                                    </a>
                                    </li>
                                    
                                    <li>
                                    <a href="<?php echo U('Cms/Zjd/prize');?>" data-loader="Joel-loader" data-loadername="砸金蛋设置-奖项设置">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">奖项设置</span>
                                    </a>
                                    </li>
                                    <li>
                                    <a href="<?php echo U('Cms/Zjd/dzplog');?>" data-loader="Joel-loader" data-loadername="砸金蛋设置-抽奖日志">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">抽奖日志</span>
                                    </a>
                                    </li>
                                    <li>
                                    <a href="<?php echo U('Cms/Zjd/zjlog');?>" data-loader="Joel-loader" data-loadername="砸金蛋设置-中奖纪录">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">中奖纪录</span>
                                    </a>
                                    </li>
                                    <li>
                                    <a href="<?php echo U('Cms/Zjd/sdpj');?>" data-loader="Joel-loader" data-loadername="砸金蛋设置-手动派奖">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">手动派奖</span>
                                    </a>
                                    </li>
                                </ul>
                            </li>    
                            <li>
                                <a href="#" class="menu-dropdown">
                                    <span class="menu-text">刮刮卡设置</span>
                                    <i class="menu-expand"></i>
                                </a>
                                <ul class="submenu">
                                   
                                    <li>
	                                    <a href="<?php echo U('Cms/Ggk/dzp');?>" data-loader="Joel-loader" data-loadername="刮刮卡设置-活动列表">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">活动列表</span>
	                                    </a>
                                    </li>
                                    
                                    <li>
                                    <a href="<?php echo U('Cms/Ggk/acformList');?>" data-loader="Joel-loader" data-loadername="刮刮卡设置-活动表单列表">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">活动表单列表</span>
                                    </a>
                                    </li>
                                    
                                    <li>
                                    <a href="<?php echo U('Cms/Ggk/prize');?>" data-loader="Joel-loader" data-loadername="刮刮卡设置-奖项设置">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">奖项设置</span>
                                    </a>
                                    </li>
                                    <li>
                                    <a href="<?php echo U('Cms/Ggk/dzplog');?>" data-loader="Joel-loader" data-loadername="刮刮卡设置-抽奖日志">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">抽奖日志</span>
                                    </a>
                                    </li>
                                    <li>
                                    <a href="<?php echo U('Cms/Ggk/zjlog');?>" data-loader="Joel-loader" data-loadername="刮刮卡设置-中奖纪录">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">中奖纪录</span>
                                    </a>
                                    </li>
                                    <li>
                                    <a href="<?php echo U('Cms/Ggk/sdpj');?>" data-loader="Joel-loader" data-loadername="刮刮卡设置-手动派奖">
                                        <i class="glyphicon glyphicon-asterisk"></i>
                                        <span class="menu-text">手动派奖</span>
                                    </a>
                                    </li>
                                </ul>
                            </li>  
                            <!-- 作者：郑伊凡 2016-2-15 母版本 功能：模版配置 -->
                            <!-- <li>
                                <a href="<?php echo U('Cms/Shop/indexset');?>" data-loader="Joel-loader" data-loadername="首页模板设置">
                                    <span class="menu-text">首页模板设置</span>
                                </a>
                            </li> -->
                            <!-- 作者：郑伊凡 2016-2-15 母版本 功能：模版配置 -->
                            
                            
                            <!-- 作者：张旭光  2016-2-18 母版本 功能：拼团购   聚友杀-->
                            <li>
                            	<a href="#" class="menu-dropdown">
                                    <span class="menu-text">拼团购设置</span>
                                    <i class="menu-expand"></i>
                                </a>
                                <ul class="submenu">
                                   
                                    <li>
	                                    <a href="<?php echo U('Cms/Shop/ptgset');?>" data-loader="Joel-loader" data-loadername="拼团购设置">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">拼团购设置</span>
	                                    </a>
                                    </li>
                                    <li>
	                                    <a href="<?php echo U('Cms/Shop/ptglist');?>" data-loader="Joel-loader" data-loadername="拼团购设置-团购列表">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">团购列表</span>
	                                    </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo U('Cms/Shop/ptgorder');?>" data-loader="Joel-loader" data-loadername="拼团购设置-团购订单">
                                            <i class="glyphicon glyphicon-asterisk"></i>
                                            <span class="menu-text">团购订单</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                            	<a href="#" class="menu-dropdown">
                                    <span class="menu-text">聚友杀设置</span>
                                    <i class="menu-expand"></i>
                                </a>
                                <ul class="submenu">
                                   
                                    <li>
	                                    <a href="<?php echo U('Cms/Shop/jysset');?>" data-loader="Joel-loader" data-loadername="聚友杀设置">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">聚友杀设置</span>
	                                    </a>
                                    </li>
                                    <li>
	                                    <a href="<?php echo U('Cms/Shop/jyslist');?>" data-loader="Joel-loader" data-loadername="聚友杀设置-聚友杀列表">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">聚友杀列表</span>
	                                    </a>
                                    </li>
                                    
                                </ul>
                            </li>
                            <!-- 作者：张旭光  2016-2-18 母版本 功能：拼团购   聚友杀-->
                            
                            <!-- 作者：张旭光  2016-2-24 母版本 功能：积分商城-->
                            <li>
                            	<a href="#" class="menu-dropdown">
                                    <span class="menu-text">积分商城设置</span>
                                    <i class="menu-expand"></i>
                                </a>
                                <ul class="submenu">
                                   
                                    <li>
	                                    <a href="<?php echo U('Cms/Shop/integset');?>" data-loader="Joel-loader" data-loadername="积分商城设置">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">积分商城设置</span>
	                                    </a>
                                    </li>
                                    <li>
	                                    <a href="<?php echo U('Cms/Shop/integlist');?>" data-loader="Joel-loader" data-loadername="积分商城设置-积分商品列表">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">积分商品列表</span>
	                                    </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- 作者：张旭光  2016-2-24 母版本 功能：积分商城-->
                            <!-- 作者：张旭光  2016-3-3 母版本 功能：一元夺宝-->
                            <li>
                            	<a href="#" class="menu-dropdown">
                                    <span class="menu-text">一元夺宝设置</span>
                                    <i class="menu-expand"></i>
                                </a>
                                <ul class="submenu">
                                   
                                    <li>
	                                    <a href="<?php echo U('Cms/Shop/yydbset');?>" data-loader="Joel-loader" data-loadername="一元夺宝设置">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">一元夺宝设置</span>
	                                    </a>
                                    </li>
                                    <li>
	                                    <a href="<?php echo U('Cms/Shop/yydblist');?>" data-loader="Joel-loader" data-loadername="一元夺宝设置-一元夺宝商品列表">
	                                        <i class="glyphicon glyphicon-asterisk"></i>
	                                        <span class="menu-text">一元夺宝商品列表</span>
	                                    </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- 作者：张旭光  2016-3-3 母版本 功能：一元夺宝-->
                            <!-- 作者：郑伊凡  2016-3-9 母版本 功能：红包功能-->
                            <li>
                                <a href="#" class="menu-dropdown">
                                    <span class="menu-text">红包设置</span>
                                    <i class="menu-expand"></i>
                                </a>
                                <ul class="submenu">
                                   
                                    <li>
                                        <a href="<?php echo U('Cms/redpaper/set');?>" data-loader="Joel-loader" data-loadername="红包设置-红包活动管理">
                                            <i class="glyphicon glyphicon-asterisk"></i>
                                            <span class="menu-text">红包活动管理</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo U('Cms/redpaper/orderlist');?>" data-loader="Joel-loader" data-loadername="红包设置-红包订单列表">
                                            <i class="glyphicon glyphicon-asterisk"></i>
                                            <span class="menu-text">红包订单列表</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo U('Cms/redpaper/loglist');?>" data-loader="Joel-loader" data-loadername="红包设置-红包操作日志">
                                            <i class="glyphicon glyphicon-asterisk"></i>
                                            <span class="menu-text">红包操作日志</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- 作者：郑伊凡  2016-3-9 母版本 功能：红包功能-->
                            
                            <li>                                
                                <a href="<?php echo U('Cms/News/news');?>" data-loader="Joel-loader" data-loadername="新闻管理">
                                    <span class="menu-text">新闻管理</span>
                                </a>
                            </li>   
                            <li>                                
                                <a href="<?php echo U('Cms/News/staff');?>" data-loader="Joel-loader" data-loadername="员工管理">
                                    <span class="menu-text">员工管理</span>
                                </a>
                            </li>  
                        </ul>
                    </li><?php endif; ?>
                  	<!--1.20 zxg 左菜单显示微活动列表-->
                
                    <!--商城设置-->
                    <?php if(in_array(($shop), is_array($useroath)?$useroath:explode(',',$useroath))): ?><li>
                        <a href="#" class="menu-dropdown">
                            <i class="menu-icon glyphicon glyphicon-shopping-cart"></i>
                            <span class="menu-text"> 商城管理 </span>
                            <i class="menu-expand"></i>
                        </a>
                        <ul class="submenu">
                        	
                            <li>
                                <a href="<?php echo U('Cms/Shop/ads');?>" data-loader="Joel-loader" data-loadername="广告管理">
                                    <span class="menu-text">广告管理</span>
                                </a>
                            </li>
                        	<li>
                                <a href="<?php echo U('Cms/Shop/cate');?>" data-loader="Joel-loader" data-loadername="商城分类">
                                    <span class="menu-text">商城分类</span>
                                </a>
                            </li>
                            <!--<li>
                                <a href="<?php echo U('Cms/Shop/group');?>" data-loader="Joel-loader" data-loadername="商城分组">
                                    <span class="menu-text">商城分组</span>
                                </a>
                            </li>-->
                            <li>
                                <a href="<?php echo U('Cms/Shop/skuattr');?>" data-loader="Joel-loader" data-loadername="SKU属性">
                                    <span class="menu-text">SKU属性</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Shop/label');?>" data-loader="Joel-loader" data-loadername="标签管理">
                                    <span class="menu-text">标签管理</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Shop/goods');?>" data-loader="Joel-loader" data-loadername="商品管理">
                                    <span class="menu-text">商品管理</span>
                                </a>
                            </li>
                            <li>
                            	<a href="#" class="menu-dropdown">
		                            <span class="menu-text">订单管理</span>
		                            <i class="menu-expand"></i>
                        		</a>
                        		<ul class="submenu">
                                    	<li>
                                        <a href="<?php echo U('Cms/Shop/order');?>" data-loader="Joel-loader" data-loadername="订单管理-全部订单">
	                                    	<i class="glyphicon glyphicon-asterisk"></i>
	                                    	<span class="menu-text">全部</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Shop/order',array('status'=>'1'));?>" data-loader="Joel-loader" data-loadername="订单管理-未支付订单">
	                                    	<i class="glyphicon glyphicon-heart-empty"></i>
	                                    	<span class="menu-text">未付款</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Shop/order',array('status'=>'2'));?>" data-loader="Joel-loader" data-loadername="订单管理-已支付订单">
	                                    	<i class="glyphicon glyphicon-usd"></i>
	                                    	<span class="menu-text">已付款</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Shop/order',array('status'=>'3'));?>" data-loader="Joel-loader" data-loadername="订单管理-已发货订单">
	                                    	<i class="glyphicon glyphicon-export"></i>
	                                    	<span class="menu-text">已发货</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Shop/order',array('status'=>'4'));?>" data-loader="Joel-loader" data-loadername="订单管理-退货中订单">
	                                    	<i class="glyphicon glyphicon-import"></i>
	                                    	<span class="menu-text">退货中</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Shop/order',array('status'=>'7'));?>" data-loader="Joel-loader" data-loadername="订单管理-退货完成">
	                                    	<i class="glyphicon glyphicon-saved"></i>
	                                    	<span class="menu-text">退货完成</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Shop/order',array('status'=>'5'));?>" data-loader="Joel-loader" data-loadername="订单管理-交易完成">
	                                    	<i class="glyphicon glyphicon-ok"></i>
	                                    	<span class="menu-text">交易完成</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Shop/order',array('status'=>'6'));?>" data-loader="Joel-loader" data-loadername="订单管理-交易关闭">
	                                    	<i class="glyphicon glyphicon-remove"></i>
	                                    	<span class="menu-text">交易关闭</span>
                                		</a>
                                		</li>
                                		<li>
                                		<a href="<?php echo U('Cms/Shop/order',array('status'=>'0'));?>" data-loader="Joel-loader" data-loadername="订单管理-交易取消">
	                                    	<i class="glyphicon glyphicon-trash"></i>
	                                    	<span class="menu-text">交易取消</span>
                                		</a>
                                		</li>                                    
                                </ul>
                            </li>
                            
                        </ul>
                    </li><?php endif; ?>
                    <!--日志中心-->
                    <?php if(in_array(($log), is_array($useroath)?$useroath:explode(',',$useroath))): ?><li>
                        <a href="#" class="menu-dropdown">
                            <i class="menu-icon glyphicon glyphicon-list-alt"></i>
                            <span class="menu-text"> 日志中心 </span>
                            <i class="menu-expand"></i>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="<?php echo U('Cms/Log/vip');?>" data-loader="Joel-loader" data-loadername="会员日志">
                                    <span class="menu-text">会员日志</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Log/order');?>" data-loader="Joel-loader" data-loadername="订单日志">
                                    <span class="menu-text">订单日志</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Log/fx');?>" data-loader="Joel-loader" data-loadername="会员分销日志">
                                    <span class="menu-text">会员分销日志</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Log/fxs');?>" data-loader="Joel-loader" data-loadername="经销商分销日志">
                                    <span class="menu-text">经销商分销日志</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Log/tj');?>" data-loader="Joel-loader" data-loadername="会员推广日志">
                                    <span class="menu-text">会员推广日志</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/Log/fxstj');?>" data-loader="Joel-loader" data-loadername="分销商推广日志">
                                    <span class="menu-text">分销商推广日志</span>
                                </a>
                            </li>
                        </ul>
                    </li><?php endif; ?>
                    <!--管理员操作日志-->
                    <?php if(in_array(($adminlog), is_array($useroath)?$useroath:explode(',',$useroath))): ?><li>
                        <a href="#" class="menu-dropdown">
                            <i class="menu-icon glyphicon glyphicon-list-alt"></i>
                            <span class="menu-text">管理员操作日志</span>
                            <i class="menu-expand"></i>
                        </a>
                        <ul class="submenu">
                        	<li>
                                <a href="<?php echo U('Cms/adminlog/login');?>" data-loader="Joel-loader" data-loadername="登入登出日志">
                                    <span class="menu-text">登入登出日志</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/adminlog/vip');?>" data-loader="Joel-loader" data-loadername="操作会员日志">
                                    <span class="menu-text">操作会员日志</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo U('Cms/adminlog/order');?>" data-loader="Joel-loader" data-loadername="操作订单日志">
                                    <span class="menu-text">操作订单日志</span>
                                </a>
                            </li>
                         
                        </ul>
                    </li><?php endif; ?>
                </ul>
                <!-- /Sidebar Menu -->
            </div>
            <!-- /Page Sidebar -->
            <!-- Page Content -->
            <div class="page-content">
                <!-- Page Breadcrumb -->
                <div class="page-breadcrumbs">
                    <ul class="breadcrumb" id="Joel-bread">
                        <li>
                            <i class="fa fa-home"></i>
                            <a href="<?php echo U('Cms/Index/main');?>" data-loader = "Joel-loader" data-loadername="主控面板">首页</a>
                        </li>
                        <li class="active">主控面板</li>
                    </ul>
                </div>
                <!-- /Page Breadcrumb -->
                <!-- Page Header -->
                <div class="page-header position-relative">
                    <div class="header-title">
                        <h1 id="Joel-loader-title">
                            	主控面板
                        </h1>
                    </div>
                    <!--Header Buttons-->
                    <div class="header-buttons">
                        <a class="sidebar-toggler" href="#">
                            <i class="fa fa-arrows-h"></i>
                        </a>
                        <a class="refresh" id="refresh-toggler" href="<?php echo U('Cms/Index/mian');?>" data-loader = "Joel-loader" data-name = "主控面板">
                            <i class="glyphicon glyphicon-refresh"></i>
                        </a>
                        <a class="fullscreen" id="fullscreen-toggler" href="#">
                            <i class="glyphicon glyphicon-fullscreen"></i>
                        </a>
                    </div>
                    <!--Header Buttons End-->
                </div>
                <!-- /Page Header -->
                <!-- Page Body -->
                <div id="Joel-loader" class="page-body">
                    <!-- 主加载器 -->
                </div>
                <!--图片库-->
               
                <!-- /Page Body -->
            </div>
            <!-- /Page Content -->
        </div>
        <!-- /Page Container -->
        <!-- Main Container -->
		
    </div>
    <!--全局隐藏控件-->
    <div class="hide">
    	 <!--JoelReloader-->
    	 <a id="Joel-reloader" href="#">JOELRELOADER</a>
    	 <!--单图片上传控件-->
    	 <iframe style="display:none" name='doupimg_frame' id="doupimg_frame"></iframe> 
   		 <form enctype="multipart/form-data" action="<?php echo U('Cms/Upload/doupimg');?>" method="post" id="Joel-form-upimg" target="doupimg_frame" >
		 	<input type="file" id="jupimg" name="jupimg" accept="image/*">
	 	 </form>
    </div>
   

    <!--基础脚本-->
    <script src="/Public/Cms/js/jquery-2.0.3.min.js"></script>
    <script src="/Public/Cms/js/bootstrap.min.js"></script>
    <script src="/Public/Cms/js/beyond.min.js"></script>
    <script src="/Public/Cms/js/toastr/toastr.js"></script>
    <script src="/Public/Cms/js/validation/bootstrapValidator.js"></script>
    <script src="/Public/Cms/js/bootbox/bootbox.js"></script>
    
    <!--时间脚本-->
     <!--Bootstrap Date Picker-->
    <script src="/Public/Cms/js/datetime/bootstrap-datepicker.js"></script>

    <!--Bootstrap Time Picker-->
    <script src="/Public/Cms/js/datetime/bootstrap-timepicker.js"></script>    
    <script src="/Public/Cms/js/datetime/bootstrap-datetimepicker.js"></script>
    <!--统计脚本-->
    <script src="/Public/Cms/js/charts/easypiechart/jquery.easypiechart.js"></script>
    <script src="/Public/Cms/js/charts/easypiechart/easypiechart-init.js"></script>
    <script src="/Public/Cms/js/charts/flot/jquery.flot.js"></script>
    <script src="/Public/Cms/js/charts/flot/jquery.flot.orderBars.js"></script>
    <script src="/Public/Cms/js/charts/flot/jquery.flot.tooltip.js"></script>
    <script src="/Public/Cms/js/charts/flot/jquery.flot.resize.js"></script>
    <script src="/Public/Cms/js/charts/flot/jquery.flot.selection.js"></script>
    <script src="/Public/Cms/js/charts/flot/jquery.flot.crosshair.js"></script>
    <script src="/Public/Cms/js/charts/flot/jquery.flot.stack.js"></script>
    <script src="/Public/Cms/js/charts/flot/jquery.flot.time.js"></script>
    <script src="/Public/Cms/js/charts/flot/jquery.flot.pie.js"></script>
    <script src="/Public/Cms/js/charts/chartjs/Chart.js"></script>
    <!--百度编辑器-->
    <script src="/Public/Cms/ueditor/ueditor.config.js"></script>
	<script src="/Public/Cms/ueditor/ueditor.all.min.js"></script>
    <!--百度地图类库-->
	<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=oOC9YM1VwjGkdsn7oLElg8vF"></script>
	<!--Joel全局API-->
    <script src="/Public/Cms/js/joelapi.js"></script>

	<script type="text/javascript">
		var JoelLoaderTitle=$('#Joel-loader-title');
		var JoelLoaderRefresh=$('#refresh-toggler');
		var JoelLoaderReloader=$('#Joel-reloader');
		var JoelSbLi=$('.sidebar-menu li');
		//主导航高亮
		var JoelSideli=$('.submenu li');
		//公共设置HTML内容方法
		function setHtml(id,html){
			$(id).html(html);
		}
		
		//初始化主框架加载后的操作
		function initFrame(){
			var JoelLoaderTitle=$('#Joel-loader-title');
			var JoelLoaderRefresh=$('#refresh-toggler');
			var JoelLoaderReloader=$('#Joel-reloader');
			//处理Frame加载后的所有链接
			var links=$('a');
			$(links).on('click',function(){
				
				//$(JoelSideli).removeClass('active');				
				var loader=$(this).data('loader');
				var tourl=$(this).attr('href');
				var name=$(this).data('loadername');
				$(JoelLoaderReloader).attr('href',tourl).data('loader',loader).data('loadername',name);
				if(loader){		
					//高亮主导航
					var li=$(this).parent('li');
					//$(li).siblings().removeClass('active');
					$(JoelSbLi).removeClass('active');
					$(li).addClass('active');
					//如果是主Loader
					if(loader=='Joel-loader'){
						setHtml(JoelLoaderTitle,name);
						$(JoelLoaderRefresh).attr('href',tourl).data('loader','Joel-loader').data('loadername',name);
						
					}
					$('#'+loader).empty().load(tourl,function(){
						initLoader(loader);
					});					
					return false;
				}
			});
		}
		//初始化Loader加载后的操作
		function initLoader(loader){
			//加载Widget特效
			InitiateWidgets();
			//处理Loader加载后的所有链接
			var loaderlinks=$('#'+loader+' a');
			$(loaderlinks).on('click',function(){
				var loader=$(this).data('loader');
				var tourl=$(this).attr('href');
				var search=$(this).data('search');
				var name=$(this).data('loadername');
				//特殊按钮特效--全部阻止
				var type=$(this).data('type');
				
				if(type){
					switch(type){
						case 'del':
						var toajax=$(this).data('ajax');
						var funok=function(){
							var callok=function(){
								//成功删除后刷新
								$(JoelLoaderReloader).trigger('click');
								return false;
							};
							var callerr=function(){
							//拦截错误
							return false;
							};
							$.Joel.ajax('post',toajax,'nodata',callok,callerr);
						}						
						$.Joel.confirm("确认要删除吗？",funok);
						return false;
						//
						break;
						default:
						$.Joel.alert('danger','此Type属性系统未定义！');
						break;
					}
					
				}else{
					//不存在特殊效果时，绑定Reloader刷新地址
					$(JoelLoaderReloader).attr('href',tourl).data('loader',loader).data('loadername',name).data('search',search);
				}
				
				if(loader){
					//如果是主Loader
					if(loader=='Joel-loader'){
						setHtml(JoelLoaderTitle,name);
						$(JoelLoaderRefresh).attr('href',tourl).data('loader','Joel-loader').data('loadername',name);
					}
					//如果有搜索条件绑定
					if(search){
						var sv=$('#'+search).serialize();
						if(sv){
							tourl=tourl+'?'+sv;
						}
					}
					$('#'+loader).empty().load(tourl,function(){
						initLoader(loader);
					});					
					return false;
				}
			});
			
		}
		//公共设置面包屑导航
		function setBread(html){
			$('#Joel-bread').empty().html(html);
			$('#Joel-bread a').on('click',function(){
				var loader=$(this).data('loader');
				var name=$(this).data('loadername');
				var tourl=$(this).attr('href');
				setHtml(JoelLoaderTitle,name);
				$(JoelLoaderRefresh).attr('href',tourl).data('loader','Joel-loader').data('loadername',name);
				$('#'+loader).empty().load(tourl,function(){
						initLoader(loader);
				});					
				return false;
			});
		}
		
		//Joel默认图片上传管理器
		function joelImguploader(fbid,isall){
			//fbid 查找带回的文本框ID,全局唯一
			//isall 多图,单图模式
			$.ajax({
					type:"post",
					url:"<?php echo U('Cms/Upload/indeximg');?>",
					data:{'fbid':fbid,'isall':isall},
					dataType: "json",
					//beforeSend:$.Joel.loading(),
					success:function(mb){
						//$.Joel.loading();
						bootbox.dialog({
	                	message: mb,
	                	title: "图片上传管理器",
	                	className: "modal-darkorange",
	                	buttons: {
	                		   "追加": {
			                        className: "btn-success",
			                        callback: function () {if(isall=='false'){$('#'+fbid).val($('#Joel-uploader-findback').val());}else{$('#'+fbid).val($('#'+fbid).val()+$('#Joel-uploader-findback').val());}}
			                    },
			                    "替换": {
			                        className: "btn-blue",
			                        callback: function () {$('#'+fbid).val($('#Joel-uploader-findback').val());}
			                    },
			                    "取消": {
			                        className: "btn-danger",
			                        callback: function () { }
			                    }
		                	}
		            	});
					},
					error:function(xhr){
						$.Joel.alert('danger','通讯失败！请重试！');
					}
			});
			return false;
		}
		//Joel默认图片预览器
		function joelImgviewer(fbid){
			//fbid 查找带回的文本框ID,全局唯一
			//isall 多图,单图模式
			var ids=$('#'+fbid).val();
			if(!ids){
				$.Joel.alert('danger','您还没有图片可以预览！');
				return false;
			}
			$.ajax({
					type:"post",
					url:"<?php echo U('Cms/Index/joelImgviewer');?>",
					data:{'ids':ids},
					dataType: "json",
					success:function(mb){
						bootbox.dialog({
	                	message: mb,
	                	title: "图片预览器",
	                	className: "modal-darkorange",
	                	buttons: {
			                    success: {
			                        label: "确定",
			                        className: "btn-blue",
			                        callback: function () { }
			                    },
			                    "取消": {
			                        className: "btn-danger",
			                        callback: function () { }
			                    }
		                	}
		            	});
					},
					error:function(xhr){
						$.Joel.alert('danger','通讯失败！请重试！');
					}
			});
			return false;
		}
		//Joel默认百度地图控件
		function baiduDitu(fbaddid,fblngid,fblatid){
			var fbadd=$('#'+fbaddid);
			var fblng=$('#'+fblngid);
			var fblat=$('#'+fblatid);
			if(!fbadd || !fblng || !fblat){
				$.Joel.alert('danger','回调控件不完整!');
			}
			//fbid 查找带回的文本框ID,全局唯一		
			$.ajax({
					type:"post",
					url:"<?php echo U('Cms/Public/baiduDitu');?>",
					data:{'address':$(fbadd).val(),'lng':$(fblng).val(),'lat':$(fblat).val()},
					dataType: "json",
					success:function(mb){
						bootbox.dialog({
	                	message: mb,
	                	title: "百度地图控件",
	                	className: "modal-darkorange",
	                	buttons: {
			                    success: {
			                        label: "确定",
			                        className: "btn-blue",
			                        callback: function () { 
			                        	$(fbadd).val($('#baiduDituaddress').val());
			                        	$(fblng).val($('#baiduDitulng').val());
			                        	$(fblat).val($('#baiduDitulat').val());
			                        }
			                    },
			                    "取消": {
			                        className: "btn-danger",
			                        callback: function () { }
			                    }
		                	}
		            	});
					},
					error:function(xhr){
						$.Joel.alert('danger','通讯失败！请重试！');
					}
			});
			return false;
		}
		//Joel默认SKU管理器
		function joelSkuloader(ids,fbid){
			//ids  已选择的属性
			//fbid 查找带回的文本框ID,全局唯一
			$.ajax({
					type:"post",
					url:"<?php echo U('Cms/Shop/skuLoader');?>",
					data:{'ids':ids,'fbid':fbid},
					dataType: "json",
					//beforeSend:$.Joel.loading(),
					success:function(mb){
						//$.Joel.loading();
						bootbox.dialog({
	                	message: mb,
	                	title: "商品Sku管理器",
	                	className: "modal-darkorange",
	                	buttons: {
			                    "取消": {
			                        className: "btn-danger",
			                        callback: function () { }
			                    }
		                	}
		            	});
					},
					error:function(xhr){
						$.Joel.alert('danger','通讯失败！请重试！');
					}
			});
			return false;
		}
		$(document).ready(function(){
			initFrame();
			$('#JoelHome').trigger('click');
		
		});
	</script>
</body>
<!--  /Body -->
</html>