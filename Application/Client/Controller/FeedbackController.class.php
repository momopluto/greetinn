<?php
namespace Client\Controller;
use Think\Controller;

/**
 * 意见反馈控制器
 * 
 */
class FeedbackController extends ClientController {

	/**
	 * 反馈意见
	 */
    public function detail(){
    	
        $this->display();
    }
}