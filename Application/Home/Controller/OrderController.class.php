<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 订单控制器
 */
class OrderController extends HomeController {

    // 操作状态
    const STATUS_CANCEL          =   0;      //  状态值，取消
    const STATUS_NEW             =   1;      //  状态值，新建
    const STATUS_PAY             =   2;      //  状态值，支付
    const STATUS_CHECKIN         =   3;      //  状态值，入住
    const STATUS_CHECKOUT        =   4;      //  状态值，退房
    
    /**
     * 未完成订单
     */
    public function dealing(){

        echo "未完成订单，beign<br/>";
        
        $o_record_model = D('OrderRecordView');

        $o_record_data = $o_record_model->select();

        p($o_record_data);

        p($o_record_model);

        echo "未完成订单，beign<br/>";
        die;
        
        $this->display();
    }

    /**
     * 已完成订单
     */
    public function complete(){
        
        $this->display();
    }

    // /**
    //  * [助]客户下单----直接使用Client/Order/submit
    //  */
    // public function detail(){
        
    //     $this->display();
    // }

    /**
     * 编辑订单
     */
    public function edit(){
        echo "编辑订单，begin<br>";

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
    }

    /**
     * 取消订单（分：未付款，已付款）
     */
    public function cancel(){

        echo "取消订单，begin<br>";

        $o_id = 3;// 模拟操作的订单号

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

            $queryStr = "status = ".self::STATUS_NEW." or status = ".self::STATUS_PAY;
            // echo $str;
            echo " *** ". $result = $order_model->scope('allowUpdateField')->where($queryStr)->save();

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

    /**
     * 办理入住
     */
    public function check_in(){

        echo "办理入住，begin<br>";

        $o_id = 3;// 模拟操作的订单号

        $checkIN['status'] = self::STATUS_CHECKIN;

        
        $room_ID = '228 test';// 模拟分配的房间号

        
        $order_model = D('OrderRecord');

        $old_status = $order_model->where("o_id = $o_id")->getField('status');
        echo "old_status = $old_status";
        if ($old_status == $checkIN['status']) {// 状态未改变

            echo "状态未改变，不需要更新<br/>";
            return false;
        }

        if ($order_model->where("o_id = $o_id")->create($checkIN ,2)) {
            echo "create成功<br/>";

            $queryStr = "status = ".self::STATUS_NEW." or status = ".self::STATUS_PAY;
            // echo $str;
            echo " *** ". $result = $order_model->scope('allowUpdateField')->where($queryStr)->save();

            p($order_model);

            if ($result) {
                echo "办理入住成功！<br/>";

                // 需要更新o_record_2_room表中记录
                dump(update_o_room($o_id, $room_ID));

                // 需要更新o_record_2_stime表中记录
                dump(update_o_sTime($o_id, $checkIN['status']));
            }else{

                echo "办理入住失败！<br/>";
                echo $order_model->getError();
            }
        }else{

            echo "create失败<br/>";
            echo $order_model->getError();
        }

        echo "办理入住，end<br>";
        die;
        
        $this->display();
    }

    /**
     * 办理换房
     */
    public function change_room(){

        echo "办理换房，begin<br>";

        $o_id = 3;// 模拟操作的订单号

        $new_data['room_ID'] = '228';// 模拟重新分配的房间号

        $o_room_model = M('o_record_2_room');

        $old_data = $o_room_model->where("o_id = $o_id")->find();
        echo "old_data[room_ID] = ".$old_data['room_ID']."<br/>";
        if ($old_data['room_ID'] == $new_room['room_ID']) {// 房间未改变

            echo "房间未改变，不需要更新<br/>";
            return false;
        }

        // 记录note
        $new_data['note'] = $old_data['note'].'换房: '.$old_data['room_ID']." -> ".$new_data['room_ID'].";";

        if ($o_room_model->where("o_id = $o_id")->create($new_data ,2)) {
            echo "create成功<br/>";

            echo " *** ". $result = $o_room_model->scope('allowUpdateField, checkIN')->save();

            p($o_room_model);

            if ($result) {
                echo "办理换房成功！<br/>";
            }else{

                echo "办理换房失败！<br/>";
                echo $o_room_model->getError();
            }
        }else{

            echo "create失败<br/>";
            echo $o_room_model->getError();
        }

        echo "办理换房，end<br>";
        die;
        
        $this->display();
    }

    /**
     * 办理退房
     */
    public function check_out(){

        echo "办理退房，begin<br>";

        $o_id = 3;// 模拟操作的订单号

        $checkOut['status'] = self::STATUS_CHECKOUT;
        
        $order_model = D('OrderRecord');

        $old_status = $order_model->where("o_id = $o_id")->getField('status');
        echo "old_status = $old_status";
        if ($old_status == $checkOut['status']) {// 状态未改变

            echo "状态未改变，不需要更新<br/>";
            return false;
        }

        if ($order_model->where("o_id = $o_id")->create($checkOut ,2)) {
            echo "create成功<br/>";

            echo " *** ". $result = $order_model->scope('allowUpdateField, checkIN')->save();

            p($order_model);

            if ($result) {
                echo "办理退房成功！<br/>";

                // 需要更新d_record_2_stime表中记录
                dump(update_o_sTime($o_id, $checkOut['status']));
            }else{

                echo "办理退房失败！<br/>";
                echo $order_model->getError();
            }
        }else{

            echo "create失败<br/>";
            echo $order_model->getError();
        }

        echo "办理退房，end<br>";
        die;
        
        $this->display();
    }
}