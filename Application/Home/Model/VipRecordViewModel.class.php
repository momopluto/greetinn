<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 会员卡记录视图模型
 * 
 */
class VipRecordViewModel extends ViewModel {

    public $viewFields = array(

        'vip_record'=>array('v_id','client_ID','card_ID','style','cTime','amount','balance','operator','o_id','_table'=>"vip_record"),
        
        'client'=>array('name','ID_card','phone', '_on'=>'vip_record.client_ID=client.client_ID','_table'=>"client"),
        
        'vip_style'=>array('name'=>'style_name', '_on'=>'vip_record.style=vip_style.style','_table'=>"vip_style"),
    );

/*

*/

}