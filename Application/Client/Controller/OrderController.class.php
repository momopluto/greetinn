<?php
namespace Client\Controller;
use Think\Controller;

/**
 * 酒店预订控制器
 * 
 */
class OrderController extends ClientController {

    // 操作状态
    const STATUS_CANCEL          =   0;      //  状态值，取消
    const STATUS_NEW             =   1;      //  状态值，新建
    const STATUS_PAY             =   2;      //  状态值，支付

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
    	
        // TODO
    	$this->display();
    }



    /**
     * 我的订单
     */
    public function myorder(){

        $this->display();
    }

    /**
     * 提交订单（要判断是否来自微信）
     */
    public function submit(){

        echo "酒店预订，begin<br/>";

        $client_ID = 1;// 模拟下单的客户

        $people_info = array(// 入住人身份信息
            array('name'=>'王一', 'ID_card'=>'520222196306159670'),
            array('name'=>'李二', 'ID_card'=>'230230197409256612')
        );


        $book_info['start_date'] = '2015-02-02';
        $book_info['leave_date'] = '2015-02-03';

        // 计算2个日期间隔天数
        $interval = date_diff(date_create($book_info['start_date']), date_create($book_info['leave_date']));
        $book_info['nights'] = $interval->format('%a');
        
        $book_info['number'] = count($people_info);// 入住人数
        $book_info['people_info'] = $people_info;
        $book_info['note'] = '2对耳塞';

        $new_order['client_ID'] = $client_ID;

        // o_record_2_room数据
        $new_order_2_room['nights'] = $book_info['nights'];
        $new_order_2_room['A_date'] = $book_info['start_date'];
        $new_order_2_room['B_date'] = $book_info['leave_date'];
        unset($book_info['nights']);
        unset($book_info['start_date']);
        unset($book_info['leave_date']);
        $new_order['book_info'] = json_encode($book_info, JSON_UNESCAPED_UNICODE);// unicode格式


        $new_order['price'] = '118';


        p($new_order);

        $order_model = D('OrderRecord');

        if ($order_model->create($new_order)) {
            echo "create成功<br/>";
            

            $o_id = $order_model->add();
            dump($o_id);

            $new_order_2_room['o_id'] = $o_id;
            init_o_room($new_order_2_room);// 初始化o_record_2_room表中记录

            init_o_sTime($o_id);// 初始化o_record_2_stime表中记录

        }else{

            echo "create失败<br/>";
            echo $order_model->getError();
        }
        
        echo "酒店预订，end<br/>";
        die;

        $this->display();
    }

    /**
     * 编辑订单（客户必须在微信上编辑）
     */
    public function edit(){

        echo "编辑订单，begin<br>";

        $o_record_model = D('OrderRecordView');
        $o_record_data = $o_record_model->select();

        p($o_record_data);

        p($o_record_model);
        die;

        $o_id = 2;// 模拟操作的订单号

        $people_info = array(// 入住人身份信息
            array('name'=>'王一', 'ID_card'=>'520222196306159670'),
            // array('name'=>'李二', 'ID_card'=>'230230197409256612')
        );


        $book_info['start_date'] = '2015-02-02';
        $book_info['leave_date'] = '2015-02-05';

        // 计算2个日期间隔天数
        $interval = date_diff(date_create($book_info['start_date']), date_create($book_info['leave_date']));
        $book_info['nights'] = $interval->format('%a');
        
        $book_info['number'] = count($people_info);// 入住人数
        $book_info['people_info'] = $people_info;
        $book_info['note'] = '2对耳塞';

        // $new_order['client_ID'] = $client_ID;
        $new_order['book_info'] = json_encode($book_info, JSON_UNESCAPED_UNICODE);// unicode格式
        // $new_order['price'] = '118';


        // 还需要更新以下几项数据
        $new_order_2_room['nights'] = $book_info['nights'];
        $new_order_2_room['A_date'] = $book_info['start_date'];
        $new_order_2_room['B_date'] = $book_info['leave_date'];

        p($new_order);

        $order_model = D('OrderRecord');

        if ($order_model->where("o_id = $o_id")->create($new_order, 2)) {
            echo "create成功<br/>";
            
            echo " ***" . $result = $order_model->scope('allowUpdateField, new')->where("o_id = $o_id")->save();
            if ($result) {
                echo "更新成功！<br/>";
            }else{

                echo "更新成功！<br/>";
                echo $order_model->getError();
            }

        }else{

            echo "create失败<br/>";
            echo $order_model->getError();
        }

        echo "编辑订单，end<br>";
        die;
        
        $this->display();
    }

    /**
     * 取消订单（客户必须在微信上取消）
     */
    public function cancel(){

        echo "取消订单，begin<br>";

        $o_id = 4;// 模拟操作的订单号

        $cancel['status'] = self::STATUS_CANCEL;
        
        $order_model = D('OrderRecord');

        $old_status = $order_model->where("o_id = $o_id")->getField('status');
        echo "old_status = $old_status";
        if ($old_status == $cancel['status']) {// 状态未改变

            echo "状态未改变，不需要更新<br/>";
            return false;
        }

        if ($order_model->where("o_id = $o_id")->create($cancel ,2)) {
            echo "create成功<br/>";

            echo " *** ". $result = $order_model->scope('allowUpdateField, new')->where("o_id = $o_id")->save();

            if ($result) {
                echo "取消成功！<br/>";

                // 需要更新d_record_2_stime表中记录
                dump(update_o_sTime($o_id, $cancel['status']));
            }else{

                echo "取消失败！<br/>";
                echo $order_model->getError();
            }
        }else{

            echo "create失败<br/>";
            echo $order_model->getError();
        }

        echo "取消订单，end<br>";
        die;
        
        $this->display();
    }

}