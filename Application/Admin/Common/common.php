<?php
/**
 * Admin公共函数
 *
 */

/**
 * 检查是否已登录
 */
function is_login(){
    if(session('?A_LOGIN_FLAG') && session('A_LOGIN_FLAG') && session('?USER_V_INFO')){

        return true;
    }
    
    return false;
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