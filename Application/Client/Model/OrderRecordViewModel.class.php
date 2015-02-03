<?php
namespace Client\Model;
use Think\Model\ViewModel;

/**
 * 订单记录模型
 * 
 */
class OrderRecordViewModel extends ViewModel {

	public $viewFields = array(
        
        'O_record'=>array('client_ID','book_info','price','status','cTime','_table'=>"o_record"),
        'O_record_2_room'=>array('room_ID','nights','A_date','B_date','note', '_on'=>'O_record.o_id=O_record_2_room.o_id','_table'=>"o_record_2_room"),
    );

/*
 Array
(
    [0] => Array
        (
            [client_ID] => 1
            [book_info] => {"start_date":"2015-02-02","leave_date":"2015-02-05","nights":"3","number":1,"people_info":[{"name":"王一","ID_card":"520222196306159670"}],"note":"2对耳塞"}
            [price] => 118
            [status] => 0
            [cTime] => 2015-02-02 05:10:15

            [room_ID] => 228 test edit2
            [nights] => 1
            [A_date] => 2015-02-02
            [B_date] => 2015-02-03
            [note] => 换房: 228 zz -> 228 zz;换房: 228 zz -> 228 test;换房: 228 test -> 228;
        )

    [1] => Array
        (
            [client_ID] => 1
            [book_info] => {"start_date":"2015-02-02","leave_date":"2015-02-03","nights":"1","number":2,"people_info":[{"name":"王一","ID_card":"520222196306159670"},{"name":"李二","ID_card":"230230197409256612"}],"note":"2对耳塞"}
            [price] => 118
            [status] => 0
            [cTime] => 2015-02-02 15:34:01
            
            [room_ID] => 
            [nights] => 1
            [A_date] => 2015-02-02
            [B_date] => 2015-02-03
            [note] => 
        )

)
*/
}