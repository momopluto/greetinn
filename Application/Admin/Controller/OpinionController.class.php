<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 意见管理控制器
 */
class OpinionController extends AdminController {
    
    /**
     * 意见列表
     */
    public function lists(){
        
        $this->display();
    }
}