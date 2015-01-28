<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 运营统计控制器
 */
class StatisticController extends AdminController {
	/**
	 * 所有订单数据
	 */
    public function lists(){
        
        $this->display();
    }
}