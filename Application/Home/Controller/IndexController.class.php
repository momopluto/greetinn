<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 主页控制器
 */
class IndexController extends HomeController {

	/**
	 * 主页
	 */
    public function index(){
        
        $this->display();
    }
}