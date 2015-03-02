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
 * @param string $queryStr 过滤字符串
 * @return Array $rooms 可分配的房间
 */
function get_available_rooms($data, $queryStr){
	$room_model = M('room');
	$rooms = $room_model->where(array('type' => $data['type']))->getField('room_ID', true);
	
	// ->where("cTime between '".$last_month_days[0]."' and '".$month_days[1]."'")
	$_2_room_model = M('o_record_2_room');

	// room_ID非空，时间区间有交集
	// $queryStr = "room_ID is not null" . " AND "
	//             . "NOT (A_date >= '".$data['B_date']."' OR B_date <= '".$data['A_date']."')";
	
	// room_ID非空，room_ID!=当前入住的房间号，时间区间有交集
	// $queryStr = "room_ID is not null AND room_ID != " . $data['room_ID'] . " AND "
	//             . "NOT (A_date >= '".$data['B_date']."' OR B_date <= '".$data['A_date']."')";
	// echo $queryStr;
	$_rooms = $_2_room_model->where($queryStr)->getField('room_ID', true);// 需要去除的房间

	// p($data);
	// p($rooms);
	// p($_rooms);
	if ($_rooms) {
	    $rooms = array_diff($rooms, $_rooms);// 该房型下，可分配的房间
	}
	// p($rooms);die;

	return $rooms;
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