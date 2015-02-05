<?php

namespace Admin\Controller;
use Think\Controller;

/**
 * 后台，公共控制器
 * 
 */
class AdminController extends Controller {

    // 操作内容
    // ***后台
    const ADMIN_LOGIN_IN        	 =   '管理员登录成功';
    const ADMIN_MEMBER_ADD       	 =   '工作人员管理，添加成功';
    const ADMIN_MEMBER_EDIT        	 =   '工作人员管理，更新信息[xxx, xxx]成功';
    const ADMIN_MEMBER_DELETE      	 =   '工作人员管理，成员编号xxx，删除成功';
    const ADMIN_DEVICE_ADD       	 =   '设备管理，添加成功';
    const ADMIN_DEVICE_EDIT        	 =   '设备管理，更新信息成功';
    const ADMIN_DEVICE_DELETE      	 =   '设备管理，删除成功';
    const ADMIN_ROOM_ADD       		 =   '房间管理，添加成功';
    const ADMIN_ROOM_EDIT        	 =   '房间管理，更新信息成功';
    const ADMIN_ROOM_DELETE      	 =   '房间管理，删除成功';

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
			session('A_LOGIN_FLAG', null);
            session('USER_V_INFO', null);
            
			$this->error('您还没有登录，请先登录！', U('Admin/User/login'));
		}
    }

    /**
     * 写登录日志
     */
    function login_log(){
    	
		// 初始化日志信息
		$this->log_model = D('Log');
		
		$info = session('USER_V_INFO');
		$this->log_data['oper_CATE'] = $info['oper_CATE'];
        $this->log_data['oper_ID'] = $info['oper_ID'];

        $this->log_model->startTrans();// 启动事务
        // 写日志
    	$log_Arr = array($this->log_model, $this->log_data, $this->log_model, self::ADMIN_LOGIN_IN, 'login', '');
    	write_log_all_array($log_Arr);
        // write_log_all($this->log_model, $this->log_data, $this->log_model, self::ADMIN_LOGIN_IN, 'login', '');
    }

}
?>