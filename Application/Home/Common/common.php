<?php
/**
 * Home公共函数
 *
 */

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