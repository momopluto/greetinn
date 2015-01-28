<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 人员管理控制器
 */
class StaffController extends AdminController {

	/**
	 * 客户列表
	 */
    public function client(){
        
        $this->display();
    }
    
    /**
     * 工作人员列表
     */
    public function member(){
        
        $this->display();
    }
}