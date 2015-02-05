<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 意见控制器
 */
class OpinionController extends HomeController {

	/**
     * 意见列表
     */
    public function lists(){

    	$model = M('opinion');

    	$data = $model->order('cTime desc')->select();
        
    	// p($data);

        $this->assign('data', $data);
        $this->display();
    }
}