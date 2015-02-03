<?php
namespace Admin\Model;
use Think\Model\ViewModel;

/**
 * 日志记录模型
 * 
 */
class LogViewModel extends ViewModel {

    // 注意：
    // 此处的Client和member表，每次只使用其中之一
    // 但此处模型中我们同时获取这2个（Client.client_ID 和 Member.member_ID 难免一样）
    // 采取client_ID从10000起，避开member_ID的范围
    // 然后在查看时再用field字段过滤输出
    public $viewFields = array(
        'Log'=>array('id','oper_CATE','oper_ID','event','cTime'),
        'Client'=>array('name'=>'client_name', '_on'=>'Log.oper_ID=Client.client_ID'),
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