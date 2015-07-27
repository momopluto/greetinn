<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 订单记录模型
 * 
 */
class VipViewModel extends ViewModel {

    public $viewFields = array(

        'vip'=>array('client_ID','card_ID','birthday','balance','first_free','first_free_checkIn','cTime','_table'=>"vip"),
        
        'client'=>array('name','ID_card','phone', '_on'=>'vip.client_ID=client.client_ID','_table'=>"client"),
    );

/*

*/

}