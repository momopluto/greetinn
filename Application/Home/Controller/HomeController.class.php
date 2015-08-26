<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 前台，公共控制器
 * 
 */
class HomeController extends Controller {

    // 资金管理开关
    const MONEY_MANAGEMENT_SWITCH = 1;// O关闭，1开启
    /*
    后台可以管理分类
    分类：
        0交班(平时不可见，只有交班时再会使用到此分类)
        1房费
        2租金
        3押金
        4退押
        5商品售卖
        6会员卡办理
        7会员卡充值
    详情：
        交班：应有余额xx，实际金额xx，差额xx
        房费、租金、押金、退押：房间号
        商品售卖：售卖物品列表。
        会员卡办理、会员卡充值：会员卡号

     */

    // "高级"特殊价，验证密码
    const SPEC_PWD = "663ab0c7fac6285832519dc1a3b8c0a6";

	// 操作内容
    // ***前台
    const RECEPTIONIST_LOGIN_IN        		   		=   '前台登录成功';
    const RECEPTIONIST_HELP_REGISTER           		=   '[助]注册成功';
    const RECEPTIONIST_HELP_SUBMIT_ORDER       		=   '[助]下单成功';
    const RECEPTIONIST_CANCEL_PAID_ORDER       		=   '已付款订单，[助]取消成功';
    const RECEPTIONIST_CANCEL_ORDER                 =   '未付款订单，[助]取消成功';
    const RECEPTIONIST_EDIT_ORDER    		   		=   '未入住订单，编辑成功';
    const RECEPTIONIST_CHECK_IN                     =   '办理入住成功';
    const RECEPTIONIST_STAY_OVER 	   		   		=   '办理续住成功';
    const RECEPTIONIST_CHANGE_ROOM    		   		=   '办理换房成功';
    const RECEPTIONIST_CHECK_OUT                    =   '办理退房成功';
    const RECEPTIONIST_CLEARED      		   		=   '空净房间成功';
    const RECEPTIONIST_HELP_SUBMIT_BORROW_REQUEST   =   '借设备请求，[助]提交成功';
    const RECEPTIONIST_CANCEL_BORROW_REQUEST   		=   '借设备请求，[助]取消成功';
    const RECEPTIONIST_RESPONSE_BORROW_REQUEST  	=   '响应借设备请求';
    const RECEPTIONIST_CONFIRM_RETURN_BORROWED      =   '确认归还设备';
    const RECEPTIONIST_OPEN_VIP                     =   '开通会员成功';
    const RECEPTIONIST_RECHARGE_VIP                 =   '充值会员成功';
    const RECEPTIONIST_SELL_GOOD                    =   '商品售卖成功';
    const RECEPTIONIST_RENT_THING                   =   '物品出租成功';

    // 订单类型
    const STYLE_0                     =   0;// 普通
    const STYLE_1                     =   1;// 钟点
    const STYLE_2                     =   2;// 团购
    const STYLE_3                     =   3;// (节假日)普通

    const IN_TIME                     =   " 14:00:00";// 入住时间
    const OUT_TIME                    =   " 12:00:00";// 离店时间
    const OUT_TIME_2                  =   " 13:00:00";// 会员离店时间

    // 会员卡记录类型
    const VIP_STYLE_0                 =   0;// 注销
    const VIP_STYLE_1                 =   1;// 开通
    const VIP_STYLE_2                 =   2;// 充值
    const VIP_STYLE_3                 =   3;// 赠送
    const VIP_STYLE_4                 =   4;// 消费

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
     * [登录时有使用]写登录日志
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