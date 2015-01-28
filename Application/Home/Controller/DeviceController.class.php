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

    /**
     * 设备清单，[助]客户借设备
     */
    public function lists(){
        
        $this->display();
    }

    /**
     * 取消申请
     */
    public function cancel(){
        
        $this->display();
    }

    /**
     * 响应请求
     */
    public function response(){
        
        $this->display();
    }

    /**
     * 确认归还
     */
    public function return(){
        
        $this->display();
    }
    
}