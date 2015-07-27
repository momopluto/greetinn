<?php
namespace Home\Model;
use Think\Model;

/**
 * 会员模型
 * 
 */
class VipModel extends Model {

	protected $tableName = 'vip';// 数据表名
    protected $fields    = array('client_ID','card_ID','birthday','balance',
                            /*'single_count','single_free','single_latestDate',
                            'double_count','double_free','double_latestDate',
                            'multiple_count','multiple_free','multiple_latestDate',*/
                            'first_free','cTime');// 字段信息
    protected $pk        = 'client_ID';// 主键

    protected $_scope = array(

        // 命名范围changeCard，换会员卡
        'changeCard'=>array(
            'field'=>'card_ID',
        ),
        // 命名范围recharge，充值
        'recharge'=>array(
            'field'=>'balance',
        ),
        // 命名范围firstFree，首住免费
        'firstFree'=>array(
            'field'=>'first_free',
        ),

        // // 命名范围single，单人间
        // 'single'=>array(
        //     'field'=>'single_count,single_free,single_latestDate',
        // ),
        // // 命名范围double，双人间
        // 'double'=>array(
        //     'field'=>'double_count,double_free,double_latestDate',
        // ),
        // // 命名范围multiple，复式
        // 'multiple'=>array(
        //     'field'=>'multiple_count,multiple_free,multiple_latestDate',
        // ),
    );

    // 自动验证
    protected $_validate = array(
        array('client_ID','unique_Client','该客户已注册会员！',self::EXISTS_VALIDATE,'callback',self::MODEL_INSERT),
        array('card_ID','','此会员卡已被使用！',self::EXISTS_VALIDATE,'unique',self::MODEL_INSERT),
    );

    // 自动完成
    protected $_auto = array (
        array('balance','200',self::MODEL_INSERT,'string'),  // 新增的时候balance=200
        array('first_free','1',self::MODEL_INSERT,'string'),  // 新增的时候first_free=1
        array('cTime','getDatetime',self::MODEL_INSERT,'function') , // 新增的时候把调用time方法写入当前时间戳
    );

    /**
     * 插入时，检查client_ID是否已存在表中(已注册会员)
     * @param int $client_ID 状态值
     * @return bool
     */
    protected function unique_Client($client_ID){

        if (M('vip')->find($client_ID)){
            // 已注册
            return false;
        }

        return true;
    }

}