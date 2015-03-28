<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 订单记录模型
 * 
 */
class VipViewModel extends ViewModel {

    public $viewFields = array(

        'vip'=>array('client_ID','card_ID','balance',
            /*'single_count','single_free','single_latestDate',
            'double_count','double_free','double_latestDate',
            'multiple_count','multiple_free','multiple_latestDate',*/
            'first_free','cTime','_table'=>"vip"),
        
        'client'=>array('name','ID_card','birthday','phone', '_on'=>'vip.client_ID=client.client_ID','_table'=>"client"),
    );

/*

*/

}