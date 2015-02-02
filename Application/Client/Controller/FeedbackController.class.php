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

    	echo "hihihi<br/>";

    	/*$data['grade'] = '5';
    	$data['openid'] = 'jkkqwhwqijnbhdufsa';
    	$data['name'] = 'momop';
    	$data['email'] = '294666880@qq.com';
    	$data['phone'] = '18826481053';
    	$data['content'] = '这这这就是我要说的话话话！！！！！';

    	$test_model = D('Opinion');

    	if ($test_model->create($data)) {

    		echo "create成功<br/>";
    		echo $test_model->cTime."<br/>";

    		// $test_model->add();
    	}else{

    		echo "create失败<br/>";
    		echo $test_model->getError();
    	}*/

    	echo "byebyebye<br/>";
    	die;
    	
        $this->display();
    }
}