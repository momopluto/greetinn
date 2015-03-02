<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 前台，公共控制器
 * 
 */
class HomeController extends Controller {

	// 操作内容
    // ***前台
    const RECEPTIONIST_LOGIN_IN        		   		=   '前台登录成功';
    const RECEPTIONIST_HELP_REGISTER           		=   '[助]注册成功';
    const RECEPTIONIST_HELP_SUBMIT_ORDER       		=   '[助]下单成功';
    const RECEPTIONIST_CANCEL_PAID_ORDER       		=   '已付款订单，[助]取消成功';
    const RECEPTIONIST_CANCEL_ORDER                 =   '未付款订单，[助]取消成功';
    const RECEPTIONIST_EDIT_ORDER    		   		=   '未入住订单，编辑成功';
    const RECEPTIONIST_CHECK_IN 	   		   		=   '办理入住成功';
    const RECEPTIONIST_CHANGE_ROOM    		   		=   '办理换房成功';
    const RECEPTIONIST_CHECK_OUT    		   		=   '办理退房成功';
    const RECEPTIONIST_HELP_SUBMIT_BORROW_REQUEST   =   '借设备请求，[助]提交成功';
    const RECEPTIONIST_CANCEL_BORROW_REQUEST   		=   '借设备请求，[助]取消成功';
    const RECEPTIONIST_RESPONSE_BORROW_REQUEST  	=   '响应借设备请求';
    const RECEPTIONIST_CONFIRM_RETURN_BORROWED  	=   '确认归还设备';

    // 全局日志模型
	protected $log_model;
	// 全局日志信息数组
	protected $log_data = array();
	

	/**
	 * 空操作，用于输出404页面
	 */
	public function _empty(){
		// redirect("http://www.qq.com/404");
		
	}
	

	/**
	 * 初始化
	 */
	function _initialize() {

		if(!is_login()){
            // 未登录
            session('H_LOGIN_FLAG', null);
            session('H_USER_V_INFO', null);
            
            $this->error('您还没有登录，请先登录！', U('Home/User/login'));
            return;
        }

        if ($info = session('H_USER_V_INFO')) {
            // 初始化日志基本信息
            
            $this->log_data['oper_CATE'] = $info['oper_CATE'];
            $this->log_data['oper_ID'] = $info['oper_ID'];

            $this->log_model = D('Log');
        }
    }

    /**
     * 写登录日志
     */
    function login_log(){
    	
		$this->log_model->startTrans();// 启动事务
        // 写日志
        $log_Arr = array($this->log_model, $this->log_data, $this->log_model, self::RECEPTIONIST_LOGIN_IN, 'login', array('操作者id' => $this->log_data['oper_ID'], '登录IP' => get_client_ip()));
        write_log_all_array($log_Arr);
        
        // p($this->log_data);
        // die;
    }

}
?>