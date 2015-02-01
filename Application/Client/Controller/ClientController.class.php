<?php

namespace Client\Controller;
use Think\Controller;

/**
 * 客户，公共控制器
 * 
 */
class ClientController extends Controller {

	// 操作者类别
    const OPER_CATE_CLIENT           	 =   0;      //  客户
    // const OPER_CATE_RECEPTIONIST      =   1;      //  前台
    // const OPER_CATE_ADMIN             =   9;      //  管理员

	// 操作内容
    // ***客户
    const CLIENT_REGISTER        		 =   '自主注册成功';
    const CLIENT_SUBMIT_ORDER    		 =   '自主下单成功';
    const CLIENT_PAY_ORDER     			 =   '未付款订单，网上支付成功';
    const CLIENT_CANCEL_ORDER    		 =   '未付款订单，自主取消成功';
    const CLIENT_SUBMIT_BORROW_REQUEST   =   '借设备请求，自主提交成功';
    const CLIENT_CANCEL_BORROW_REQUEST   =   '借设备请求，自主取消成功';

    // 全局日志模型
	protected $log_model;

    

	/**
	 * 空操作，用于输出404页面
	 */
	public function _empty(){
		// redirect("http://www.qq.com/404");
		
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