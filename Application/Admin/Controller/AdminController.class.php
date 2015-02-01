<?php

namespace Admin\Controller;
use Think\Controller;

/**
 * 后台，公共控制器
 * 
 */
class AdminController extends Controller {

	// 操作者类别
    // const OPER_CATE_CLIENT        =   0;      //  客户
    // const OPER_CATE_RECEPTIONIST  =   1;      //  前台
    const OPER_CATE_ADMIN            =   9;      //  管理员

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
	// 全局日志模型
	protected $log_data = array();

	

	/**
	 * 空操作，用于输出404页面
	 */
	public function _empty(){
		// redirect("http://www.qq.com/404");
		
	}

	/**
	 * 管理员登录
	 */
	public function login(){

		$this->display();
	}

	//初始化操作
	function _initialize() {

		$this->log_model = D('Log');

		$this->log_data['oper_CATE'] = self::OPER_CATE_ADMIN;
        $this->log_data['oper_ID'] = '1';// session('ADMIN_ID');

		// /* 用户登录检测AMDMIN_ID */
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