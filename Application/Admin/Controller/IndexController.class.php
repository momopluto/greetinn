<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 主页管理控制器
 */
class IndexController extends AdminController {

	/**
	 * 主页
	 */
    public function index(){
        
        $this->display();
    }
}