<?php
namespace Client\Controller;
use Think\Controller;

/**
 * 酒店预订控制器
 * 
 */
class OrderController extends ClientController {

    /**
     * 酒店预订/预定
     */
    public function detail(){
    	
    	$this->display();
    }

    /**
     * 网上支付
     */
    public function payOnline(){
    	
    	$this->display();
    }

}