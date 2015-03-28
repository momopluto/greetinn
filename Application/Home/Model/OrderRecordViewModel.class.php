<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 订单记录模型
 * 
 */
class OrderRecordViewModel extends ViewModel {

    public $viewFields = array(

        'O_record'=>array('o_id','client_ID','book_info','style','type','source','a_id','g_id','pay_mode','price'/*=>'o_price'*/,'deposit','phone','status'/*=>'o_status'*/,'cTime'/*=>'o_cTime'*/,'_table'=>"o_record"),
        'client'=>array('name','ID_card', '_on'=>'O_record.client_ID=client.client_ID','_table'=>"client"),

        'style'=>array('name' => 'style_name', '_on'=>'O_record.style=style.style','_table'=>"style"),
        'type'=>array('name' => 'type_name', '_on'=>'O_record.type=type.type','_table'=>"type"),
        'order_source'=>array('name' => 'source_name', '_on'=>'O_record.source=order_source.source','_table'=>"order_source",'_type'=>'LEFT'),
        'groupon'=>array('name' => 'groupon_name', '_on'=>'O_record.g_id=groupon.g_id','_table'=>"groupon",'_type'=>'LEFT'),
        'agent'=>array('name' => 'agent_name','phone' => 'a_phone','a_price', '_on'=>'O_record.a_id=agent.a_id','_table'=>"agent"),
        
        'O_record_2_room'=>array('room_ID','nights','A_date','B_date','note', '_on'=>'O_record.o_id=O_record_2_room.o_id','_table'=>"o_record_2_room"),
        'O_record_2_stime'=>array('cancel'/*=>'o_cancel'*/,'pay','checkIn','checkOut', '_on'=>'O_record.o_id=O_record_2_stime.o_id','_table'=>"o_record_2_stime"),
        
        // 'D_record'=>array('d_id','device_IDs','price'=>'d_price','openid','status'=>'d_status','cTime'=>'d_cTime', '_on'=>'D_record.o_id=O_record.o_id','_table'=>"d_record"),
        // 'D_record_2_stime'=>array('cancel'=>'d_cancel','response','return'=>'return_', '_on'=>'D_record.d_id=D_record_2_stime.d_id','_table'=>"d_record_2_stime"),
    );

/* Array
(
    [0] => Array
        (
            [o_id] => 31126
            [book_info] => {"number":1,"people_info":[{"name":"刘恩坚","ID_card":"441423199305165018"}],"note":""}
            [style] => 0
            [type] => 4
            [source] => 4
            [a_id] => 3
            [g_id] => 12
            [pay_mode] => 0
            [price] => 808
            [deposit] => 0
            [phone] => 18826481053
            [status] => 1
            [cTime] => 2015-03-08 10:13:23
            
            [name] => 刘恩坚
            [ID_card] => 441423199305165018

            [style_name] => 普通

            [type_name] => 标准单间

            [source_name] => 团购 (不可选)

            [groupon_name] => 去哪儿网

            [agent_name] => 黄冠龙
            [a_phone] => 15017528599
            [a_price] => 

            [room_ID] => 413
            [nights] => 1
            [A_date] => 2015-03-08 12:00:00
            [B_date] => 2015-03-09 12:00:00
            [note] => 

            [cancel] => 
            [pay] => 
            [checkIn] => 
            [checkOut] => 
        )

)*/

}