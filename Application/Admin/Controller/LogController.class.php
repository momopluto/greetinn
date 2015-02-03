<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 日志管理控制器
 */
class LogController extends AdminController {

	// 字段范围
	const CLIENT_LOG 	= 'id,oper_CATE,oper_ID,event,cTime,client_name';
    const MANAGER_LOG 	= 'id,oper_CATE,oper_ID,event,cTime,member_name';

    const CLIENT_OPTION		= "oper_CATE = 0";
    const MANAGER_OPTION	= "oper_CATE = 1 or oper_CATE = 9";
    
    /**
     * 操作日志列表
     */
    public function lists(){
    	// 操作者类别对应的name名，[0]client_name,[1]member_name,[9]member_name
    	$CATE_NAME = array('client_name', 'member_name','','','','','','','','member_name');





    	echo "操作日志列表，begin<br/>";

    	



        $log_model = D("LogView");
        // $logData = $log_model->order('cTime desc')->select();
        // $logData = $log_model->field(self::CLIENT_LOG)->where(self::CLIENT_OPTION)->order('cTime desc')->select();
        $logData = $log_model->field(self::MANAGER_LOG)->where(self::MANAGER_OPTION)->order('cTime desc')->select();
        // ->where("oper_CATE = 9")
        p($CATE_NAME);
        echo "-=-=-=".$logData['0'][$CATE_NAME[$logData['0']['oper_CATE']]];

        p($logData);

        p($log_model);

    	echo "操作日志列表，end<br/>";
    	die;
        
        $this->display();
    }
}