S<?php

/**
 * 微信接入验证
 * 在入口进行验证而不是放到框架里验证，主要是解决验证URL超时的问题
 */
if (! empty ( $_GET ['echostr'] ) && ! empty ( $_GET ["signature"] ) && ! empty ( $_GET ["nonce"] )) {
    $signature = $_GET ["signature"];
    $timestamp = $_GET ["timestamp"];
    $nonce = $_GET ["nonce"];

    $tmpArr = array (
        'greetinn',
        $timestamp,
        $nonce
    );
    sort ( $tmpArr, SORT_STRING );
    $tmpStr = sha1 ( implode ( $tmpArr ) );

    if ($tmpStr == $signature) {
        echo $_GET ["echostr"];
    }
    exit ();
}


	define('APP_DEBUG', true);

	// // 绑定Client模块到当前入口文件
	// define('BIND_MODULE','Client');
	// define('BUILD_CONTROLLER_LIST','Index,User,Order,Device,Feedback');
	// define('BUILD_MODEL_LIST','Client,O_record,D_record,Opinion');

    // // 绑定Home模块到当前入口文件
    // define('BIND_MODULE','Home');
    // define('BUILD_CONTROLLER_LIST','Index,Client,Order,Device,Opinion');
    // // define('BUILD_MODEL_LIST','Index');

    // // 绑定Admin模块到当前入口文件
    // define('BIND_MODULE','Admin');
    // define('BUILD_CONTROLLER_LIST','Index,Staff,Statistic,Facility,Opinion,Log');
    // define('BUILD_MODEL_LIST','Member,Room,Deivce');

    define('APP_PATH', './Application/');
    define('THINK_PATH', './ThinkPHP/');

    // define('DOMAIN_URL', "http://momopluto.xicp.net");//服务器域名
    
    define('DOMAIN_URL', "http://127.0.0.1:8080");//服务器域名
    define('PUBLIC_URL', '/greetinn/Application/Public');//Public公共文件夹路径
    
    define('ADMIN_SRC', '/greetinn/Application/Admin/Source');//Admin资源文件夹路径
    define('HOME_SRC', '/greetinn/Application/Home/Source');//Home资源文件夹路径
    define('CLIENT_SRC', '/greetinn/Application/CLient/Source');//CLient资源文件夹路径

    define('ADMIN_TITLE', '归客驿站后台管理');
    define('HOME_TITLE', '归客驿站前台系统');
    define('SYS_NAME', '归客驿站系统');


    require THINK_PATH.'ThinkPHP.php'

?>