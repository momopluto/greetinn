<?php
namespace Admin\Model;
use Think\Model;

/**
 * 工作人员信息模型
 * 
 */
class MemberModel extends Model {

	protected $tableName = 'member';// 数据表名
	protected $fields 	 = array('member_ID', 'name','ID_card','gender','birthday','phone','position','salary','on_job','cTime');// 字段信息
    protected $pk     	 = array('member_ID');// 主键

    // 命名范围
    protected $_scope = array(
    	// 命名范围allowUpdate，允许更新的字段
    	'allowUpdateField'=>array(
    		'field'=>'phone,position,salary,on_job',
    	),
    );

    // 自动验证
    protected $_validate = array(
		array('name','require','姓名不能为空！',self::EXISTS_VALIDATE,'',self::MODEL_INSERT),
		array('ID_card','','身份证已存在！',self::EXISTS_VALIDATE,'unique',self::MODEL_INSERT), 
		array('ID_card','check_IDCard','身份证不正确！',self::EXISTS_VALIDATE,'function',self::MODEL_INSERT),
		array('gender','require','性别不能为空！',self::EXISTS_VALIDATE,'',self::MODEL_INSERT),
		array('phone','check_Phone','手机号不正确！',self::EXISTS_VALIDATE,'function',self::MODEL_BOTH),
	);

    // 自动完成
    protected $_auto = array (
        array('birthday','getBirthdayFromIDcard',self::MODEL_INSERT,'callback'),  // 新增的时候把调用getBirthdayFromIDcard方法从“身份证”中获取生日
        array('on_job','1',self::MODEL_INSERT,'string') , // 新增的时候默认on_job＝1
        array('cTime','getDatetime',self::MODEL_INSERT,'function') , // 新增的时候把调用time方法写入当前时间戳
    );

    /**
     * 从身份证ID_card中获取生日
     * @return date 格式如：1991-01-01
     */
    protected function getBirthdayFromIDcard(){

        // p($this->checkData);
        return getBirthday($this->checkData['ID_card']);
    }

}