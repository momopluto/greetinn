<?php
namespace Client\Model;
use Think\Model;

/**
 * 订单记录模型
 * 
 */
class OrderRecordModel extends Model {

	protected $tableName = 'o_record';// 数据表名


	protected $_scope = array(
         // 命名范围cancel
         'cancel'=>array(
             'where'=>array('status'=>0),
         ),
         // 命名范围havePay
         'havePay'=>array(
             'where'=>array('status'=>2),
         ),
         // 命名范围occpuy
         'occpuy'=>array(
             'where'=>array('status'=>3),
         ),
         // 命名范围leave
         'leave'=>array(
             'where'=>array('status'=>4),
         ),
     );

}