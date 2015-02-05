<?php
namespace Admin\Model;
use Think\Model\ViewModel;

/**
 * 工作人员日志记录模型
 * 
 */
class LogManagerViewModel extends ViewModel {

    // 注意：
    // 此处的Client和member表
    // 采取client_ID从10000起，避开member_ID的范围
    // 然后在查看时再用field字段过滤输出
    public $viewFields = array(
        'Log'=>array('id','oper_CATE','oper_ID','event','cTime'),
        // 'Client'=>array('name'=>'client_name', '_on'=>'Log.oper_ID=Client.client_ID'),
        'Member'=>array('name'=>'member_name', '_on'=>'Log.oper_ID=Member.member_ID'),
    );

/*
 Array
(
    [0] => Array
        (
            [id] => 61
            [oper_CATE] => 9
            [oper_ID] => 1
            [event] => 房间管理，更新信息成功，房间id: 1，[room_ID=>228 test edit2, price=>238, type=>1]
            [cTime] => 2015-02-03 12:37:51
            [member_name] => 刘恩坚
        )
)
*/

}