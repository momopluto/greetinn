<?php
namespace Admin\Model;
use Think\Model;

/**
 * 日志记录模型
 * 
 */
class LogModel extends Model {

	protected $tableName = 'log';// 数据表名
    protected $fields    = array('id','oper_CATE','oper_ID','event','cTime');// 字段信息
    protected $pk        = 'id';// 主键

    // 自动验证
    protected $_validate = array(
        array('oper_CATE','require','操作者类别不能为空！',self::MUST_VALIDATE,'',self::MODEL_INSERT),
        array('oper_ID','require','操作者id不能为空！',self::MUST_VALIDATE,'',self::MODEL_INSERT),
        array('oper_ID','check_operID','操作者id不正确！',self::MUST_VALIDATE,'callback',self::MODEL_INSERT),
        array('event','require','房间号不能为空！',self::MUST_VALIDATE,'',self::MODEL_INSERT),
    );

    // 自动完成
    protected $_auto = array (
        array('cTime','getDatetime',self::MODEL_INSERT,'function') , // 新增的时候把调用time方法写入当前时间戳
    );

    /**
     * 检查操作者id是否合法
     * @param string $oper_ID 操作者id
     * @return bool
     */
    function check_operID($oper_ID){

        $IDs = array();
        switch ($this->checkData['oper_CATE']) {
            case 0:// 客户
                $IDs = M('client')->getField('client_ID', true);
                break;
            case 1:// 前台
            case 9:// 管理员
                $IDs = M('member')->getField('member_ID', true);
                break;
            default:
                return false;// 操作者类别不存在
        }

        // p($IDs);

        if (in_array($oper_ID, $IDs)) {
            
            return true;
        }

        echo "操作者类别:".$this->checkData['oper_CATE']." ---操作者ID:".$oper_ID."不存在！<br/>";
        return false;
    }

}