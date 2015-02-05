<?php
namespace Admin\Model;
use Think\Model\ViewModel;

/**
 * 客户日志记录模型
 * 
 */
class LogClientViewModel extends ViewModel {

    // 注意：
    // 此处的Client和member表
    // 采取client_ID从10000起，避开member_ID的范围
    // 然后在查看时再用field字段过滤输出
    public $viewFields = array(
        'Log'=>array('id','oper_CATE','oper_ID','event','cTime'),
        'Client'=>array('name'=>'client_name', '_on'=>'Log.oper_ID=Client.client_ID'),
        // 'Member'=>array('name'=>'member_name', '_on'=>'Log.oper_ID=Member.member_ID'),
    );

/*
 Array
(
    [1] => Array
        (
            [id] => 60
            [oper_CATE] => 0
            [oper_ID] => 10001
            [event] => 自主下单成功，订单id: xxx
            [cTime] => 2015-02-02 03:24:14

            [client_name] => 刘恩坚
        )
)
*/

}