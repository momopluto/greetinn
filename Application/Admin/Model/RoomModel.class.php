<?php
namespace Admin\Model;
use Think\Model;

/**
 * 客房间信息模型
 * 
 */
class RoomModel extends Model {

	protected $tableName = 'room';// 数据表名
    protected $fields    = array('r_id','room_ID','price','type','desc','is_open','cTime');// 字段信息
    protected $pk        = 'r_id';// 主键

    // 自动验证
    protected $_validate = array(
        array('room_ID','require','房间号不能为空！',self::MUST_VALIDATE,'',self::MODEL_INSERT),// 新增时，不管字段是否存在，必须验证
        array('room_ID','require','房间号不能为空！',self::EXISTS_VALIDATE,'',self::MODEL_UPDATE),// 更新时，如果存在字段，验证
        // array('room_ID','','房间号已存在！',self::EXISTS_VALIDATE,'unique',self::MODEL_BOTH), 
        array('room_ID','check_room','房间号已存在！',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH),
        array('price','check_Price','价钱非负！',self::EXISTS_VALIDATE,'function',self::MODEL_BOTH),
    );

    // 自动完成
    protected $_auto = array (
        array('is_open','1',self::MODEL_INSERT,'string'),  // 新增的时候is_open=1
        array('cTime','getDatetime',self::MODEL_INSERT,'function') , // 新增的时候把调用time方法写入当前时间戳
    );

    /**
     * 检查房间号
     * @param string $room_ID 房间号
     * @return bool 不存在返回true，存在返回false
     */
    function check_room($room_ID){

        $room_model = M('room');

        $rs = $room_model->where(array("room_ID" => $room_ID))->find();

        if ($rs) {
            if ($rs['r_id'] == $this->checkData['r_id']) {
                // 防止修改时，被'自己'限制
                return true;
            }
            return false;
        }else{

            return true;
        }
    }

}