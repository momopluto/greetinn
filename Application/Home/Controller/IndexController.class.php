<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 主页控制器
 */
class IndexController extends HomeController {

	/**
	 * 主页，房态
	 */
    public function index(){


    	// 上一页，today-10
    	// 下一页，today+10
    	if ($today = I('get.src')) {

    	}else{

	    	$today = date('Y-m-d');
    	}

    	$start = -2;
    	for ($i=0; $i < 10; $i++) {

    		$date[$start] = date('Y-m-d',strtotime($start . ' day', strtotime($today)));
    		// date("Y-m-d",strtotime("+2 month", strtotime("2012-12-12")));
    		$start++;
    	}
/*
 Array
(
    [-2] => 2015-08-03
    [-1] => 2015-08-04
    [0] => 2015-08-05
    [1] => 2015-08-06
    [2] => 2015-08-07
    [3] => 2015-08-08
    [4] => 2015-08-09
    [5] => 2015-08-10
    [6] => 2015-08-11
    [7] => 2015-08-12
)
*/

/*
SELECT DISTINCT room.room_ID,o_record.`status`,room_status.`name` AS status_name,o_record.o_id,client.`name` AS client_name,DATE_FORMAT(o_record_2_room.A_date,'%Y-%m-%d') AS startDay,DATE_FORMAT(o_record_2_room.B_date,'%Y-%m-%d') AS endDay
FROM (room JOIN o_record_2_room ON room.room_ID=o_record_2_room.room_ID) JOIN o_record ON o_record.o_id=o_record_2_room.o_id JOIN client ON o_record.client_ID=client.client_ID JOIN room_status ON room_status.`status`=o_record.`status`
WHERE (DATE_FORMAT(o_record_2_room.A_date,'%Y-%m-%d')>='2015-08-03' AND DATE_FORMAT(o_record_2_room.B_date,'%Y-%m-%d')<='2015-08-12') AND (o_record.`status`<>0)
ORDER BY room_ID,endDay,startDay

#GROUP BY room.room_ID
*/

		// $conn = @mysql_connect("localhost","root","");
		// if (!$conn){
		//     die("连接数据库失败：" . mysql_error());
		// }

		// mysql_select_db("greetinn", $conn);
		
		//****************************************


		$Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表

		$queryStr1 = "DROP TEMPORARY TABLE IF EXISTS `r_status`;";
		$result = $Model->execute($queryStr1);
		// var_dump($result);

		$queryStr1 = "CREATE TEMPORARY TABLE r_status(
	room_ID VARCHAR(20) NOT NULL,";
		foreach ($date as $value) {
			$queryStr1 .= " `". $value ."` VARCHAR(255) NOT NULL DEFAULT '0',";
		}
		$queryStr1 .= "PRIMARY KEY (room_ID)
) ENGINE=INNODB DEFAULT CHARSET=utf8;";
		$result = $Model->execute($queryStr1);
		// var_dump($result);

		$queryStr1 = "INSERT INTO r_status(room_ID) VALUES";
		$rooms = M('room')->where("is_open=1")->order('type,room_ID+1')->getField('room_ID',true);
		// p($rooms);die;
		$k = true;
		foreach ($rooms as $value) {
			if ($k) {
				$queryStr1 .= " ('". $value ."')";
				$k = false;
			}else{

				$queryStr1 .= ", ('". $value ."')";
			}
		}
		$queryStr1 .= ";";
		$result = $Model->execute($queryStr1);
		// var_dump($result);

		// die;


//----------------------------------------------------------------


		// 获取"前2天~后7天，有订单记录的房间"
		$queryStr2 = "SELECT DISTINCT room.room_ID,o_record.`status`,room_status.`name` AS status_name,o_record.o_id,client.`name` AS client_name,DATE_FORMAT(o_record_2_room.A_date,'%Y-%m-%d') AS startDay,DATE_FORMAT(o_record_2_room.B_date,'%Y-%m-%d') AS endDay
FROM (room JOIN o_record_2_room ON room.room_ID=o_record_2_room.room_ID) JOIN o_record ON o_record.o_id=o_record_2_room.o_id JOIN client ON o_record.client_ID=client.client_ID JOIN room_status ON room_status.`status`=o_record.`status`"
			. " WHERE (DATE_FORMAT(o_record_2_room.A_date,'%Y-%m-%d')>='" . $date[-2] . "' AND DATE_FORMAT(o_record_2_room.B_date,'%Y-%m-%d')<='" . $date[7] . "') AND (o_record.`status`<>0)"
			. " ORDER BY room_ID,endDay,startDay";
		// echo $queryStr2;
		$temp_data2 = $Model->query($queryStr2);

		// p($temp_data2);die;

		// 抽出room_ID
		// 组合成形式如下数组
/*
 Array
(
    [2015-08-03] => Array
        (
            [205] => status
            [206] => status
            [207] => status
        )
    [2015-08-04] => Array
        (
            [206] => status
        )
    [2015-08-05] => Array
        (
            [207] => status
        )
)
*/
		foreach ($temp_data2 as $value) {

			$room_IDs[$value['room_ID']] = 0;

			// status, status_name, o_id, client_name
			$inStr = $value['status'].",".$value['status_name'].",".$value['o_id'].",".$value['client_name'];

			// echo $inStr." ***<br>";

			if (strcmp($value['startDay'], $value['endDay']) == 0) {
				// 钟点房，或者是"当天开当天离店"
				$update_dataArr[$tempDay][$value['room_ID']] = $inStr;
				continue;
			}

			$tempDay = $value['startDay'];
			while (strcmp($tempDay, $value['endDay']) != 0) {

				$update_dataArr[$tempDay][$value['room_ID']] = $inStr;
				$tempDay = date("Y-m-d",strtotime("+1 day", strtotime($tempDay)));
				// echo $tempDay . " *** ".$value['endDay'];
			}
		}

