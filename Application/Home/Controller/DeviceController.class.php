<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 设备管理控制器
 */
class DeviceController extends HomeController {
    
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