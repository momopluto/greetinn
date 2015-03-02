<?php
/**
 * 公共方法函数
 *
 */

// 格式化打印数组
function p($array){
    dump($array, 1, '<pre>', 0);
}

/**
 * 写日志进log表
 * @param mixed $val
 * @return bool
 */ 
function write_log_all_array($val){

    // 日志内容初始化
    $val[1]['event'] = $val[3];
    
    $val[4] = strtolower(trim($val[4]));
    switch ($val[4]) {

        case 'login':
        case 'reg':
        case 'submit_order':
        case 'cancel':
        case 'check_in':
        case 'add':
            foreach ($val[5] as $key => $value) {
                // 组合日志内容
                $val[1]['event'] .= "，" . $key . ": " . $value;
            }
            break;
        case 'edit':
            if (empty($val[6])) {
                echo "<br/>"."edit的更新内容数组'$val[6]'为空！"."<br/>";
                return false;
            }
            // 得到格式化后的$edit_info
            $edit_info = format_edit_info($val[6]);

            foreach ($val[5] as $key => $value) {
                // 组合日志内容
                $val[1]['event'] .= "，" . $key . ": " . $value;
            }
            $val[1]['event'] .= "，" . $edit_info;
            break;
        case 'delete':

            // 组合日志内容
            $val[1]['event'] .= "，" . $val[5];

            break;
        default:
            echo "<br/>"."write_log_all的$type出错了！"."<br/>";
            die;
            break;
    }

    // 写日志
    if (!write_log($val[0], $val[1])) {
        // 如果不成功，rollback
        
        $val[2]->rollback();// 回滚事务
    }else{// 成功

        $val[2]->commit();// 提交事务
    }
}

/**
 * 写日志进log表
 * @param Model $log_model log模型
 * @param array $log_data "加了引用的"log数据
 * @param Model $model "加了引用的"数据来源模型，如：room表，device表等
 * @param string $event 操作内容，如：ADMIN_LOGIN_IN ＝ '管理员登录成功';
 * @param string $type 写日志的情形，$type可能为add, edit, delete等
 * @param mixed $data 附加的日志内容的键值数组 / 1个数字or1个数组
 * @param array $arr 需要额外处理的数组（可选）
 * @return bool
 */ 
function write_log_all($log_model, $log_data, $model, $event, $type, $data, $arr = array()){

    // 日志内容初始化
    $log_data['event'] = $event;
    
    $type = strtolower(trim($type));
    switch ($type) {

        case 'login':
        case 'reg':
        case 'add':
            foreach ($data as $key => $value) {
                // 组合日志内容
                $log_data['event'] .= "，" . $key . ": " . $value;
            }
            break;
        case 'edit':
            if (empty($arr)) {
                echo "<br/>"."edit的更新内容数组$arr为空！"."<br/>";
                return false;
            }
            // 得到格式化后的$edit_info
            $edit_info = format_edit_info($arr);

            foreach ($data as $key => $value) {
                // 组合日志内容
                $log_data['event'] .= "，" . $key . ": " . $value;
            }
            $log_data['event'] .= "，" . $edit_info;
            break;
        case 'delete':

            // 组合日志内容
            $log_data['event'] .= "，" . $data;

            break;
        default:
            echo "<br/>"."write_log_all的$type出错了！"."<br/>";
            die;
            break;
    }

    // 写日志
    if (!write_log($log_model, $log_data)) {
        // 如果不成功，rollback
        
        dump($model->rollback());// 回滚事务
    }else{// 成功

        dump($model->commit());// 提交事务
    }
    
    /*$type = strtolower(trim($type));
    switch ($type) {
        // ================================Client
        
        case 'pay_order':
            
            break;
        
        // ================================Client & Home
        case 'register':
            
            break;
        case 'submit_order':
            
            break;
        case 'cancel_order':
            
            break;
        case 'submit_borrow_request':
            
            break;
        case 'cancel_borrow_request':
            
            break;
        
        // ================================Home
        case 'cancel_paid_order':
            
            break;
        case 'check_in':
            
            break;
        case 'change_room':
            
            break;
        case 'check_out':
            
            break;
        case 'response_borrow_request':
            
            break;
        case 'confirm_return_borrowed':
            
            break;

        // ================================Home & Admin
        case 'login':
            
            break;

        // ================================Admin
        case 'add':
            
            break;
        case 'edit':
            
            break;
        case 'delete':
            
            break;
        
        default:
            return false;
    }*/
}

