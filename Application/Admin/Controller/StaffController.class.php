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

    /**
     * 添加工作人员
     */
    public function add(){
        
        $this->display();
    }

    /**
     * 编辑工作人员
     */
    public function edit(){
        
        $this->display();
    }

    /**
     * 删除人员列表
     */
    public function del(){
        
        $this->display();
    }
}