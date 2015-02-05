<?php

namespace Admin\Controller;
use Think\Controller;

/**
 * 登录控制器
 */
class UserController extends Controller{

	// Admin账号信息
	// const ADMIN_USER     =   '7ed46fa1c4c38a738b9534fc7bd11f8c';
	const ADMIN_PWD    	 =   'b199af2a73eac17d94ffac6d998b31dc';

	// 操作者类别
    // const OPER_CATE_CLIENT        =   0;      //  客户
    // const OPER_CATE_RECEPTIONIST  =   1;      //  前台
    const OPER_CATE_ADMIN            =   9;      //  管理员

	/**
     * 管理员登录
     */
    public function login(){
    	if (IS_POST) {

    		if (!check_verify(I('post.verify'))) {
    			
    			$this->error('验证码不正确！', U('Admin/User/login'));
    			return;
    		}

    		if (md5(I('post.pwd')) != self::ADMIN_PWD) {
    			$this->error('账号或密码不正确！', U('Admin/User/login'));
    			return;
    		}
    		
    		$one = is_IDCard_exists(I('post.user'), 'member');
    		
			// 密码通过验证，则检查是否存在该成员，且职位position为管理员
			if ($one && $one['position'] == self::OPER_CATE_ADMIN) {
				
				$info['oper_ID'] = $one['member_ID'];
				$info['oper_CATE'] = self::OPER_CATE_ADMIN;
				// 通过验证，合法，写session
				session('USER_V_INFO', $info);
				session('A_LOGIN_FLAG', true);

				$Admin = A('admin'); 
				$Admin->login_log();// 调用Admin/Controller/login_log方法
				
				$this->success('登录成功！', U('Admin/Index/index'));
			}else{

				$this->error('账号或密码不正确！', U('Admin/User/login'));
			}
    		
    	}else{

    		$this->display();
    	}
    }

    /**
     * 管理员退出
     */
    public function quit(){
        
        session('A_LOGIN_FLAG', null);                
        session('USER_V_INFO', null);
        
        redirect(U("Admin/User/login"));
    }

    /**
     * 获取验证码
     */
    public function verify(){

    	// 调用function
    	verify();
    }
}
