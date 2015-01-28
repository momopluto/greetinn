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
}