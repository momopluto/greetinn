<?php
/**
 * Client公共函数
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


// 比较上个月销售额(降序)，用于所有餐厅排序
function compare_last_month_sale($x, $y){
	if($x['last_month_sale'] == $y['last_month_sale']){//
		return 0;
	}elseif($x['last_month_sale'] > $y['last_month_sale']){
		return -1;
	}else{
		return 1;
	}
}

// 订餐页面所需要的餐厅的信息，组装
// 判断当前时间餐厅状态以及月销售量
function rstInfo_combine($an_rst){
	// 判断是否营业时间
    $n_time = date('H:i');


    if(strtotime($n_time) < strtotime($an_rst['stime_1_open'])){
        $open_status = "0";
    }elseif(strtotime($n_time) <= strtotime($an_rst['stime_1_close'])){
        $open_status = "1";
    }else{
        $open_status = "14";
    }

    if ($an_rst['stime_2_open'] !== '' && $an_rst['stime_2_close'] !== '') {
        $has_2_time = true;
        if(strtotime($n_time) < strtotime($an_rst['stime_2_open'])){
            if($open_status == "14"){
                $open_status = "12";
            }
        }elseif(strtotime($n_time) <= strtotime($an_rst['stime_2_close'])){
            $open_status = "2";
        }else{
            $open_status = "24";
        }
    }

    if ($an_rst['stime_3_open'] !== '' && $an_rst['stime_3_close'] !== '') {
        $has_2_time = true;
        if(strtotime($n_time) < strtotime($an_rst['stime_3_open'])){
            if($open_status == "24"){
                $open_status = "23";
            }
        }elseif(strtotime($n_time) <= strtotime($an_rst['stime_3_close'])){
            $open_status = "3";
        }else{
            $open_status = "4";
        }
    }

    $an_rst['open_status'] = $open_status;

    $an_rst['month_sale'] = M('menu', $an_rst['rid']."_")->sum('month_sale');//本月销售量
    $an_rst['last_month_sale'] = M('menu', $an_rst['rid']."_")->sum('last_month_sale');//本月销售量

    return $an_rst;
}


// 将open - close之间的时间，以10分钟为间隔分割
function cut_cut($open, $close, $on){

	$times = array();

	$strOpen = strtotime($open);
	$strClose = strtotime($close);

	if($on){//餐厅正在营业

		if(($strClose  - $strOpen) / 60 > 40){
			
			$strOpen = $strOpen + (600 - $strOpen % 600);//向上取整为10的倍数

			for ($i=2; $i <= ($strClose  - $strOpen) / 600; $i++) { 

				array_push($times, date('H:i',($strOpen + 600 * $i)));

			}
		}

	}else{//餐厅尚未营业

		for ($i=0; $i <= ($strClose  - $strOpen) / 600; $i++) { 

			array_push($times, date('H:i',($strOpen + 600 * $i)));

		}
	}

	// p($times);die;
	return $times;
}

// 划分送餐时间，返回字符串数组
function cut_send_times($an_rst){

	$s_times = array();

	if(intval($an_rst['open_status']) % 10 != 4){//未到休息时间
		
		if($an_rst['open_status'] == "0"){

			$s_times = cut_cut($an_rst['stime_1_open'], $an_rst['stime_1_close'], 0);

		}elseif($an_rst['open_status'] == "1"){

			$s_times = cut_cut(date('H:i'), $an_rst['stime_1_close'], 1);

		}elseif($an_rst['open_status'] == "12"){

			$s_times = cut_cut($an_rst['stime_2_open'], $an_rst['stime_2_close'], 0);

		}elseif($an_rst['open_status'] == "2"){

			$s_times = cut_cut(date('H:i'), $an_rst['stime_2_close'], 1);

		}elseif($an_rst['open_status'] == "23"){

			$s_times = cut_cut($an_rst['stime_3_open'], $an_rst['stime_3_close'], 0);

		}elseif($an_rst['open_status'] == "3"){

			$s_times = cut_cut(date('H:i'), $an_rst['stime_3_close'], 1);

		}

	}

	return $s_times;
}

// 获取卓效团队的接口得到的user_id
function get_zx_userid($jump_url){

	$aid = "608f5652accc7314abd682e8dedfba86";
    // $jump_url = "http://192.168.1.103:8080/platform/index.php/Client/Restaurant/lists.html";
    
    $zx_url = "http://wx.joshell.com/" . $aid . "/jwc/open-oauth?redirect_uri=" . $jump_url;

    redirect($zx_url);
}

// 判断网页是否在微信浏览器中打开
function is_weixin(){

	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {

		return true;
	}

	return false;

	// $user_agent = $_SERVER['HTTP_USER_AGENT'];
	// if (strpos($user_agent, 'MicroMessenger') === false) {
	//     // 非微信浏览器禁止浏览
	//     echo "HTTP/1.1 401 Unauthorized";
	// } else {
	//     // 微信浏览器，允许访问
	//     echo "MicroMessenger";
	//     // 获取版本号
	//     preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
	//     echo '<br>Version:'.$matches[2];
	// }

// 	下面分别是 Android, WinPhone, iPhone 的 HTTP_USER_AGENT 信息。
// 1 "HTTP_USER_AGENT": "Mozilla/5.0 (Linux; U; Android 4.1; zh-cn; Galaxy Nexus Build/Wind-Galaxy Nexus-V1.2) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 MicroMessenger/5.0.1.352",
// 2 "HTTP_USER_AGENT": "Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; Nokia 920T)",
// 3 "HTTP_USER_AGENT": "Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Mobile/10B329 MicroMessenger/5.0.1",
}


?>