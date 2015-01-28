<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 前台，公共控制器
 * 
 */
class HomeController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		// redirect("http://www.qq.com/404");
		
	}

	/**
	 * 前台登录
	 * 
	 * 前台只能本地固定IP登录
	 * 只允许1位前台登录，另一个前台登录会自动将之前的前台挤下线
	 */
	public function login(){
		
		$this->display();
	}
	

	// //初始化操作
	// function _initialize() {
	// 	/* 用户登录检测 */
	// 	 if(!is_login()){
	// 		// session(null);
	// 		session('login_flag', null);
 //            session('uid', null);
	// 		$this->error('您还没有登录，请先登录！', U('Home/User/login'));
	// 	}
 //    }
    
 //    // 退出
 //    public function quit(){    
 //        // session(null);
 //        session('login_flag', null);                
 //        session('uid', null);
 //        session('isOpen', null);
 //        redirect(U("Home/User/login"));
 //    }

}
?>