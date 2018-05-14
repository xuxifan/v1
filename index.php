<?php
// +----------------------------------------------------------------------
// | JoelCMS V1.0 Beta
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.JoelCMS.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Joel <2094157689@qq.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
//var_dump($_SERVER);
/*$host=explode('.', $_SERVER["HTTP_HOST"]);
if(strtolower($host[0])!='www'){
	header("location:".$_SERVER["REQUEST_SCHEME"].'://www.'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
}*/
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.3 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);
// 定义应用和框架目录
define('APP_PATH','./Joel/');
define('THINK_PATH',realpath('./Joel/_Core').'/');
// 定义运行时目录
define('RUNTIME_PATH','./Runtime/');
// 引入ThinkPHP入口文件
require THINK_PATH.'ThinkPHP.php';
