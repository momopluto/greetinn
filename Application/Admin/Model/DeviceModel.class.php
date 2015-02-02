<?php
namespace Admin\Model;
use Think\Model;

/**
 * 设备信息模型
 * 
 */
class DeviceModel extends Model {

	protected $tableName = 'device';// 数据表名
    protected $fields    = array('device_ID','pid','name','price','desc','on_use','allNum','stock','cTime');// 字段信息
    protected $pk        = 'device_ID';// 主键

    // 自动验证
    protected $_validate = array(
        array('pid','check_pid','设备所属类别不正确!',self::EXISTS_VALIDATE,'callback',self::MODEL_INSERT),
        array('name','require','设备名称不能为空！',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('name','check_name','设备名称已存在！!',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH),
        array('price','check_Price','价钱非负！',self::EXISTS_VALIDATE,'function',self::MODEL_BOTH),
        array('allNum','number','设备总数不正确！',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),// 为了配合check_stock验证stock，强制需要验证allNum字段
        array('stock','check_stock','库存数据不正确!',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH),
    );

    // 自动完成
    protected $_auto = array (
        array('on_use','1',self::MODEL_INSERT,'string'),  // 新增的时候on_use=1
        array('cTime','getDatetime',self::MODEL_INSERT,'function') , // 新增的时候把调用time方法写入当前时间戳
    );

    /**
     * 验证是否存在该pid
     * @param int $pid 父类id
     * @return bool
     */
    protected function check_pid($pid){

    	if ($pid == 0) {// 类名，直接通过
    		return true;
    	}

    	$device = M('device');
    	$pids = $device->distinct(true)->where('pid = 0')->getField('device_ID', true);

    	// p($pids);

    	if (in_array($pid, $pids)) {
    		return true;
    	}else{
    		echo $pid." 不存在该类别<br/>";
    		return false;
    	}
    	
    }

    /**
     * 检查设备名称是否唯一
     * @param string $name 设备名称
     * @return bool 不存在返回true，存在返回false
     */
    function check_name($name){

        $device_model = M('device');

        $rs = $device_model->where(array("name" => $name))->find();

        if ($rs) {
            if ($rs['device_ID'] == $this->checkData['device_ID']) {
                // 防止修改时，被'自己'限制
                return true;
            }
            
            return false;
        }else{

            return true;
        }
    }

    /**
     * 验证库存stock，范围[0, allNum]
     * @param int $stock 库存
     * @return bool
     */
    protected function check_stock($stock){
    	
    	// p($this);
    	// p($this->checkData);// 修改框架代码所增加的数组
    	if ($stock >= 0) {
    		
    		if (isset($this->checkData['allNum'])) {// 如果有allNum字段
    			
    			if ($stock <= $this->checkData['allNum']) {
    				return true;
    			}else{
    				return false;
    			}
    		}else{// 没有allNum字段，直接返回true
    			return true;
    		}
    	}
    		
		return false;
    }
}
