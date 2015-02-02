<?php
namespace Client\Model;
use Think\Model;

/**
 * 意见信息模型
 * 
 */
class OpinionModel extends Model {

	protected $tableName = 'opinion';// 数据表名
	protected $fields 	 = array('grade', 'openid','name','email','phone','content','cTime');// 字段信息
    protected $pk     	 = array('opinion_ID');// 主键

    // 命名范围
    protected $_scope = array(
    	// 命名范围allowUpdate，允许更新的字段
    	'allowUpdateField'=>array(
    		// 'field'=>'一次性，无需更新',
    	),
    );

    // 自动验证
    protected $_validate = array(
		array('grade','check_grade','评分不合法！',self::EXISTS_VALIDATE,'callback',self::MODEL_INSERT),
		array('openid','require','微信id不能为空！',self::MUST_VALIDATE,'',self::MODEL_INSERT),
		array('name','require','姓名不能为空！',self::MUST_VALIDATE,'',self::MODEL_INSERT),
		array('email','email','邮箱格式不正确！',self::EXISTS_VALIDATE,'',self::MODEL_INSERT),
		array('phone','check_Phone','手机号不正确！',self::MUST_VALIDATE,'function',self::MODEL_BOTH),
		array('content','require','内容不能为空！',self::MUST_VALIDATE,'',self::MODEL_INSERT),
	);

    // 自动完成
    protected $_auto = array (
        array('cTime','getDatetime',self::MODEL_INSERT,'function') , // 新增的时候把调用time方法写入当前时间戳
    );

    /**
     * 检查评分合法性
     * @param int $grade 评分值
     * @return bool
     */
    protected function check_grade($grade){

    	if($grade < 0 || $grade > 5){
    		return false;
    	}

    	// 0~5分增合法，0为未评分
    	return true;
    }
}