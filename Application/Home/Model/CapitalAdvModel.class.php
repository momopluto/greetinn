<?php
namespace Home\Model;
use Think\Model\AdvModel;

/**
 * 资金记录模型
 * 
 */
class CapitalAdvModel extends AdvModel {

	protected $tableName = 'capital_flow';// 数据表名
    protected $fields    = array('id','shift','cTime','in','out','type',
                            'balance','info','pay_mode','operator');// 字段信息
    protected $pk        = 'id';// 主键
}