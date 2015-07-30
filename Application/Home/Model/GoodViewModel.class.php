<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 商品售卖记录模型
 * 
 */
class GoodViewModel extends ViewModel {

    public $viewFields = array(

        'g_record'=>array('g_id','guid','good_ID','quantity','total','note','cTime','_table'=>"g_record"),
        
        'good'=>array('name','price', '_on'=>'g_record.good_ID=good.good_ID','_table'=>"good"),
    );

/*

*/

}