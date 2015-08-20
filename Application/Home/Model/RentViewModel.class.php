<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 商品售卖记录模型
 * 
 */
class RentViewModel extends ViewModel {

    public $viewFields = array(

        'r_record'=>array('r_id','o_id','thing_ID','quantity','total','note','cTime','_table'=>"r_record"),
        
        'rent_things'=>array('name','price','deposit', '_on'=>'r_record.thing_ID=rent_things.thing_ID','_table'=>"rent_things"),
    );

/*

*/

}