<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 订单控制器
 */
class OrderController extends HomeController {
    
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