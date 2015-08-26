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

            $rst = validate_login(I('post.'));// 验证前台登录

            if ($rst['errcode'] == 0) {
                $one = is_IDCard_exists(I('post.user'), 'member');

                $last = get_lastShift_record();// 得到最后一条交班记录
                if (!$last) {// 如果没有交班记录，说明为新表
                    // init capital_flow
                    
                    // 资金流水表capital_flow数据
                    // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                    $flow['cTime'] = getDatetime();
                    $flow['shift'] = strtotime($flow['cTime']);// 班次标识
                    $flow['in'] = 0;// 收入
                    $flow['out'] = 0;// 支出
                    $flow['type'] = 0;// 0交班
                    $flow['pay_mode'] = 0;// 支付方式，交班固定为现金
                    $flow['balance'] = 0;// 初始为0
                    $flow['info'] = "[初始资金记录]";
                    $flow['operator'] = $one['name'];// 接班人

                    // p($flow);die;

                    $capital_model = M('capital_flow');
                    if ($capital_model->add($flow) === false) {

                        $this->error('写-初始记录-资金流量表-失败！');
                        return;
                    }else{

                        $last['shift'] = $flow['shift'];
                    }
                    // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                }
                if (strcmp($last['operator'], $one['name']) != 0) {
                    
                    $this->error("最后操作者: [ " . $last['operator'] . " ]未进行交班操作！<br/>请该操作者登录后进行交班操作！",'',5);
                    return;
                }
                $info['shift'] = $last['shift'];
                $info['oper_ID'] = $one['member_ID'];
                $info['oper_CATE'] = self::OPER_CATE_RECEPTIONIST;
                $info['oper_NAME'] = $one['name'];
                // 通过验证，合法，写session
                session('H_USER_V_INFO', $info);
                session('H_LOGIN_FLAG', true);

                $Home = A('home'); 
                $Home->login_log();// 调用Home/Controller/login_log方法

                $this->success('登录成功！', U('Home/Index/index'));
            }else {

                $this->error($rst['errmsg']);
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
