<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 前台，公共控制器
 * 
 */
class HomeController extends Controller {

	// 操作者类别
    // const OPER_CATE_CLIENT          				=   0;      //  客户
    const OPER_CATE_RECEPTIONIST    				=   1;      //  前台
    // const OPER_CATE_ADMIN           				=   9;      //  管理员

	// 操作内容
    // ***前台
    const RECEPTIONIST_LOGIN_IN        		   		=   '前台登录成功';
    const RECEPTIONIST_HELP_REGISTER           		=   '前台[助]注册成功';
    const RECEPTIONIST_HELP_SUBMIT_ORDER       		=   '前台[助]下单成功';
    const RECEPTIONIST_CANCEL_PAID_ORDER       		=   '已付款订单，前台取消成功';
    const RECEPTIONIST_CANCEL_ORDER    		   		=   '未付款订单，前台取消成功';
    const RECEPTIONIST_CHECK_IN 	   		   		=   '前台办理入住成功';
    const RECEPTIONIST_CHANGE_ROOM    		   		=   '前台办理换房成功';
    const RECEPTIONIST_CHECK_OUT    		   		=   '前台办理退房成功';
    const RECEPTIONIST_HELP_SUBMIT_BORROW_REQUEST   =   '借设备请求，前台[助]提交成功';
    const RECEPTIONIST_CANCEL_BORROW_REQUEST   		=   '借设备请求，前台取消成功';
    const RECEPTIONIST_RESPONSE_BORROW_REQUEST  	=   '借设备请求，前台响应请求';
    const RECEPTIONIST_CONFIRM_RETURN_BORROWED  	=   '借设备请求，前台确认归还';

    // 全局日志模型
	protected $log_model;

	

	/**
	 * 空操作，用于输出404页面
	 */
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
	

	//初始化操作
	function _initialize() {
		
		$this->log_model = D('Log');

		// /* 用户登录检测 */
		//  if(!is_login()){
		// 	// session(null);
		// 	session('login_flag', null);
  //           session('uid', null);
		// 	$this->error('您还没有登录，请先登录！', U('Home/User/login'));
		// }
    }
    
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