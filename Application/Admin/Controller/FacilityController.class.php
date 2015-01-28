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
	 * 设备列表
	 */
    public function device(){
        
        $this->display();
    }
}