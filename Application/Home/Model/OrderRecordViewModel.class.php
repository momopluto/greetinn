<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 订单记录模型
 * 
 */
class OrderRecordViewModel extends ViewModel {

    public $viewFields = array(

        'O_record'=>array('o_id'/*,'client_ID'*/,'book_info','type','price'/*=>'o_price'*/,'deposit','phone','status'/*=>'o_status'*/,'cTime'/*=>'o_cTime'*/,'_table'=>"o_record"),
        'client'=>array('name','ID_card', '_on'=>'O_record.client_ID=client.client_ID','_table'=>"client"),
        'O_record_2_room'=>array('room_ID','nights','A_date','B_date','note', '_on'=>'O_record.o_id=O_record_2_room.o_id','_table'=>"o_record_2_room"),
        'O_record_2_stime'=>array('cancel'/*=>'o_cancel'*/,'pay','checkIn','checkOut', '_on'=>'O_record.o_id=O_record_2_stime.o_id','_table'=>"o_record_2_stime"),
        
        // 'D_record'=>array('d_id','device_IDs','price'=>'d_price','openid','status'=>'d_status','cTime'=>'d_cTime', '_on'=>'D_record.o_id=O_record.o_id','_table'=>"d_record"),
        // 'D_record_2_stime'=>array('cancel'=>'d_cancel','response','return'=>'return_', '_on'=>'D_record.d_id=D_record_2_stime.d_id','_table'=>"d_record_2_stime"),
    );

/* Array
(
    [0] => Array
        (
            [o_id] => 2
            [client_ID] => 1
            [book_info] => {"number":1,"people_info":[{"name":"王一","ID_card":"520222196306159670"}],"note":"2对耳塞"}
            [o_price] => 118
            [o_status] => 0
            [o_cTime] => 2015-02-02 05:10:15

            [room_ID] => 228 test edit2
            [nights] => 1
            [A_date] => 2015-02-02
            [B_date] => 2015-02-03
            [note] => 换房: 228 zz -> 228 zz;换房: 228 zz -> 228 test;换房: 228 test -> 228;
            
            [o_cancel] => 2015-02-03 12:50:13
            [pay] => 2015-02-03 12:50:15
            [checkIn] => 2015-02-03 12:50:16
            [checkOut] => 2015-02-03 12:50:18
            
            // [d_id] => 1
            // [device_IDs] => 12,13,14
            // [d_price] => 4
            // [openid] => jhskwqujsdlkwql
            // [d_status] => 3
            // [d_cTime] => 2015-01-30 22:38:27
            
            // [d_cancel] => 2015-01-31 16:46:04
            // [response] => 2015-01-31 17:48:10
            // [return_] => 2015-01-31 17:50:40
        )

)*/

}