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
        
        $o_record_model = D('OrderRecordView');

        $data = $o_record_model->where('status != 0 AND status != 4')->order('cTime desc')->select();

        // p($data);
        // p($o_record_model);

        // die;
        
        $this->assign('data', $data);
        $types = M('type_price')->getField('type,name,price');
        // p($types);die;
        $this->assign('types', $types);
        $this->display();
    }

    /**
     * 已完成订单
     */
    public function complete(){

        $o_record_model = D('OrderRecordView');

        $data = $o_record_model->where('status = 0 or status = 4')->order('cTime desc')->select();

        // p($data);

        // p($o_record_model);
        
        $this->assign('data', $data);
        $types = M('type_price')->getField('type,name,price');
        // p($types);die;
        $this->assign('types', $types);
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

        if (!I('get.id')) {
            $this->error('ERROR, id不能为空！');
            return;
        }

        if (IS_POST) {
            // echo strtotime(I('post.aDay'))."***".NOW_TIME;die;
            if (strtotime(I('post.aDay')) > strtotime(I('post.bDay')) || strtotime(I('post.aDay')) < NOW_TIME) {
                $this->error('入住/退房时间错误！');
                return;
            }
            
            $o_id = I('get.id');

            $info = I('post.info');
            // p($info);
            if ($info[2]['name'] == '' || $info[2]['ID'] == '') {
                // 去除入住人(二)信息
                unset($info[2]);
            }
            $people_info = $info;


            $book_info['start_date'] = I('post.aDay');
            $book_info['leave_date'] = I('post.bDay');
            
            // 计算2个日期间隔天数
            $interval = date_diff(date_create($book_info['start_date']), date_create($book_info['leave_date']));
            $book_info['nights'] = $interval->format('%a');
            
            $book_info['number'] = count($people_info);// 入住人数
            $book_info['people_info'] = $people_info;
            $book_info['note'] = I('post.note');

            // 还需要更新以下几项数据
            $edit_order_2_room['nights'] = $book_info['nights'];
            $edit_order_2_room['A_date'] = $book_info['start_date'];
            $edit_order_2_room['B_date'] = $book_info['leave_date'];
            unset($book_info['nights']);
            unset($book_info['start_date']);
            unset($book_info['leave_date']);
            $edit_order['book_info'] = json_encode($book_info, JSON_UNESCAPED_UNICODE);// unicode格式

            $edit_order['type'] = I('post.type');
            $type_price = M('type_price')->find(I('post.type'));
            // p($type_price);die;
            $edit_order['price'] = $type_price['price'] * $edit_order_2_room['nights'];
            $edit_order['phone'] = I('post.phone');

            // p($edit_order);

            $order_model = D('OrderRecord');

            $order_model->startTrans();// 启动事务

            if ($order_model->where("o_id = $o_id")->create($edit_order, 2)) {
                echo "create成功<br/>";
                
                // 更新o_record
                $order_model->scope('allowUpdateField')->where("o_id = $o_id")->save();
                // 更新o_record_2_room
                M('o_record_2_room')->where("o_id = $o_id")->save($edit_order_2_room);
                $result = array_merge((array)$edit_order, (array)$edit_order_2_room);
                // unset($result['book_info']);
                // p($result);die;
                $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_EDIT_ORDER, 'edit', array('订单id' => $o_id), $result);
                //                     0                 1                2             3                4                            5
                write_log_all_array($log_Arr);
                // write_log_all($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_EDIT_ORDER, 'edit', array('订单id' => $o_id), $result);

                $this->success('编辑订单成功！', U('Home/Order/dealing'));
                return;
            }else{

                echo "create失败<br/>";
                // echo $order_model->getError();

                $this->error($order_model->getError());
                return;
            }
        }else{

            $o_id = I('get.id');

            $o_record_model = D('OrderRecordView');
            $data = $o_record_model->where("o_record.o_id = $o_id")->find();
            // p($data);die;

            $this->assign('data', $data);
            $types = M('type_price')->getField('type,name,price');
            // p($types);die;
            $this->assign('types', $types);
            $this->display();

        }

    }

    /**
     * 取消订单（分：未付款，已付款）
     */
    public function cancel(){

        if (!I('get.id')) {
            $this->error('ERROR, id不能为空！');
            return;
        }

        $o_id = I('get.id');

        $cancel['status'] = self::STATUS_CANCEL;
        
        $order_model = D('OrderRecord');

        $old_status = $order_model->where("o_id = $o_id")->getField('status');
        echo "old_status = $old_status";
        if ($old_status == $cancel['status']) {// 状态未改变

            echo "状态未改变，不需要更新<br/>";
            return false;
        }

        // 根据是否付款区分记录日志内容
        if ($old_status == 1) {
            $log_type = self::RECEPTIONIST_CANCEL_ORDER;
            $log_type_Arr = array('订单id' => $o_id);
        }else{
            $log_type = self::RECEPTIONIST_CANCEL_PAID_ORDER;
            $price = $order_model->where("o_id = $o_id")->getField('price');
            $log_type_Arr = array('订单id' => $o_id, '总价' => "￥".$price);
        }

        $order_model->startTrans();// 启动事务

        if ($order_model->where("o_id = $o_id")->create($cancel ,2)) {
            echo "create成功<br/>";

            $queryStr = "o_id = ".$o_id." and (status = ".self::STATUS_NEW." or status = ".self::STATUS_PAY.")";
            
            echo " *** ". $result = $order_model->scope('allowUpdateField')->where($queryStr)->save();
            // p($order_model);die;
            
            if ($result) {
                echo "取消成功！<br/>";

                // 需要更新d_record_2_stime表中记录
                if (update_o_sTime($o_id, $cancel['status'])) {
                    
                    $log_Arr = array($this->log_model, $this->log_data, $order_model, $log_type, 'cancel', $log_type_Arr);
                    //                     0                 1                2             3                4                            5
                    write_log_all_array($log_Arr);
                    // write_log_all($this->log_model, $this->log_data, $order_model, $log_type, 'cancel', $log_type_Arr);

                    $this->success('取消成功！', U('Home/Order/dealing'));
                    return;
                }
            }else{

                echo "取消失败！<br/>";
                // echo $order_model->getError();

                $this->error($order_model->getError());
                return;
            }
        }else{

            echo "create失败<br/>";
            // echo $order_model->getError();
            
            $this->error($order_model->getError());
            return;
        }
    }

    /**
     * 办理入住
     */
    public function check_in(){

        if (IS_POST) {

            // p(I('post.'));die;
            
            $o_id = I('post.id');
            $room_ID = I('post.room');

            $checkIN['status'] = self::STATUS_CHECKIN;
            
            $order_model = D('OrderRecord');

            $old_status = $order_model->where("o_id = $o_id")->getField('status');
            echo "old_status = $old_status";
            if ($old_status == $checkIN['status']) {// 状态未改变

                echo "状态未改变，不需要更新<br/>";
                return false;
            }

            $order_model->startTrans();// 启动事务

            if ($order_model->where("o_id = $o_id")->create($checkIN ,2)) {
                echo "create成功<br/>";
                // die;
                $queryStr = "o_id = $o_id AND (status = ".self::STATUS_NEW." or status = ".self::STATUS_PAY.")";
                // echo $str;
                echo " *** ". $result = $order_model->scope('allowUpdateField')->where($queryStr)->save();

                // p($order_model);

                if ($result) {
                    echo "办理入住成功！<br/>";
                    
                    // // 需要更新o_record_2_room表中记录
                    // update_o_room($o_id, $room_ID);
                    // // 需要更新o_record_2_stime表中记录
                    // update_o_sTime($o_id, $checkIN['status']);

                    if (update_o_room($o_id, $room_ID) && update_o_sTime($o_id, $checkIN['status'])) {
                        
                        $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CHECK_IN, 'check_in', array('订单id' => $o_id, '总价' => I('post.price')));
                        //                     0                 1                2             3                4                            5
                        write_log_all_array($log_Arr);
                        // write_log_all($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CHECK_IN, 'check_in', array('房间id' => $o_id, '总价' => I('post.price')));

                        $this->success('[助]提交订单成功！', U('Home/Order/dealing'));
                        return;
                    }
                }else{

                    echo "办理入住失败！<br/>";
                    // echo $order_model->getError();

                    $this->error($order_model->getError());
                    return;
                }
            }else{

                echo "create失败<br/>";
                // echo $order_model->getError();

                $this->error($order_model->getError());
                return;
            }
        }else{
            
            if (!I('get.id')) {
                $this->error('ERROR, id不能为空！');
                return;
            }

            $o_id = I('get.id');

            // 根据o_id得到该条订单详情，A_date,B_date
            // 按房型type，得到该房型总开放的房间数
            // 找到所有涉及(A_date, B_date)此区域的已分配的房间，减去，得到剩下的各房型房间

            $o_record_model = D('OrderRecordView');
            $data = $o_record_model->where("o_record.o_id = $o_id")->find();

            $room_model = M('room');
            $rooms = $room_model->where(array('type' => $data['type']))->getField('room_ID', true);
            
            
            // ->where("cTime between '".$last_month_days[0]."' and '".$month_days[1]."'")
            $_2_room_model = M('o_record_2_room');

            // room_ID非空，时间区间有交集
            $queryStr = "room_ID is not null" . " AND "
                        . "NOT (A_date >= '".$data['B_date']."' OR B_date <= '".$data['A_date']."')";
            // echo $queryStr;
            $_rooms = $_2_room_model->where($queryStr)->getField('room_ID', true);// 需要去除的房间

            // p($data);
            // p($rooms);
            // p($_rooms);
            if ($_rooms) {
                $rooms = array_diff($rooms, $_rooms);// 该房型下，可分配的房间
            }
            // p($rooms);die;

            $this->assign('data', $data);
            $this->assign('rooms', $rooms);
            $types = M('type_price')->getField('type,name,price');
            // p($types);die;
            $this->assign('types', $types);
            $this->display();
        }
    }

    /**
     * 办理续住
     */
    public function stay_over(){
        
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