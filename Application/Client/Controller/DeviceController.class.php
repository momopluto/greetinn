<?php
namespace Client\Controller;
use Think\Controller;

/**
 * 设备管理控制器
 * 
 */
class DeviceController extends ClientController {

	/**
	 * 设备清单
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
     * 历史记录
     */
    public function history(){
    	
    	$this->display();
    }
}