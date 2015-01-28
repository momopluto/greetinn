<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 日志管理控制器
 */
class LogController extends AdminController {
    
    /**
     * 操作日志列表
     */
    public function lists(){
        
        $this->display();
    }
}