		// 更新的字段为varchar型(int型不会改变默认值)，虽然有默认值为'0'，
		// 但是用set case when这样的结构更新时，所涉及到的room_IDs相关的所有字段都会更新
		// 这就造成，本来不需要更新的字段，默认值却变为了空
		// 此处就是为了处理这种情况，为所有"涉及到但是本不需要更新的字段"赋值为'0'
		foreach ($room_IDs as $room_ID => $noUse1) {
			foreach ($update_dataArr as $curDate => $noUse2) {
				if (!$update_dataArr[$curDate][$room_ID]) {

					$update_dataArr[$curDate][$room_ID] = '0';
				}
			}
		}

		// p($update_dataArr);die;

		$c = true;
		$update_Str = "UPDATE r_status SET";
        foreach ($update_dataArr as $curDate => $value) {
            if ($c) {
	            //  first_free_checkIn =  CASE client_ID 
	            $update_Str .= " `". $curDate ."` = CASE room_ID ";
	            $c = false;
            }else{
            	$update_Str .= ", `". $curDate ."` = CASE room_ID ";
            }

            foreach ($value as $room_ID => $status) {
            	
	            $update_Str .= sprintf("WHEN '%s' THEN '%s' ", $room_ID, $status);
            }
            $update_Str .= "END";
            
        }
		// 得到需要更新的room_ID字符串
		$room_IDs = "'".implode("','", array_keys($room_IDs))."'";
		// echo $room_IDs;die;
        $update_Str .= " WHERE room_ID IN ($room_IDs);";

        // echo $update_Str;die;
        $result = $Model->execute($update_Str);// 执行更新临时表
		// var_dump($result);
		// echo "*************************************";
		
		$queryStr = "SELECT r_status.room_ID,type.`name` AS type_name,`".$date[-2]."`,`".$date[-1]."`,`".$date[0]."`,`".$date[1]."`,`".$date[2]."`,`".$date[3]."`,`".$date[4]."`,`".$date[5]."`,`".$date[6]."`,`".$date[7]."`
FROM r_status JOIN room ON r_status.room_ID=room.room_ID JOIN type ON room.type=type.type";
		// $queryStr = "SELECT * FROM r_status;";
		// echo $queryStr;die;
		$result = $Model->query($queryStr);// select临时表
		// var_dump($result);
		// p($result);die;

/*
DROP TEMPORARY TABLE IF EXISTS r_status;

CREATE TEMPORARY TABLE r_status(
	room_ID VARCHAR(20) NOT NULL,
  ##`type` tinyint(1) unsigned NOT NULL,
	`2015-08-03` VARCHAR(255) NOT NULL DEFAULT 0,
	`2015-08-04` VARCHAR(255) NOT NULL DEFAULT 0,
	`2015-08-05` VARCHAR(255) NOT NULL DEFAULT 0,
	`2015-08-06` VARCHAR(255) NOT NULL DEFAULT 0,
	`2015-08-07` VARCHAR(255) NOT NULL DEFAULT 0,
	`2015-08-08` VARCHAR(255) NOT NULL DEFAULT 0,
	`2015-08-09` VARCHAR(255) NOT NULL DEFAULT 0,
	`2015-08-10` VARCHAR(255) NOT NULL DEFAULT 0,
	`2015-08-11` VARCHAR(255) NOT NULL DEFAULT 0,
	`2015-08-12` VARCHAR(255) NOT NULL DEFAULT 0,
	PRIMARY KEY (room_ID)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT INTO r_status(room_ID) VALUES
(205),
(206),
(302);

UPDATE r_status
	SET `2015-08-03` = CASE room_ID
    	WHEN '205' THEN '4,空净(已打扫),32372,刘恩坚'
        WHEN '206' THEN '4,空净(已打扫),32371,刘恩坚'
    END,
      `2015-08-04` = CASE room_ID
      	WHEN '206' THEN '4,空净(已打扫),32371,刘恩坚'
        WHEN '302' THEN '1,预订(未付款),32368,#预订订单#'
    END,
      `2015-08-05` = CASE room_ID
      	WHEN '206' THEN '3,入住ing,32373,刘恩坚'
    END,
      `2015-08-07` = CASE room_ID
    	WHEN '206' THEN '1,预订(未付款),32374,刘恩坚'
    END,
      `2015-08-08` = CASE room_ID
      	WHEN '206' THEN '1,预订(未付款),32374,刘恩坚'
    END
WHERE room_ID IN ('205','206','302');

SELECT * FROM r_status;
*/


		// 接下来要做的，就是把临时表与o_record和client表join，得到o_id和name
		// 在这之前，要重新设计o_record的status
		// 		4.已退房(已打扫), 5.已退房(未打扫、锁定)
		// 以及定义房态status(此与上status不同)
		// 		0.空闲, 1.已预订未付款, 2.已预订已付款, 3.正在入住, 4.空净/已打扫, 5.已退房未打扫
		// 对应操作：
		// 		0:{预定}
		// 		1:{办理入住,编辑,取消}
		// 		2:{办理入住,编辑}
		// 		3:{办理续住,办理换房,办理退房}
		// 		4:{预定}
		// 		5:{设为空净&解锁}

		$this->assign("data", $result);
		$this->assign("today", $today);
		$this->display('roomstatus');








/*
		$model = M('roomstatus');
    	// $data = $model->getField('room_ID,2015-08-03,2015-08-04,2015-08-05,2015-08-06,2015-08-07,2015-08-08,2015-08-09,2015-08-10,2015-08-11,2015-08-12');
    	$data = $model->select();

    	p($data);die;
        
        $this->display();
*/
    }
}