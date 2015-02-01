<?php
namespace Client\Model;
use Think\Model;

/**
 * 借还设备记录模型
 * 
 */
class DeviceRecordModel extends Model {

	protected $tableName = 'd_record';// 数据表名
	protected $fields 	 = array('d_id', 'o_id','device_IDs','price','openid','status','cTime',
			// '_type'=>array('d_id'=>'int','o_id'=>'int','device_IDs'=>'text','price'=>'int','openid'=>'varchar','status'=>'tinyint','cTime'=>'datetime')
		);// 字段信息
    protected $pk     	 = 'd_id';// 主键

    protected $_scope = array(

        // 命名范围allowUpdate，允许更新的字段
        'allowUpdateField'=>array(
            'field'=>'device_IDs,price,status',
        ),

        // 命名范围cancel
        'cancel'=>array(
            'where'=>array('status'=>0),
        ),
        // 命名范围new
        'new'=>array(
            'where'=>array('status'=>1),
        ),
        // // 命名范围response
        // 'response'=>array(
        //     'where'=>array('status'=>2),
        // ),
        // // 命名范围return
        // 'return'=>array(
        //     'where'=>array('status'=>3),
        // ),
    );

    // 自动验证
    protected $_validate = array(
        array('device_IDs','check_DeviceIn','选择的设备信息不正确!',self::EXISTS_VALIDATE,'function',self::MODEL_BOTH),
        array('price','check_Price','价钱非负！',self::EXISTS_VALIDATE,'function',self::MODEL_BOTH),
        array('openid','require','微信id不能为空！',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('status','check_status','记录状态不合法！',self::EXISTS_VALIDATE,'callback',self::MODEL_UPDATE),// 更新的时候检查：status只能为1或0
    );

    // 自动完成
    protected $_auto = array (
        array('status','1',self::MODEL_INSERT,'string'),  // 新增的时候status=1
        array('cTime','getDatetime',self::MODEL_INSERT,'function') , // 新增的时候把调用time方法写入当前时间戳
    );

    // 
    /**
     * 更新时，检查status是否合法
     * @param int $status 状态值
     * @return bool
     */
    protected function check_status($status){

        // 限制用户操作的status只能在 0取消 和 1新建 之间切换
        // 实际上，更严格的话，只能允许status从 1新建 -> 0取消，0取消 不能-> 1新建
        // 此处为宽松限制

        if ($status == 0 || $status == 1) {
            // echo "right<br/>";
            return true;
        }

        // echo "wrong<br/>";
        return false;
    }
}