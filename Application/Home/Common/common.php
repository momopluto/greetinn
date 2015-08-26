<?php
/**
 * Home公共函数
 *
 */


/**
 * 检查是否已登录
 */
function is_login(){
    if(session('?H_LOGIN_FLAG') && session('H_LOGIN_FLAG') && session('?H_USER_V_INFO')){

        return true;
    }
    
    return false;
}

/**
 * 过滤得到空闲的房间
 * @param Array $data 订单详情
 * @return Array $rooms 可分配的房间
 */
function get_available_rooms($data, $self=''){
	$room_model = M('room');
	$rooms = $room_model->where(array('type' => $data['type'],'is_open'=>1))->getField('room_ID', true);
	
	$_2_room_model = M('o_record_2_room');

	// room_ID非空，时间区间有交集
	// $queryStr = "room_ID is not null" . " AND "
	//             . "NOT (A_date >= '".$data['B_date']."' OR B_date <= '".$data['A_date']."')";
	
	// room_ID非空，room_ID!=当前入住的房间号，时间区间有交集
	// $queryStr = "room_ID is not null AND room_ID != " . $data['room_ID'] . " AND "
	//             . "NOT (A_date >= '".$data['B_date']."' OR B_date <= '".$data['A_date']."')";
	// echo $queryStr;

	// $str = "( room_ID is not null AND NOT (A_date >= '".$data['B_date']."' OR B_date <= '".$data['A_date']."') )"
	// 		." AND ( status <> 0 AND status <> 4 )";
	$str = "( room_ID is not null";
	if ($self) {
		$str .= " AND room_ID != " . $self;
	}
	$str .= " AND NOT (A_date >= '".$data['B_date']."' OR B_date <= '".$data['A_date']."') OR status = 5 )"/* "OR status = 5" 锁定状态的房间 */
			." AND ( status <> 0 AND status <> 4 )";
	// echo $str;

	// _rooms为要去除的房间
	$_rooms = $_2_room_model->join('o_record ON o_record.o_id = o_record_2_room.o_id')->where($str)->getField('room_ID',true);

	// p($data);
	// p($rooms);
	// p($_rooms);
	if ($_rooms) {
	    $rooms = array_diff($rooms, $_rooms);// 该房型下，可分配的房间
	}
	// p($rooms);die;

	return $rooms;
}

// [From数据库]获得(前台)最后一次交班的标识
function get_lastShift_record(){
	
	$map['in'] = 0;
	$map['out'] = 0;
	$map['type'] = 0;

	$last = D("CapitalAdv")->where($map)->last();
	return $last;
}

// // [From session]获得(前台)当前班次的标识
// function get_shift(){

// 	$info = session('H_USER_V_INFO');

// 	return $info['shift'];
// }

// // 获得(前台)操作者名字
// function get_OperName(){

// 	$info = session('H_USER_V_INFO');

// 	return $info['oper_NAME'];
// }

// 获得(前台)操作者信息
function get_Oper($field=""){

	$info = session('H_USER_V_INFO');

	switch ($field) {
		case 'ID':
			$rst = $info['oper_ID'];
			break;
		case 'name':
			$rst = $info['oper_NAME'];
			break;
		case 'shift':
			$rst = $info['shift'];
			break;
		default:
			$rst = "";
			break;
	}

	return $rst;
}


// 验证前台登录
function validate_login($post){

	$one = is_IDCard_exists($post['user'], 'member');// 检验账号
	//检验密码
    // p($one);die;
    // $data['one'] = $one;
    // $data['session'] = session('H_USER_V_INFO');
	if ($one === false) {

		$data['errcode'] = 4;
		$data['errmsg'] = "成员不存在";
		return $data;
	}else {

		if ($one['member_ID'] == get_Oper('ID')) {
			$data['errcode'] = 5;
			$data['errmsg'] = "交班者与接班者为同一人";
			return $data;
		}

		if ($one['position'] != 1) {// 职位不是前台
			$data['errcode'] = 6;
			$data['errmsg'] = "无权登录";
			return $data;
		}

		switch ($one['on_job']) {
			case 0:
				$data['errcode'] = 7;
				$data['errmsg'] = "成员已离职";
				break;
			case 1:
				$data['errcode'] = 0;
				$data['errmsg'] = "验证成功";
				$data['next_oper'] = $one['name'];// 接班者
				break;
			case 2:
				$data['errcode'] = 8;
				$data['errmsg'] = "成员休假中";
				break;
			default:
				$data['errcode'] = 9;
				$data['errmsg'] = "未知错误";
				break;
		}

		return $data;
	}
}





// 比较本月销售额(降序)，用于所有餐厅排序
function compare_month_sale($x, $y){
	if($x['month_sale'] == $y['month_sale']){//
		return 0;
	}elseif($x['month_sale'] > $y['month_sale']){
		return -1;
	}else{
		return 1;
	}
}

?>