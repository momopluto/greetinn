<?php
namespace Client\Controller;
use Think\Controller;

/**
 * 个人中心控制器
 * 
 */
class UserController extends ClientController {
	/**
	 * 个人中心
     * 若未注册，则显示为注册页面
	 */
    public function index(){
        
        $this->display();
    }

    /**
     * 个人资料
     */
    public function info(){

    	$this->display();
    }

    /**
     * 我的订单
     */
    public function myorder(){

    	$this->display();
    }

    /**
     * 取消订单
     */
    public function cancel(){
        
        $this->display();
    }
}