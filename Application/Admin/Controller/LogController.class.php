<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 日志管理控制器
 */
class LogController extends AdminController {

	// 字段范围
	const CLIENT 	= 'id,oper_CATE,oper_ID,event,cTime,client_name';
    const MANAGER 	= 'id,oper_CATE,oper_ID,event,cTime,member_name';

    const CLIENT_OPTION		= "oper_CATE = 0";
    const MANAGER_OPTION	= "oper_CATE = 1 or oper_CATE = 9";
    
    /**
     * 操作日志列表
     */
    public function lists(){
    	// 操作者类别对应的name名，[0]client_name,[1]member_name,[9]member_name
    	$CATE_NAME = array('client_name', 'member_name','','','','','','','','member_name');



    	// echo "操作日志列表，begin<br/>";

        // 客户操作日志
        $logClient_model = D("LogClientView");
        $logData_1 = $logClient_model->field(self::CLIENT)->where(self::CLIENT_OPTION)->order('cTime desc')->select();

        // 工作人员操作日志
        $logManager_model = D("LogManagerView");
        $logData_2 = $logManager_model->field(self::MANAGER)->where(self::MANAGER_OPTION)->order('cTime desc')->select();
        
        // 合并为总
        $logData = array_merge($logData_1, $logData_2);

        // $logData['0'][$CATE_NAME[$logData['0']['oper_CATE']]];// 根据类别取不同字段名
                
        // p($logData);
        // 按cTime降序排序
        usort($logData, 'compare_cTime');
        // p($logData);

    	// echo "操作日志列表，end<br/>";
    	// die;
        
        $this->assign('data', $logData);
        $this->display();
    }
}