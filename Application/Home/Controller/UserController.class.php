<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 登录控制器
 */
class UserController extends Controller{

	// 操作者类别
    // const OPER_CATE_CLIENT                       =   0;      //  客户
    const OPER_CATE_RECEPTIONIST                    =   1;      //  前台
    // const OPER_CATE_ADMIN                        =   9;      //  管理员

	/**
     * 前台登录
     * 
     * 前台只能本地固定IP登录
     */
    public function login(){
        // p(session());
    	if (IS_POST) {

    		if (!check_verify(I('post.verify'))) {
    			
    			$this->error('验证码不正确！');// , U('Home/User/login')
    			return;
    		}
    		
    		$one = is_IDCard_exists(I('post.user'), 'member');

            // p($one);die;
    		
			// 密码通过验证，则检查是否存在该成员，且职位position为前台
			if ($one && $one['position'] == self::OPER_CATE_RECEPTIONIST) {

                switch ($one['on_job']) {
                    case 0:
                        $this->error('该成员已离职！');// , U('Home/User/login')
                        break;
                    case 2:
                        $this->error('该成员休假中！');
                        break;
                    case 1:
                        $info['oper_ID'] = $one['member_ID'];
                        $info['oper_CATE'] = self::OPER_CATE_RECEPTIONIST;
                        $info['oper_NAME'] = $one['name'];
                        // 通过验证，合法，写session
                        session('H_USER_V_INFO', $info);
                        session('H_LOGIN_FLAG', true);

                        $Home = A('home'); 
                        $Home->login_log();// 调用Home/Controller/login_log方法
                        
                        $this->success('登录成功！', U('Home/Index/index'));
                        break;
                    default:
                        break;
                }

                return;
				
			}else{

				$this->error('账号或密码不正确！', U('Home/User/login'));
			}
    		
    	}else{

    		$this->display();
    	}
    }

    /**
     * 前台退出
     */
    public function quit(){
        
        session('H_LOGIN_FLAG', null);                
        session('H_USER_V_INFO', null);
        
        redirect(U("Home/User/login"));
    }

    /**
     * 获取验证码
     */
    public function verify(){

    	// 调用function
    	verify();
    }
}
