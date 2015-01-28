<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 设施管理控制器
 */
class FacilityController extends AdminController {

	/**
	 * 房间列表
	 */
	public function room(){

		$this->display();
	}

    /**
     * 添加房间
     */
    public function addR(){
        
        $this->display();
    }

    /**
     * 编辑房间
     */
    public function editR(){
        
        $this->display();
    }

    /**
     * 删除房间
     */
    public function delR(){
        
        $this->display();
    }


    /**
	 * 设备列表
	 */
    public function device(){
        
        $this->display();
    }

    /**
     * 添加设备
     */
    public function addD(){
        
        $this->display();
    }

    /**
     * 编辑设备
     */
    public function editD(){
        
        $this->display();
    }

    /**
     * 删除设备
     */
    public function delD(){
        
        $this->display();
    }
}