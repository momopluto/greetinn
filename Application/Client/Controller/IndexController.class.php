<?php
namespace Client\Controller;
use Think\Controller;

/**
 * Client，默认index控制器
 * 
 */
class IndexController extends ClientController {

	/**
	 * 首页
	 */
    public function index(){
    	
    	$this->display();
    }
}