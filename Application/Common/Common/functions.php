<?php
/**
 * 公共方法函数
 *
 */

// 格式化打印数组
function p($array){
    dump($array, 1, '<pre>', 0);
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