/**
 * 写日志进log表
 * @param Model $log_model log模型
 * @param array $log_data "加了引用的"log数据
 * @return bool
 */ 
function write_log($log_model, $log_data){

    // return false;// 模拟写日志失败

    if ($log_model->create($log_data)) {

        $log_model->add();// 新日志记录写入log表
        
        echo "log create成功<br/>";
        return true;
    }else{

        echo "log create失败<br/>";
        echo $log_model->getError();

        return false;
    }
}

/**
 * 格式化"用于更新的数组"的内容[xx=>xx, xx=>xx]
 * @param array $arr 用于更新的数组
 * @return string 格式化后的字符串
 */
function format_edit_info($arr){

    // 记录所更新的字段信息
    $edit_info = '';
    foreach ($arr as $key => $value) {

        $edit_info .= $key . "=>" . $value . ", ";
    }
    $edit_info = "[" . substr_replace($edit_info, "]", strlen($edit_info) - 2);// 加上'['，最后一个多出的  ', '  用 ']' 替换

    return $edit_info;
}

/**
 * 记录所删除的数据信息
 * @param Model $model 数据来源模型，如：room表，device表等
 * @param mixed $data data[1]为主键，[0]为主键对应的中文，[3]为根据主键找到的额外信息字段名，[2]为该额外信息对应的中文
 * @return string 记录所删除的数据信息
 */
function get_delete_info($model, $data){

    // 所删除的数据信息
    $del_info = '';
    if (is_array($data[1])) {// 如果是数组，组合成以','为连接符的字符串

        foreach ($data[1] as $value) {
            
            $del_data = $model->find($value);
            
            $del_info .= $del_data[$data[3]] . ",";
        }

        $del_info = $data[2] . ": " . substr($del_info, 0, strlen($del_info) - 1);
        
        $data[1] = implode(',', $data[1]);
        
        $del_info = $data[0] . ": " . $data[1] . "，" . $del_info;
    }else{
        
        $del_data = $model->find($data[1]);
        $del_info = $data[0] . ": " . $data[1] . "，". $data[2] . ": " . $del_data[$data[3]];
    }
    
    return $del_info;
}






/**
 * 检查身份证是否已注册
 * @param string $ID_card 身份证号码
 * @param string $modelName 模型名，client / member
 * @return bool或者一行记录
 */
function is_IDCard_exists($ID_card, $modelName){

    $model = M($modelName);
    // p($model);

    return $model->where(array('ID_card'=>$ID_card))->find();
}

/**
 * 检查房间号是否存在
 * @param string $room_ID 房间号
 * @return bool或者一行记录
 */
function is_Room_exists($room_ID){

    $room_model = M('room');

    return $room_model->where(array("room_ID" => $room_ID))->find();
}




/**
 * 检测输入的验证码是否正确
 * @param string $code 户输入的验证码字符串
 */
