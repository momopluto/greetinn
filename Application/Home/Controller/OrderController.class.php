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
    

    /*
    (
        [o_id] => 31126
        [book_info] => {"number":1,"people_info":[{"name":"刘恩坚","ID_card":"441423199305165018"}],"note":""}
        [style] => 0
        [type] => 4
        [source] => 4
        [a_id] => 3
        [g_id] => 12
        [pay_mode] => 0
        [price] => 808
        [deposit] => 0
        [phone] => 18826481053
        [status] => 1
        [cTime] => 2015-03-08 10:13:23
        [name] => 刘恩坚
        [ID_card] => 441423199305165018
        [style_name] => 普通
        [type_name] => 标准单间
        [source_name] => 团购 (不可选)
        [groupon_name] => 去哪儿网
        [agent_name] => 黄冠龙
        [a_phone] => 15017528599
        [a_price] => 
        [room_ID] => 413
        [nights] => 1
        [A_date] => 2015-03-08 12:00:00
        [B_date] => 2015-03-09 12:00:00
        [note] => 
        [cancel] => 
        [pay] => 
        [checkIn] => 
        [checkOut] => 
    )
    */
    /**
     * 未完成订单
     */
    public function dealing(){
        
        // var_dump(I('get.date'));die;
        $f_sql = "";
        if (($fliter = I('get.date') /*'2015-05-26'*/) != '') {

            // 过滤条件为"入住时间"
            $f_sql = "checkIn BETWEEN '" . $fliter." 00:00:00" . "' AND '" . $fliter." 23:59:59" . "' AND ";
        }

        // echo $f_sql;
        
        $o_record_model = D('OrderRecordView');

        $data = $o_record_model->where($f_sql . '(status != 0 AND status != 4)')->order('room_ID,cTime desc')->select();

        // p($o_record_model);
        // p($data);die;
        // p($o_record_model);

        // die;
        
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 已完成订单
     */
    public function complete(){

        $f_sql = "";
        if (($fliter = I('get.date') /*'2015-05-26'*/) != '') {

            // 过滤条件为"入住时间"
            $f_sql = "checkIn BETWEEN '" . $fliter." 00:00:00" . "' AND '" . $fliter." 23:59:59" . "' AND ";
        }

        // echo $f_sql;
        
        $o_record_model = D('OrderRecordView');

        $data = $o_record_model->where($f_sql . '(status = 0 or status = 4)')->order('cTime desc')->select();

        // p($data);die;

        // p($o_record_model);
        
        $this->assign('data', $data);
        $this->display();
    }


    /**
     * 完善客户信息
     */
    public function perfect(){

        if (IS_POST) {
            
            // p(I('post.'));die;

            $o_id = I('post.id');

            $row = is_IDCard_exists(I('post.ID'), 'client');
            // p($row);die;
            if ($row) {// 存在
                
                $update['client_ID'] = $row['client_ID'];
                // 姓名、手机
                if (!I('post.name')) {
                    $this->error('姓名不能为空！');
                }
                if (!I('post.phone')) {
                    $this->error('手机不能为空！');
                }

            }else{// 不存在，注册取得client_ID

                // 验证必要信息是否合法
                $reg_data['name'] = I('post.name');// '刘momo';//
                $reg_data['ID_card'] = I('post.ID');// '441423199305165018';//
                $reg_data['gender'] = I('post.sex');
                $reg_data['phone'] = I('post.phone');

                $Client = D('Client');

                $Client->startTrans();// 启动事务

                if ($Client->create($reg_data)){
                    echo "测试，自动验证&完成，成功！";

                    $client_ID = $Client->add();

                    if ($client_ID === false) {
                        $this->error('写入数据库失败！');
                        return;
                    }

                    $log_Arr = array($this->log_model, $this->log_data, $Client, self::RECEPTIONIST_HELP_REGISTER, 'reg', array('客户id' => $client_ID, '身份证' => $reg_data['ID_card']));
                    //                     0                 1                2             3                4                            5
                    write_log_all_array($log_Arr);
                    // write_log_all($this->log_model, $this->log_data, $Client, self::RECEPTIONIST_HELP_REGISTER, 'reg', array('客户id' => $client_ID, '身份证' => $reg_data['ID_card']));

                    // $this->success('注册成功！', U('Home/Client/order')."?id=".$reg_data['ID_card']);

                    $update['client_ID'] = $client_ID;
                }else{
                    echo "测试，自动验证&完成，失败！";
                    // echo $Client->getError();

                    $this->error($Client->getError());
                    return;
                }
            }

            // die;

            $order_model = M('o_record');

            $old_data = $order_model->find($o_id);
            // p($old_data);die;

            $book_info = json_decode($old_data['book_info'], true);
            $book_info['people_info'][0]['name'] = I('post.name');
            $book_info['people_info'][0]['ID_card'] = I('post.ID');

            $update['book_info'] = json_encode($book_info, JSON_UNESCAPED_UNICODE);
            $update['phone'] = I('post.phone');
            // 更新o_id订单的client_ID
            if ($order_model->where("o_id = $o_id")->setField($update)) {
                
                // echo "完善信息成功！";
                $this->success('完善信息成功！', U('Home/Order/dealing'));
                return;
            }else{
                
                $this->error("完善信息更新失败！");
                return;
            }
            
        }else{

            if (!I('get.id')) {
                $this->error('ERROR, id不能为空！');
                return;
            }

            $o_id = I('get.id');

            $o_record_model = D('OrderRecordView');
            $data = $o_record_model->where("o_record.o_id = $o_id")->find();

            $info['o_id'] = $o_id;
            $book_info = json_decode($data['book_info'], true);
            $info['name'] = $book_info['people_info'][0]['name'];
            $info['phone'] = $data['phone'];
            // p($info);die;
            $this->assign('data', $info);
            $this->display('reg');
        }
    }

    /**
     * 编辑订单
     */
    public function edit(){
        
        if (!I('get.id')) {
            $this->error('ERROR, id不能为空！');
            return;
        }

        if (IS_POST) {
            
        }else{

            $o_id = I('get.id');

            $o_record_model = D('OrderRecordView');
            $data = $o_record_model->where("o_record.o_id = $o_id")->find();
            $data['A_date'] = explode(' ', $data['A_date'])[0];
            $data['B_date'] = explode(' ', $data['B_date'])[0];
            // p($data);die;

            $types = M('type')->getField('type,name');// 普通入住可选的房型
            $prices = M('0_price')->find($data['type']);// 普通入住，对应房型价钱
            
            // 根据不同类型订单，作相应处理
            if ($data['style'] == 0 || $data['style'] == 1) {
                $sources = M('order_source')->getField('source,name');// 来源
                unset($sources[4]);
                $agents = M("agent")->field('a_id, name, phone')->select();;
                $this->assign('agents', $agents);
            }else{
                $sources = M('groupon')->getField('g_id,name');// 来源
            }
            
            // p($sources);die;

            $this->assign('types', $types);
            $this->assign('prices', $prices);
            $this->assign('sources', $sources);
            $this->assign('data', $data);
            $this->display('edit_'.$data['style']);
        }
    }

    public function edit_0(){

        if (!I('get.id')) {
            $this->error('ERROR, id不能为空！');
            return;
        }

        if (IS_POST) {
            // p(I('post.'));die;

            // if (!check_verify(I('post.verify'))) {
                
            //     $this->error('验证码不正确！');
            //     return;
            // }
            
            // style
            // ID身份证,aDay,bDay,type,price,(room),source,mode,note,info,phone

            // $client = M('client')->where(array('ID_card'=>I('post.ID')))->find();
            // if (!$client) {
            //     $this->error('该身份证未注册！', U('Home/Client/reg'));
            //     return;
            // }

            $o_id = I('get.id');

            if (strtotime(I('post.aDay')) >= strtotime(I('post.bDay')) || strtotime(I('post.aDay')) < strtotime(date('Y-m-d',time()))) {
                $this->error('入住/退房时间错误！');
                return;
            }

            if (I('post.price') == '') {
                $this->error('价格为空！请重新选择！');
                return;
            }

            // o_record_2_room数据
            if (I('post.room') == '') {// 防止，验证码错误导致的预分配房间信息丢失问题
                $this->error('预分配房间信息错误！');
                return;
            }
            if (I('post.room') != -1) {
                $edit_order_2_room['room_ID'] = I('post.room');
            }else{
                $edit_order_2_room['room_ID'] = '';
            }
            $edit_order_2_room['A_date'] = I('post.aDay');
            $edit_order_2_room['B_date'] = I('post.bDay');
            // 计算2个日期间隔天数
            $interval = date_diff(date_create($edit_order_2_room['A_date']), date_create($edit_order_2_room['B_date']));
            $edit_order_2_room['nights'] = $interval->format('%a');
            $edit_order_2_room['A_date'] .= self::IN_TIME;// 入住时间
            if (I('post.mode') == 2) {
                $edit_order_2_room['B_date'] .= self::OUT_TIME_2;// 会员离店时间
            }else{
                $edit_order_2_room['B_date'] .= self::OUT_TIME;// 离店时间
            }

            // $client_ID = $client['client_ID'];// 客户ID
            $people_info = I('post.info');
            if ($people_info[1]['name'] == '' || $people_info[1]['ID'] == '') {
                // 去除入住人(二)信息
                unset($people_info[1]);
            }
            
            // book_info字段，JSON格式
            $book_info['number'] = count($people_info);// 入住人数
            $book_info['people_info'] = $people_info;
            $book_info['note'] = I('post.note');

            // o_record数据
            // $edit_order['client_ID'] = $client_ID;
            $edit_order['book_info'] = json_encode($book_info, JSON_UNESCAPED_UNICODE);// unicode格式

            // $edit_order['style'] = self::STYLE_0;// 订单类型
            $edit_order['type'] = I('post.type');// 房型
            $edit_order['price'] = I('post.price');// 总价
            $edit_order['source'] = I('post.source');// 来源
            if (I('post.agent')) {
                $edit_order['a_id'] = I('post.agent');// 协议人
            }
            $edit_order['pay_mode'] = I('post.mode');// 支付方式
            $edit_order['phone'] = I('post.phone');// 联系手机

            // 会员卡支付
            // 1、检查该会员卡合法性，余额是否足够支付
            // 2.合法，扣除相应金额，status = 2
            if (I('post.paid') == 1) {
                $edit_order['status'] = 2;// 已支付状态
            }
            


            // p($edit_order);
            // p($edit_order_2_room);
            // die;

            $order_model = D('OrderRecord');

            $order_model->startTrans();// 启动事务

            if ($order_model->where("o_id = $o_id")->create($edit_order, 2)) {
                echo "create成功<br/>";
                
                // 更新o_record
                $order_model->scope('allowUpdateField')->where("o_id = $o_id")->save();
                // 更新o_record_2_room
                echo "*******".M('o_record_2_room')->where("o_id = $o_id")->save($edit_order_2_room);

                // die;
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
        }
    }

    /**
     * 编辑订单
     */
    public function edit_old(){

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

            // $types = M('type_price')->getField('type,name,price');
            // // p($types);die;
            // $this->assign('types', $types);

            $styles = M('style')->getField('style,name');// 普通入住
            $types = M('type')->getField('type,name');// 普通入住可选的房型
            $prices = M('0_price')->find(0);// 普通入住，标单价钱
            $sources = M('order_source')->getField('source,name');// 来源

            $this->assign('styles', $styles);
            $this->assign('types', $types);
            $this->assign('prices', $prices);
            $this->assign('sources', $sources);
            $this->assign('data', $data);
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

        $old_data = $order_model->where("o_id = $o_id")->find();
        if (!$old_data) {
            $this->error('ERROR, 不存在此订单！');
            return;
        }
        echo "old_status = ".$old_data['status'];
        if ($old_data['status'] == $cancel['status']) {// 状态未改变

            $this->error('状态未改变！');
            return;
        }

        // pay_mode，支付方式
        // price，总价
        // status，从1/2变成0

        // p($old_data);die;

        // 根据是否付款区分记录日志内容
        if ($old_data['status'] == self::STATUS_NEW) {
            $log_type = self::RECEPTIONIST_CANCEL_ORDER;
            $log_type_Arr = array('订单id' => $o_id);
        }else{

            $log_type = self::RECEPTIONIST_CANCEL_PAID_ORDER;
            $price = $order_model->where("o_id = $o_id")->getField('price');
            $log_type_Arr = array('订单id' => $o_id, '总价' => "￥".$price, '支付方式' => $old_data['pay_mode']);

            // pay_mode = 0，返还现金
            // pay_mode = 1，网上返还
            // 会员卡余额加回去
            // pay_mode = 2
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
            $checkIN['deposit'] = I('post.deposit');
            
            $order2_model = D('OrderRecordView');
            $old_data = $order2_model->where("o_record.o_id = $o_id")->find();
            // p($old_data);die;

            echo "old_status = ".$old_data['status'];
            if ($old_data['status'] == $checkIN['status']) {// 状态未改变

                $this->error('状态未改变！');
                return;
            }

            // $vipModel = M('vip');
            // $whe['client_ID'] = $old_data['client_ID'];
            // $old_VipData = $vipModel->where($whe)->find();// 会员卡信息

            // if ($old_data['price'] == 0 && $old_VipData['first_free'] == 0) {

            //     $this->error('此会员已享用过首住免费！');
            //     return;
            // }

            $day_IN = strtotime(date('Y-m-d',$old_data['A_date']));
            if (!($day_IN < NOW_TIME && NOW_TIME < strtotime($old_data['B_date']))) {
                
                $this->error('当前非预定入住时间！无法办理入住！');
                return;
            }

            // p($checkIN);die;
            // $now = strtotime(date('Y-m-d',time())." 14:00:00");
            // if (strtotime(date('Y-m-d',time())) < strtotime($old_data['A_date']) ||  $now >= strtotime($old_data['B_date'])) {

            //     $this->error('当前非预定入住时间！无法办理入住！');
            //     return;
            // }

            $order_model = D('OrderRecord');
            $order_model->startTrans();// 启动事务
            
            if ($order_model->where("o_id = $o_id")->create($checkIN ,2)) {
                echo "create成功<br/>";
                // die;
                $queryStr = "o_id = $o_id AND (status = ".self::STATUS_NEW." or status = ".self::STATUS_PAY.")";
                // echo $str;
                echo " *** ". $result = $order_model->scope('allowUpdateField')->where($queryStr)->save();// ->scope('allowUpdateField')

                // p($order_model);

                if ($result) {
                    echo "办理入住成功！<br/>";
                    
                    // // 需要更新o_record_2_room表中记录
                    // update_o_room($o_id, $room_ID);
                    // // 需要更新o_record_2_stime表中记录
                    // update_o_sTime($o_id, $checkIN['status']);

                    if (update_o_room($o_id, $room_ID) && update_o_sTime($o_id, $checkIN['status'])) {

                        $flag = true;
                        if ($old_data['pay_mode'] == 2 && $old_data['status'] == self::STATUS_NEW) {// 会员卡消费，未支付，扣除会员卡费用

                            // echo "进这里来啦？！";die;

                            // p($old_data);die;

                            $vipModel = M('vip');
                            $vipModel->startTrans();// vip表开启事务
                            $whe['client_ID'] = $old_data['client_ID'];
                            $old_VipData = $vipModel->where($whe)->find();

                            if ($old_VipData['balance'] < $old_data['price']) {// 余额不够支付

                                $this->error('会员卡余额不足，请充值！');
                                return;
                            }

                            if ($vipModel->where($whe)->setDec('balance', $old_data['price']) === false) {
                                 
                                 echo "会员卡扣费失败！";
                                 $flag = false;
                            }

                            if ($old_data['price'] == 0 && $old_VipData['first_free'] == 1) {

                                $update_VipData['first_free'] = 0;
                                $o_stime = M('o_record_2_stime')->where("o_id = $o_id")->find();// 读取入住时间
                                $update_VipData['first_free_checkIn'] = $o_stime['checkIn'];
                                if ($vipModel->where($whe)->setField($update_VipData) === false) {
                                    
                                    echo "会员首住失败！";
                                    $flag = false;
                                }
                            }
                        }

                        if ($flag) {
                            
                            $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CHECK_IN, 'check_in', array('订单id' => $o_id, '总价' => $old_data['price']));
                            //                     0                 1                2             3                4                            5
                            if (write_log_all_array($log_Arr)) {

                                $vipModel->commit();// vip表提交事务
                                $this->success('办理入住成功！', U('Home/Order/dealing'));
                            }else{
                                
                                $vipModel->rollback();// vip表回滚事务
                                $this->error('办理入住失败！写日志出错！');
                            }
                            return;
                        }else{

                            echo "会员卡消费，扣费失败！<br/>";
                            // echo $order_model->getError();

                            $vipModel->rollback();// vip表回滚事务
                            $order_model->rollback();
                            $this->error('会员卡消费，扣费失败！');
                            return;
                        }

                    }else{

                        echo "更新房间信息失败！<br/>";
                        // echo $order_model->getError();

                        $this->error($order_model->getError());
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
                echo $order_model->getError();

                P($order_model);die;

                // $this->error($order_model->getError());
                return;
            }
        }else{
            
            if (!I('get.id')) {
                $this->error('ERROR, id不能为空！');
                return;
            }

            $o_id = I('get.id');

            $o_record_model = D('OrderRecordView');
            $data = $o_record_model->where("o_record.o_id = $o_id")->find();

            // 过滤得到空闲的房间
            $rooms = get_available_rooms($data, $data['room_ID']);

            // p($data);
            // p($rooms);
            // die;

            $this->assign('data', $data);
            $this->assign('rooms', $rooms);
            $this->display();
        }
    }

    /**
     * 办理续住
     */
    public function stay_over(){

        if (IS_POST) {
            // p(I('post.'));die;

            $o_id = I('post.id');

            $o_record_model = D('OrderRecordView');
            $old_data = $o_record_model->where("o_record.o_id = $o_id")->find();

            // p($old_data);die;

            if (strtotime(I('post.bDay')) <= strtotime($old_data['B_date'])) {
                
                $this->error('续住日期错误！');
                return;
            }

            $update_2_room['B_date'] = I('post.bDay');
            
            // 计算2个日期间隔天数
            $interval = date_diff(date_create($old_data['A_date']), date_create($update_2_room['B_date']));
            $update_2_room['nights'] = $interval->format('%a');

            $type_price = M('type_price')->find($old_data['type']);
            // p($type_price);die;
            $update_record['price'] = $type_price['price'] * $update_2_room['nights'];

            $_price = $update_record['price'] - $old_data['price'];// 差价

            $update_2_room['note'] = $old_data['note']."续住: ".$old_data['B_date']." -> ".$update_2_room['B_date']."(+￥".$_price."), 晚数: ".$old_data['nights']." -> ".$update_2_room['nights'].";";

            echo $update_2_room['note'];

            // p($update_2_room);die;

             $o_room_model = M('o_record_2_room');
             $o_room_model->startTrans();// 启动事务，此事务最后才提交
            // 更新o_record_2_room的B_date,nights,note
            echo "***".$res1 = $o_room_model->where("o_id = $o_id")->save($update_2_room);
            if ($res1) {
                echo "更新o_record_2_room成功！<br/>";
                
                $order_model = M('o_record');
                $order_model->startTrans();// 启动事务
                // 更新o_record的price
                echo "*****".$res2 = $order_model->where("o_id = $o_id")->save($update_record);

                if ($res2) {
                    echo "更新o_record成功！<br/>";
                    
                    $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_STAY_OVER, 'stay_over', array('订单id' => $o_id, '补交金额' => $_price));
                    //                     0                 1                2             3                4                            5
                    write_log_all_array($log_Arr);
                    // write_log_all($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_STAY_OVER, 'stay_over', array('房间id' => $o_id, '补交金额' => $_price));

                    $o_room_model->commit();// o_record_2_room提交事务
                    
                    $this->success('办理续住成功！', U('Home/Order/dealing'));
                    return;
                }else{
                    
                    $o_room_model->rollback();// o_record_2_room回滚事务
                    echo "更新o_record失败！<br/>";
                    // echo $order_model->getError();

                    $this->error($order_model->getError());
                    return;
                }
                
            }else{
                
                $o_room_model->rollback();// o_record_2_room回滚事务
                echo "更新o_record_2_room失败！<br/>";
                // echo $o_room_model->getError();

                $this->error($o_room_model->getError());
                return;
            }
            
            
        }else{
            if (!I('get.id')) {
                $this->error('ERROR, id不能为空！');
                return;
            }

            $o_id = I('get.id');

            $o_record_model = D('OrderRecordView');
            $data = $o_record_model->where("o_record.o_id = $o_id")->find();

            $this->assign('data', $data);
            $types = M('type_price')->getField('type,name,price');
            // p($types);die;
            $this->assign('types', $types);
            $this->display();
        }
    }

    /**
     * 办理换房
     */
    public function change_room(){

        if (IS_POST) {
            
            $o_id = I('post.id');
            $new_data['room_ID'] = I('post.room');

            $o_room_model = M('o_record_2_room');

            $old_data = $o_room_model->where("o_id = $o_id")->find();
            echo "old_data[room_ID] = ".$old_data['room_ID']."<br/>";
            if ($old_data['room_ID'] == $new_data['room_ID']) {// 房间未改变

                $this->error('房间未改变！');
                return;
            }

            // 记录note
            $new_data['note'] = $old_data['note'].'换房: '.$old_data['room_ID']." -> ".$new_data['room_ID'].";";

            $o_room_model->startTrans();// 启动事务

            if ($o_room_model->where("o_id = $o_id")->create($new_data ,2)) {
                echo "create成功<br/>";

                echo " *** ". $result = $o_room_model->scope('allowUpdateField, checkIN')->save();

                // p($o_room_model);
                
                if ($result) {
                    echo "办理换房成功！<br/>";

                    $log_Arr = array($this->log_model, $this->log_data, $o_room_model, self::RECEPTIONIST_CHANGE_ROOM, 'change_room', array('订单id' => $o_id));
                    //                     0                 1                2             3                4                            5
                    write_log_all_array($log_Arr);
                    // write_log_all($this->log_model, $this->log_data, $o_room_model, self::RECEPTIONIST_CHANGE_ROOM, 'change_room', array('房间id' => $o_id));

                    $this->success('办理换房成功！', U('Home/Order/dealing'));
                    return;
                }else{

                    echo "办理换房失败！<br/>";
                    // echo $o_room_model->getError();

                    $this->error($o_room_model->getError());
                    return;
                }
            }else{

                echo "create失败<br/>";
                // echo $o_room_model->getError();

                $this->error($o_room_model->getError());
                return;
            }
        }else{

            if (!I('get.id')) {
                $this->error('ERROR, id不能为空！');
                return;
            }

            $o_id = I('get.id');

            // 根据o_id得到该条订单详情，today,B_date
            // 按房型type，得到该房型总开放的房间数
            // 除了该订单当前正在入住的房间，找到所有涉及(today, B_date)此区域的已分配的房间，减去，得到剩下的各房型房间

            $o_record_model = D('OrderRecordView');
            $data = $o_record_model->where("o_record.o_id = $o_id")->find();

            // 过滤得到空闲的房间
            $rooms = get_available_rooms($data, $data['room_ID']);
            
            $this->assign('data', $data);
            $this->assign('rooms', $rooms);
            $this->display();
        }
    }

    /**
     * 办理退房
     */
    public function check_out(){

        if (IS_POST) {
            // p(I('post.'));die;
            
            $o_id = I('post.id');

            $checkOut['status'] = self::STATUS_CHECKOUT;
            
            $order_model = D('OrderRecord');

            $old_status = $order_model->where("o_id = $o_id")->getField('status');
            echo "old_status = $old_status";
            if ($old_status == $checkOut['status']) {// 状态未改变

                echo "状态未改变，不需要更新<br/>";
                return false;
            }

            $order_model->startTrans();// 启动事务

            if ($order_model->where("o_id = $o_id")->create($checkOut ,2)) {
                echo "create成功<br/>";

                echo " *** ". $result = $order_model->scope('allowUpdateField, checkIN')->save();

                // p($order_model);

                if ($result) {
                    echo "办理退房成功！<br/>";

                    // 需要更新d_record_2_stime表中记录
                    if (update_o_sTime($o_id, $checkOut['status'])) {
                        $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CHECK_OUT, 'check_out', array('订单id' => $o_id));
                        //                     0                 1                2             3                4                            5
                        write_log_all_array($log_Arr);
                        // write_log_all($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CHECK_OUT, 'check_out', array('房间id' => $o_id));

                        $this->success('办理换退房成功！', U('Home/Order/dealing'));
                        return;
                    }
                }else{

                    echo "办理退房失败！<br/>";
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

            $o_record_model = D('OrderRecordView');
            $data = $o_record_model->where("o_record.o_id = $o_id")->find();

            // p($data);die;

            $this->assign('data', $data);
            $this->display();
        }
    }
}