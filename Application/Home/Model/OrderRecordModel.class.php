<?php
namespace Home\Model;
use Think\Model;

/**
 * 订单记录模型
 * 
 */
class OrderRecordModel extends Model {

	protected $tableName = 'o_record';// 数据表名
    protected $fields    = array('o_id','client_ID','book_info','style','type','source','a_id','g_id','pay_mode','price','deposit','phone','status','cTime');// 字段信息
    protected $pk        = 'o_id';// 主键

    protected $_scope = array(
        // 命名范围allowUpdate，允许更新的字段
        'allowUpdateField'=>array(
            'field'=>'book_info,type,source,a_id,g_id,pay_mode,price,deposit,phone,status',
        ),

        // 命名范围cancel
        'cancel'=>array(
            'where'=>array('status'=>0),
        ),
        // 命名范围new
        'new'=>array(
            'where'=>array('status'=>1),
        ),
        // 命名范围pay
        'pay'=>array(
            'where'=>array('status'=>2),
        ),
        // 命名范围checkIn
        'checkIn'=>array(
            'where'=>array('status'=>3),
        ),
        // 命名范围checkOut
        'checkOut'=>array(
            'where'=>array('status'=>4),
        ),
    );

    // 自动验证
    protected $_validate = array(
        array('client_ID','check_Client','用户id不存在！',self::EXISTS_VALIDATE,'function',self::MODEL_INSERT),
        array('phone','check_Phone','手机号不正确！',self::EXISTS_VALIDATE,'function',self::MODEL_BOTH),
        array('book_info','check_BookInfo','订单信息不合法!',self::EXISTS_VALIDATE,'function',self::MODEL_BOTH),
        array('price','check_Price','价钱非负！',self::EXISTS_VALIDATE,'function',self::MODEL_INSERT),
        array('status','check_status','记录状态不合法！',self::EXISTS_VALIDATE,'callback',self::MODEL_UPDATE),// 更新的时候检查：status只能为1或0或2
    );

    // 自动完成
    protected $_auto = array (
        // array('status','1',self::MODEL_INSERT,'string'),  // 新增的时候status=1
        array('status','getStatus',self::MODEL_INSERT,'callback'),  // 新新增时，判断status是否给出
        array('cTime','getDatetime',self::MODEL_INSERT,'function') , // 新增的时候把调用time方法写入当前时间戳
    );

    /*
    入住信息（JSON格式）
        {
            "入住晚数": "1",

            "入住日期": "xxxxxx",
            "离开日期": "xxxxxx",

            "入住人数": "2",
            "入住人信息": [{
                "name": "王一",
                "ID_card": "520222196306159670"
            }, {
                "name": "李二",
                "ID_card": "230230197409256612"
            }],
            "备注": "2对耳塞"
        }
    */

    /**
     * 更新时，检查status是否合法
     * @param int $status 状态值
     * @return bool
     */
    protected function check_status($status){

        if ($status == 0 || $status == 1 || $status == 2 || $status == 3 || $status == 4) {
            // echo "right<br/>";
            return true;
        }

        // echo "wrong<br/>";
        return false;
    }

    /**
     * 新增时，判断status是否给出
     * @param int $status 状态值
     * @return bool
     */
    protected function getStatus($status){

        if (isset($status)) {// 给出
            return $status;
        }else{// 默认为1
            return 1;
        }
    }

}