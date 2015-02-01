<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 订单控制器
 */
class OrderController extends HomeController {

    // 操作状态
    const STATUS_CANCEL          =   0;      //  状态值，取消
    const STATUS_NEW             =   1;      //  状态值，新建
    const STATUS_PAY             =   2;      //  状态值，支付
    const STATUS_CHECKIN         =   3;      //  状态值，入住
    const STATUS_CHECKOUT        =   4;      //  状态值，退房
    
    /**
     * 未完成订单
     */
    public function dealing(){
        
        $this->display();
    }

    /**
     * 已完成订单
     */
    public function complete(){
        
        $this->display();
    }

    /**
     * [助]客户下单
     */
    public function detail(){
        
        $this->display();
    }

    /**
     * 取消订单
     */
    public function cancel(){
        
        $this->display();
    }

    /**
     * 办理入住
     */
    public function check_in(){
        
        $this->display();
    }

    /**
     * 办理换房
     */
    public function change_room(){
        
        $this->display();
    }

    /**
     * 办理退房
     */
    public function check_out(){
        
        $this->display();
    }
}