<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 会员卡视图模型
 * 
 */
class VipViewModel extends ViewModel {

    public $viewFields = array(

        'vip'=>array('client_ID','card_ID','birthday','nominal_fee','balance','first_free','first_free_checkIn','operator','cTime','_table'=>"vip"),
        
        'client'=>array('name','ID_card','phone', '_on'=>'vip.client_ID=client.client_ID','_table'=>"client"),
    );

/*

*/

}