function check_verify($code, $id = ''){

    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 检查身份证合法性
 * @param string $ID_card 身份证号码
 * @return bool
 */
function check_IDCard($ID_card){

    $map=array(1, 0, X, 9, 8, 7, 6, 5, 4, 3, 2);
    $sum = 0;
    for($i = 17; $i > 0; $i--){
        $s=pow(2, $i) % 11;
        $sum += $s * $ID_card[17-$i];
    }

    // echo $map[$sum % 11];//这里显示最后一位校验码
    // echo $ID_card[strlen($ID_card) - 1];
    
    return $ID_card[strlen($ID_card) - 1] == $map[$sum % 11] ? true : false;
}

/**
 * 检查手机证合法性
 * @param string $phone 手机号
 * @return bool
 */
function check_Phone($phone){

    // 数字字符串，11位
    return is_numeric($phone) && strlen($phone) == 11 ? true : false;
}

/**
 * 检查价钱合法性
 * @param int $price 价钱
 * @return bool
 */
function check_Price($price){

    // 非负数
    return $price >= 0 ? true : false;
}

/**
 * 检查所选设备是否合法（所选的设备的ID是否存在）
 * @param string $device_IDs 设备IDs
 * @return bool
 */
function check_DeviceIn($device_IDs){

    // $device_IDs空则停止处理
    if ($device_IDs === '') {
        // echo "空！";
        return false;
    }

    // echo "不空！";


    // 以下检测所选设备是否合法
    $device = M('device');
    $in_Arr = $device->where('pid != 0')->getField('device_ID',true);// 所有可借设备ID的数组

    p($in_Arr);

    $IDs = explode(',', $device_IDs);// 需要检查的ID分解成数组

    // 循环遍历检测每个ID是否都存在于$in_Arr中
    foreach ($IDs as $val) {
        if (!in_array($val, $in_Arr)) {
            echo $val."不存在的设备！";
            return false;
        }
    }

    // $map['device_ID']  = array('in', $in_Arr);
    return true;
}


/**
 * 新增时，检查client_ID是否存在
 * @param int $client_ID 客户id
 * @return bool
 */
function check_Client($client_ID){

    $client = M('client');

    return $client->find($client_ID);// 因为client_ID是主键，可以直接find($client_ID)
}

/**
 * 新增时，检查book_info是否合法
 * @param int $book_info 客户id
 * @return bool
 */
function check_BookInfo($book_info){

    $book_info = json_decode($book_info, true);
    // p($book_info);

    foreach ($book_info['people_info'] as $one) {
        if ($one['name'] == ''){
            // 入住人姓名为空
            return false;
        }
        if (!check_IDCard($one['ID_card'])){
            // 身份证不正确
            return false;
        }
    }

    return true;
}

/**
 * 检查room_ID是否合法（防止1间房，在交叉的时间段，分配给多个o_id）
 * @param Array $new_data 新的入住信息
 * @return bool
 */
function check_RoomID($new_data){

    if (strtotime($new_data['A_date']) >= $new_data['B_date']) {
        // 入住时间 >= 离开时间，不合法
        return false;
    }
    // status 0, 1, 2, 3
}




/**
 * 配置验证码
 * @return 验证码
 */
function verify(){

    $config =    array(
        'fontSize'    =>    15,    // 验证码字体大小
        'useNoise'    =>    false, // 关闭验证码杂点
        'imageW'      =>    0,     // 验证码宽度
        'imageH'      =>    0,     // 验证码高度
        'length'      =>    4,     // 验证码位数
    );
    $Verify =     new \Think\Verify($config);
    return $Verify->entry();
}

/**
 * 获取身份证上的生日
 * @param string $ID_card 身份证号码
 * @return date 格式如：1991-01-01
 */
function getBirthday($ID_card){

    // echo $ID_card;

    // return substr($ID_card, 6,8);
    return date('Y-m-d',strtotime(substr($ID_card, 6,8)));
}

/**
 * 获取当前日期时间datetime，用于插入数据库
 * @return date 格式如：1991-01-01 14:08:27
 */
function getDatetime(){

    return date('Y-m-d H:i:s',time());
}





/**
 * 初始化o_record_2_room表中记录
 * @param int $o_id 订单记录id
 * @return bool
 */
function init_o_room($data){

    $model = M('o_record_2_room');
    return $model->add($data);
}

/**
 * 初始化o_record_2_stime表中记录
 * @param int $o_id 订单记录id
 * @return bool
 */
function init_o_sTime($o_id){

    $model = M('o_record_2_stime');

    $data['o_id'] = $o_id;

    return $model->add($data);
}

/**
 * 初始化d_record_2_stime表中记录
 * @param int $d_id 借设备记录id
 * @return bool
 */
function init_d_sTime($d_id){

    $model = M('d_record_2_stime');

    $data['d_id'] = $d_id;

    return $model->add($data);
}

function update_o_room($o_id, $room_ID){

    $model = M('o_record_2_room');

    $data['room_ID'] = $room_ID;

    return $model->where("o_id = $o_id")->save($data);
}

/**
 * 更新o_record_2_stime表中记录
 * @param int $o_id 借设备记录id
 * @param int $new_status 改变的状态值
 * @return bool
 */
function update_o_sTime($o_id, $new_status){

    $model = M('o_record_2_stime');
    $which = '';

    switch ($new_status) {
        case '0':
            $which = 'cancel';
            break;
        case '2':
            $which = 'pay';
            break;
        case '3':
            $which = 'checkIn';
            break;
        case '4':
            $which = 'checkOut';
            break;
        default:
            return false;
    }

    $updata[$which] = getDatetime();
    return $model->where("o_id = $o_id")->setField($updata);
}

/**
 * 更新d_record_2_stime表中记录
 * @param int $d_id 借设备记录id
 * @param int $new_status 改变的状态值
 * @return bool
 */
function update_d_sTime($d_id, $new_status){

    $model = M('d_record_2_stime');
    $which = '';

    switch ($new_status) {
        case '0':
            $which = 'cancel';
            break;
        case '2':
            $which = 'response';
            
            $device_IDs = M('d_record')->where("d_id = $d_id")->getField('device_IDs');
            // echo $device_IDs . "==========device_IDs<br/>";
            update_device_stock($device_IDs, 0);
            break;
        case '3':
            $which = 'return';

            $device_IDs = M('d_record')->where("d_id = $d_id")->getField('device_IDs');
            // echo $device_IDs . "==========device_IDs<br/>";
            update_device_stock($device_IDs, 1);
            break;
        default:
            return false;
    }

    $updata[$which] = getDatetime();
    return $model->where("d_id = $d_id")->setField($updata);
}

/**
 * 更新设备库存
 * @param string $device_IDs 设备IDs
 * @param int $type 方式，0响应减少库存，1归还增加库存
 */
function update_device_stock($device_IDs, $type){

    $device = M('device');

    $IDs = explode(',', $device_IDs);// 需要更新的ID分解成数组

    switch ($type) {
        case 0:// 响应借出，库存减少
        
            // 循环遍历检测每个ID，库存-1
            foreach ($IDs as $device_ID) {

                $device->where("device_ID = $device_ID")->setDec('stock');
            }
            break;
        case 1:// 归还，库存增加

            // 循环遍历检测每个ID，库存+1
            foreach ($IDs as $device_ID) {

                $device->where("device_ID = $device_ID")->setInc('stock');
            }
            break;
        
        default:
            return false;
    }
}



/**
 * 二维键值数组，按时间降序排序
 * @param Array $x 数组1
 * @param Array $y 数组2
 * @return int 
 */
function compare_cTime($x, $y){

    $_x = strtotime($x['cTime']);
    $_y = strtotime($y['cTime']);

    if ($_x == $_y){
        
        return 0;
    } elseif($_x > $_y){

        return -1;
    } else{

        return 1;
    }
}


// 获取本月的第1天和最后1天
function getMonth_StartAndEnd($date){
    $firstday = date("Y-m-01",strtotime($date));
    $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
    // return array($firstday,$lastday);//返回日期
    return array(strtotime($firstday),strtotime($lastday));//返回时间戳
 }

// 获取上月的第1天和最后1天
function getlastMonth_StartAndEnd($date){
    $timestamp=strtotime($date);
    $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
    $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
    // return array($firstday,$lastday);//返回日期
    return array(strtotime($firstday),strtotime($lastday));//返回时间戳
 }

// 获取下月的第1天和最后1天
function getNextMonth_StartAndEnd($date){
    $timestamp=strtotime($date);
    $arr=getdate($timestamp);
    if($arr['mon'] == 12){
        $year=$arr['year'] +1;
        $month=$arr['mon'] -11;
        $firstday=$year.'-0'.$month.'-01';
        $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
    }else{
        $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)+1).'-01'));
        $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
    }
    // return array($firstday,$lastday);//返回日期
    return array(strtotime($firstday),strtotime($lastday));//返回时间戳
}

?>