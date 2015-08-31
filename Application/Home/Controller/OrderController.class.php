<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 订单控制器
 */
class OrderController extends HomeController {

    // 操作状态
    const STATUS_CANCEL          =   0;      //  状态值，取消
    const STATUS_NEW             =   1;      //  状态值，预订未支付
    const STATUS_PAY             =   2;      //  状态值，预订已支付
    const STATUS_CHECKIN         =   3;      //  状态值，入住
    const STATUS_CLEARED         =   4;      //  状态值，退房(空净)
    const STATUS_CHECKOUT        =   5;      //  状态值，退房(未打扫&锁定)
   
    /**
     * 打印订单信息
     */
    public function print_o(){

        $o_id = I('get.id');
        // $o_id = 32374;
        $o_record_model = D('OrderRecordView');
        $map['o_id'] = $o_id;
        $temp = $o_record_model->where($map)->find();

        // p($temp);die;

        $data['pTime'] = date('Y年m月d日 H:i',time());// 打单时间
        $json_Arr = json_decode($temp['book_info'], true);
        // p($json_Arr);die;
        $data['name'] = $json_Arr['people_info'][0][0]['name'];// 入住人姓名
        $data['o_id'] = $temp['o_id'];// 订单号
        $data['startDay'] = date('Y-m-d H:i',strtotime($temp['A_date']));// 入住时间
        $data['endDay'] = date('Y-m-d H:i',strtotime($temp['B_date']));// 离店时间
        $data['nights'] = $temp['nights'];
        $data['room_ID'] = $temp['room_ID'];// 房间号
        $data['type_name'] = $temp['type_name'];// 房间类型名

        // 订单1晚的价格，去价格表中比对(如果找不到，则为特殊优惠)，确认其是属于哪种优惠
        // 在价格表中取得 "标价"，再根据订单是哪种优惠，相减得出优惠金额
        $one_night_price = $temp['price'] / $temp['nights'];// 订单1晚的价格
        $price_model = M($temp['style'].'_price');
        $whe['type'] = $temp['type'];
        $price_row = $price_model->where($whe)->find();// 此"类型+房型"的价格表

        $bid_price = $price_row['bid_price'];// 标价，原价
        $data['old_price'] = number_format($bid_price * $temp['nights'],2);// 按标价计的价格

        $price_type = "spec_price";// 特殊价格
        foreach ($price_row as $key => $value) {
            if ($value == $one_night_price) {// 确定订单享用的价格
                // 记录下该key。break;
                $price_type = $key;
                break;
            }
        }

        // 确定优惠类型
        switch ($price_type) {
            case 'bid_price':
                $discount_type = "无";
                break;
            case 'stu_price':
                $discount_type = "学生";
                break;
            case 'agent_price':
                $discount_type = "代理";
                break;
            case 'vip_price':
                $discount_type = "会员";
                break;
            case 'groupon_price':
                $discount_type = "团购";
                break;
            default:
                $discount_type = "特殊";
                break;
        }
        $data['discount_name'] = $discount_type . "优惠";
        $data['discount'] = number_format(($one_night_price - $bid_price) * $temp['nights'], 2);// 总优惠差额=1晚优惠差额*n
        
        $data['deposit'] = number_format($temp['deposit'],2);// 押金
        $data['total'] = number_format($temp['price'] + $temp['deposit'],2);// 合计=订单价格+押金

        // p($data);die;

        $this->assign("data", $data);
        $this->display('print');

    }

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

        // p($data);
        // die;

        // p($o_record_model);
        
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 编辑订单
     */
    public function edit(){

        if (IS_POST) {

            // p(I('post.'));
            // die;

            if (!check_verify(I('post.verify'))) {
                
                $this->error('验证码不正确！');
                return;
            }

            $temp = I('post.');
            $o_id = $temp['id'];
            $order2_model = D('OrderRecordView');
            $old_data = $order2_model->where("o_record.o_id = $o_id")->find();

            if ($old_data['status'] == self::STATUS_PAY) {
                // 已支付订单
                
                if ($temp['room'] == '') {// 防止，验证码错误导致的预分配房间信息丢失问题
                    $this->error('预分配房间信息错误！');
                    return;
                }

                if ($temp['room'] != -1) {
                    $room_ID = $temp['room'];
                }else {
                    $this->error('已支付订单，必须预分配房间！');
                    return;
                }

                if ($temp['style'] == self::STYLE_2) {/*团购，来源特殊处理*/

                    $update_order['source'] = 3;// 来源固定为“团购”
                    $update_order['g_id'] = $temp['source'];// 团购平台id
                }else {

                    $update_order['source'] = $temp['source'];// 来源
                }

                $people_info[] = $temp['info'];
                $people_info[] = $temp['phone'];
                
                // book_info字段，JSON格式
                $book_info['number'] = count($people_info[0]);// 入住人数
                $book_info['people_info'] = $people_info;
                $book_info['note'] = $temp['note'];
                $update_order['book_info'] = json_encode($book_info, JSON_UNESCAPED_UNICODE);// unicode格式
                $update_order['o_id'] = $o_id;


                // p($update_order);
                // echo $room_ID;
                // die;

                $order_model = D('OrderRecord');
                $order_model->startTrans();// 启动事务

                if ($order_model->save($update_order)) {
                    echo "更新o_record成功！";

                    if (update_o_room($o_id, $room_ID) && update_o_sTime($o_id, $old_data['status'], $old_data['status'])) {
                        echo "更新o_record_2_room成功！更新o_record_2_stime成功！";

                        $order_model->commit();
                        $this->success('更新订单成功！', U('Home/Order/dealing'));
                        return;
                    }else{
                        $order_model->rollback();
                        echo "更新o_record_2_room失败！或者更新o_record_2_stime失败！";
                    }
                }else{
                    $order_model->rollback();
                    echo $order_model->getError();
                }

            }else{// 未支付订单

                $update_order['a_id'] = '';// 初始，代理人为空
                $update_order['g_id'] = '';// 初始，团购来源为空

                $PRICE_TYPE = "standard";// standard(标准:标价,学生价) agent(代理价) vip(会员价) special(高级)
                
                $leave_time = self::OUT_TIME;// 普通顾客离店时间

                // 判断使用的价格类型
                if (array_key_exists("verifyPwd", $temp)) {
                    $PRICE_TYPE = "special";

                    if (strcmp(md5($temp['verifyPwd']), self::SPEC_PWD) !== 0) {
                        $this->error('"高级"验证密码错误！');
                        return;
                    }

                }else if(array_key_exists("agent", $temp)){
                    $PRICE_TYPE = "agent";

                    if (!$temp['agent']) {
                        $this->error('"代理人"信息有误！');
                        return;
                    }

                    $update_order['a_id'] = $temp['agent'];// 协议人

                }else if($temp['mode'] == 2){
                    $PRICE_TYPE = "vip";

                    if ($temp['paid'] != 0) {
                        $this->error('会员卡支付"付款状态"有误！');
                        return;
                    }

                    // $update_order['pay_mode'] = 2;
                    // $update_order['status'] = 1;
                    $leave_time = self::OUT_TIME_2;// 会员离店时间
                }

                $today = date("Y-m-d");
                $yesterday = date("Y-m-d",strtotime("$today -1 day"));
                if (strtotime($temp['aDay']) >= strtotime($temp['bDay']) || strtotime($temp['aDay']) < strtotime($yesterday)) {
                    // (入住时间 < 离店时间) && (入住时间 >= 昨天)
                    $this->error('入住/退房时间错误！');
                    return;
                }

                if ($temp['price'] == '') {
                    $this->error('价格为空！请重新选择！');
                    return;
                }

                if ($temp['room'] == '') {// 防止，验证码错误导致的预分配房间信息丢失问题
                    $this->error('预分配房间信息错误！');
                    return;
                }

                // o_record_2_room数据
                // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                if ($temp['room'] != -1) {
                    $update_order_2_room['room_ID'] = $temp['room'];
                }else {
                    if ($temp['paid'] == 1) {
                        $this->error('已支付订单，必须预分配房间！');
                        return;
                    }
                }
                $update_order_2_room['A_date'] = $temp['aDay'];
                $update_order_2_room['B_date'] = $temp['bDay'];
                // 计算2个日期间隔天数
                $interval = date_diff(date_create($update_order_2_room['A_date']), date_create($update_order_2_room['B_date']));
                $update_order_2_room['nights'] = $interval->format('%a');
                if ($temp['style'] != self::STYLE_1) {/*非钟点房，加上时间*/
                    
                    $update_order_2_room['A_date'] .= self::IN_TIME;// 入住时间
                    $update_order_2_room['B_date'] .= $leave_time;// 离店时间(已判断是否为会员)
                }
                // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

                $people_info[] = $temp['info'];
                $people_info[] = $temp['phone'];
                
                // book_info字段，JSON格式
                $book_info['number'] = count($people_info[0]);// 入住人数
                $book_info['people_info'] = $people_info;
                $book_info['note'] = $temp['note'];
                $update_order['book_info'] = json_encode($book_info, JSON_UNESCAPED_UNICODE);// unicode格式
                $update_order['type'] = $temp['type'];// 房型
                if ($temp['style'] == self::STYLE_1) {/*钟点房，总价另算*/
                    
                    $update_order['price'] = $temp['price'] * $temp['quantity'];// 总价
                }else {

                    $update_order['price'] = $temp['price'] * $update_order_2_room['nights'];// 总价
                }

                if ($temp['style'] == self::STYLE_2) {/*团购，来源特殊处理*/

                    $update_order['source'] = 3;// 来源固定为“团购”
                    $update_order['g_id'] = $temp['source'];// 团购平台id
                }else {

                    $update_order['source'] = $temp['source'];// 来源
                }

                $update_order['pay_mode'] = $temp['mode'];// 支付方式
                $update_order['phone'] = $temp['phone'][0];// 联系手机
                if ($temp['paid'] == 1) {
                    $update_order['status'] = 2;// 已支付状态
                }elseif ($temp['paid'] == 0){
                    $update_order['status'] = 1;// 未支付状态
                }

                $operator = json_decode($old_data['operator'],true);
                $operator['edit'] = get_Oper("name");// 经办人
                $update_order['operator'] = json_encode($operator, JSON_UNESCAPED_UNICODE);// unicode格式

                $update_order['o_id'] = $o_id;

                // p($update_order);
                // p($update_order_2_room);
                // die;

                $order_model = D('OrderRecord');
                $order_model->startTrans();// 启动事务

                if ($order_model->save($update_order)) {
                    echo "更新o_record成功！";

                    if (update_o_room($o_id, $temp['room'], $update_order_2_room) && update_o_sTime($o_id, $update_order['status'], $old_data['status'])) {
                        echo "更新o_record_2_room成功！更新o_record_2_stime成功！";

                        if ($update_order['status'] == 2) {/*未支付->已支付*/

                            // 资金管理是否开启
                            if (self::MONEY_MANAGEMENT_SWITCH) {
                                
                                // 资金流水表capital_flow数据
                                // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                                $flow['shift'] = get_Oper('shift');// 班次标识

                                $o_stime = M('o_record_2_stime')->where("o_id = $o_id")->find();
                                $flow['cTime'] = $o_stime['pay'];// 支付时间

                                $flow['in'] = $update_order['price'];// 收入，房费
                                $flow['out'] = 0;// 支出
                                $flow['type'] = 1;// 1房费

                                $capitalAdvModel = D("CapitalAdv");
                                $last_record = $capitalAdvModel->where(array('shift'=>$flow['shift']))->last();

                                // p($last_record);die;

                                $flow['pay_mode'] = $temp['mode'];// 支付方式
                                if ($flow['pay_mode'] == 0) {

                                    // 只计算现金的资金流
                                    $flow['balance'] = $last_record['balance'] + $flow['in'] - $flow['out'];//余额        
                                }else{

                                    $flow['balance'] = $last_record['balance'];
                                }       

                                $flow['info'] = "房间号：".$update_order_2_room['room_ID'];
                                $flow['operator'] = get_Oper("name");// 经办人

                                // p($flow);die;

                                if (M('capital_flow')->add($flow) === false) {

                                    $order_model->rollback();
                                    $this->error('写-房费-资金流量表-失败！');
                                    return;
                                }else{

                                    $order_model->commit();
                                    $this->success('更新订单成功！', U('Home/Order/dealing'));
                                    return;
                                }
                                // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                            }
                        }else{

                            $order_model->commit();
                            $this->success('更新订单成功！', U('Home/Order/dealing'));
                            return;
                        }
                    }else{
                        echo "更新o_record_2_room失败！或者更新o_record_2_stime失败！";
                    }
                }else{
                    echo $order_model->getError();
                }
            }
        }else {
            if (I('get.id') == '') {

                $this->error('ERROR, id不能为空！');
                return;
            }

            $o_id = I('get.id');
            $o_record_model = D('OrderRecordView');
            $one = $o_record_model->where("o_record.o_id = $o_id")->find();

            // p($one);
            // die;

            // 简单不需处理的数据
            $data['o_id'] = $o_id;
            // $data['client_ID'] = $one['client_ID'];
            $data['ID_card'] = $one['ID_card'];
            $data['pay_mode'] = $one['pay_mode'];
            $data['paid'] = $one['status'] == self::STATUS_NEW ? 0 : 1;
            if ($one['style'] != self::STYLE_1) {/*非钟点房，去除时间*/
                $data['A_date'] = substr($one['A_date'], 0, 10);
                $data['B_date'] = substr($one['B_date'], 0, 10);
            }else{
                $data['A_date'] = $one['A_date'];
                $data['B_date'] = $one['B_date'];

                $data['price'] = $one['price'];// 订单总价
                // 计算2个日期间隔天数
                $interval = date_diff(date_create($data['A_date']), date_create($data['B_date']));
                $data['quantity'] = $interval->format('%h') / 3;// 份数
            }
            $book_info = json_decode($one['book_info'], true);
            $data['book_info'] = $book_info;
            // $data['note'] = $book_info['note'];

            $this->assign("style", $one['style']);

            $typeStr = M('style_type')->where("style = %d", $one['style'])->getField('map_types');// 符合的房型字符串
            $map['type'] = array('in', $typeStr);
            $types = M('type')->where($map)->getField('type,name');// style入住可选的房型
            $data['type'] = $one['type'];// 订单房间类型
            $this->assign('types', $types);
            // echo "types";
            // p($types);

            if ($one['style'] != self::STYLE_2) {/*非团购*/
                
                $sources = M('order_source')->getField('source,name');
                unset($sources[count($sources) - 1]);// 去除 最后1个"团购"
                $data['source'] = $one['source'];// 订单来源
            }else {

                $sources = M('groupon')->getField('g_id,name');// 团购来源
                $data['source'] = $one['g_id'];// 订单来源
            }
            $this->assign('sources', $sources);

            if ($one['style'] == self::STYLE_0 || $one['style'] == self::STYLE_3) {
                // 普通 和 (节假日)普通，才有代理人
                $agents = M("agent")->field('a_id, name, phone')->select();// 代理人
                if ($one['a_id'] != '') {
                    $data['agent'] = $one['a_id'];// 订单代理人
                }
                $this->assign('agents', $agents);
            }

            $prices = M($one['style'].'_price')->find($one['type']);// style入住，type价钱
            // 找到订单所使用的价格
            if ($data['pay_mode'] == 2) {/*会员价*/
                if ($vipInfo = is_IDcard_Vip($data['ID_card'])){
                    $vipInfo_str = ' 会员卡号：<span style="color:#5bc0de">'. $vipInfo['card_ID'] .'</span>';

                    if ($vipInfo['balance'] < $one['price']) {
                        $vipInfo_str .=' 余额：<span style="color:#d9534f">'. $vipInfo['balance'] .'</span>';
                    }else{
                        $vipInfo_str .=' 余额：<span style="color:#5cb85c">'. $vipInfo['balance'] .'</span>';
                    }

                    $this->assign('vipInfo_str', $vipInfo_str);
                    $data['price_type'] = 'vip';
                }else{
                    $this->error("订单数据非法！<br/>非会员用户使用了会员卡支付！");
                    return;
                }
            }elseif($data['agent'] != ''){/*代理价*/
                $data['price_type'] = 'agent';
            }elseif($one['g_id'] != ''){/*团购价*/
                $data['price_type'] = 'groupon';
            }else{

                $one_night_price = $one['price'] / $one['nights'];// 单价
                $price_type = "spec_price";// 特殊价格
                foreach ($prices as $key => $value) {
                    if ($value == $one_night_price) {// 确定订单享用的价格
                        // 记录下该key。break;
                        $price_type = $key;
                        break;
                    }
                }

                // 确定优惠类型
                switch ($price_type) {
                    case 'bid_price':/*标价*/
                        $data['price_type'] = 'bid';
                        break;
                    case 'stu_price':/*学生价*/
                        $data['price_type'] = 'stu';
                        break;
                /*
                    case 'agent_price':
                        $discount_type = "代理";
                        break;
                    case 'vip_price':
                        $discount_type = "会员";
                        break;
                    case 'groupon_price':
                        $discount_type = "团购";
                        break;
                */
                    default:
                        $data['spec_price'] = $one_night_price;// 特殊价，单价
                        $data['price_type'] = 'spec';
                        break;
                }
            }
            $this->assign('prices', $prices);
            // echo "prices";
            // echo $data['price_type'];
            // p($prices);

            // 过滤得到空闲的房间
            $rooms = get_available_rooms($one, $one['room_ID']);
            $data['room_ID'] = $one['room_ID'];// 订单房间号
            $this->assign('rooms', $rooms);

            // p($data);
            // die;
            
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
        $order_model->startTrans();// 启动事务

        // 根据是否付款区分记录日志内容
        if ($old_data['status'] == self::STATUS_NEW) {

            $log_type = self::RECEPTIONIST_CANCEL_ORDER;
            $log_type_Arr = array('订单id' => $o_id);

        }elseif($old_data['status'] == self::STATUS_PAY){

            $log_type = self::RECEPTIONIST_CANCEL_PAID_ORDER;
            $price = $order_model->where("o_id = $o_id")->getField('price');
            $log_type_Arr = array('订单id' => $o_id, '总价' => "￥".$price, '支付方式' => $old_data['pay_mode']);

            // pay_mode = 0，返还现金
        }


        if ($order_model->where("o_id = $o_id")->create($cancel ,2)) {
            echo "create成功<br/>";

            $queryStr = "o_id = ".$o_id." and (status = ".self::STATUS_NEW." or status = ".self::STATUS_PAY.")";
            
            echo " *** ". $result = $order_model->scope('allowUpdateField')->where($queryStr)->save();
            // p($order_model);die;
            
            if ($result) {
                echo "取消成功！<br/>";

                // 需要更新d_record_2_stime表中记录
                if (update_o_sTime($o_id, $cancel['status'])) {

                    if ($old_data['status'] == self::STATUS_PAY) {
                        // 资金管理是否开启
                        if (self::MONEY_MANAGEMENT_SWITCH) {
                            
                            // 资金流水表capital_flow数据
                            // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                            $flow['shift'] = get_Oper('shift');// 班次标识

                            $o_stime = M('o_record_2_stime')->where("o_id = $o_id")->find();
                            $flow['cTime'] = $o_stime['cancel'];

                            $flow['in'] = 0;// 收入
                            $flow['out'] = $price;// 支出，退房费
                            $flow['type'] = 1;// 1房费

                            $capitalAdvModel = D("CapitalAdv");
                            $last_record = $capitalAdvModel->where(array('shift'=>$flow['shift']))->last();

                            // p($last_record);die;

                            $flow['pay_mode'] = 0;// 支付方式，固定为现金退还
                            if ($flow['pay_mode'] == 0) {

                                // 只计算现金的资金流
                                $flow['balance'] = $last_record['balance'] + $flow['in'] - $flow['out'];//余额        
                            }else{

                                $flow['balance'] = $last_record['balance'];
                            }

                            $room_info = M('o_record_2_room')->where("o_id = $o_id")->find();
                            $flow['info'] = "房间号：".$room_info['room_ID'];

                            $flow['operator'] = get_Oper("name");// 经办人

                            // p($flow);die;
                            $capitalModel = M('capital_flow');
                            if ($capitalModel->add($flow) === false) {// 房费

                                $order_model->rollback();
                                $this->error('写-房费-资金流量表-失败！');
                                return;
                            }
                            // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                        }
                    }
                    
                    $log_Arr = array($this->log_model, $this->log_data, $order_model, $log_type, 'cancel', $log_type_Arr);
                    //                     0                 1                2             3                4                            5
                    // write_log_all($this->log_model, $this->log_data, $order_model, $log_type, 'cancel', $log_type_Arr);
                    if (write_log_all_array($log_Arr)){

                        $this->success('取消成功！', U('Home/Order/dealing'));
                        return;
                    }else {
                        $this->error('取消失败！');
                        return;
                    }
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
            
            // 妈的，这里竟然没有校验输入

            $room_ID = I('post.room');

            if ($room_ID == -1) {
                $this->error("请分配房间！");
                return;
            }

            $o_id = I('post.id');
            $order2_model = D('OrderRecordView');
            $old_data = $order2_model->where("o_record.o_id = $o_id")->find();
            // p($old_data);die;

            echo "old_status = ".$old_data['status'];
            if ($old_data['status'] == self::STATUS_CHECKIN) {// 状态未改变

                $this->error('状态未改变！');
                return;
            }
            
            $day_IN = strtotime(date('Y-m-d',$old_data['A_date']));
            if (!($day_IN < NOW_TIME && NOW_TIME < strtotime($old_data['B_date']))) {
                
                $this->error('当前非预定入住时间！无法办理入住！');
                return;
            }

            // 办理入住，需要更新的信息
            // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            $checkIN['status'] = self::STATUS_CHECKIN;
            $checkIN['deposit'] = I('post.deposit');// 校验押金。表单input用了number
            
            $operator = json_decode($old_data['operator'],true);
            $operator['checkIn'] = get_Oper("name");// 经办人，增加"办理入住"
            $checkIN['operator'] = json_encode($operator, JSON_UNESCAPED_UNICODE);// unicode格式
            // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            
            // p($checkIN);die;
            
            // $vipModel = M('vip');
            // $whe['client_ID'] = $old_data['client_ID'];
            // $old_VipData = $vipModel->where($whe)->find();// 会员卡信息

            // if ($old_data['price'] == 0 && $old_VipData['first_free'] == 0) {

            //     $this->error('此会员已享用过首住免费！');
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
                    
                    // 需要更新o_record_2_room表中记录
                    // update_o_room($o_id, $room_ID);
                    // 需要更新o_record_2_stime表中记录
                    // update_o_sTime($o_id, $checkIN['status']);

                    // $order_model->rollback();
                    // die;

                    if (update_o_room($o_id, $room_ID) && update_o_sTime($o_id, $checkIN['status'])) {

                        $flag = true;
                        $vipModel = D('vip');
                        // $vipModel->startTrans();// vip表开启事务

                        // $vipModel->rollback();
                        // $order_model->rollback();
                        // die;

                        // 会员卡支付，会员表更新
                        // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                        if ($old_data['pay_mode'] == 2 && $old_data['status'] == self::STATUS_NEW) {// 会员卡消费，未支付，扣除会员卡费用

                            // p($old_data);die;

                            $whe['client_ID'] = $old_data['client_ID'];
                            $old_VipData = $vipModel->where($whe)->find();

                            if ($old_VipData['balance'] < $old_data['price']) {// 余额不够支付

                                echo "会员卡余额不足，请充值！";
                                $flag = false;
                                // $this->error('会员卡余额不足，请充值！');
                                // return;
                            }

                            $o_stime = M('o_record_2_stime')->where("o_id = $o_id")->find();
                            if ($old_data['price'] == 0 && $old_VipData['first_free'] == 1) {

                                $update_VipData['first_free'] = 0;
                                $update_VipData['first_free_checkIn'] = $o_stime['checkIn'];// 读取入住时间
                                if ($vipModel->where($whe)->setField($update_VipData) === false) {
                                    
                                    echo "会员首住失败！";
                                    $flag = false;
                                }
                            }else{

                                if ($vipModel->where($whe)->setDec('balance', $old_data['price']) === false) {
                                     
                                     echo "会员卡扣费失败！";
                                     $flag = false;
                                }
                            }

                            // 会员卡记录表vip_record数据
                            // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                            $record['client_ID'] = $old_VipData['client_ID'];
                            $record['card_ID'] = $old_VipData['card_ID'];
                            $record['cTime'] = $o_stime['checkIn'];// 办理入住时间

                            $new_VipData = $vipModel->where($whe)->find();
                            $record['balance'] = $new_VipData['balance'];// 余额
                            
                            $record['style'] = self::VIP_STYLE_4;// 消费
                            $record['amount'] = $old_data['price'];// 金额=订单总价
                            $record['operator'] = get_Oper("name");// 经办人
                            $record['o_id'] = $o_id;// 订单号

                            // p($old_VipData);
                            // P($record);
                            // die;

                            if (M('vip_record')->add($record) === false) {

                                $order_model->rollback();
                                $this->error('写-消费-会员卡记录表-失败！');
                                return;
                            }
                            // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                        }
                        // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

                        if ($flag) {// 会员数据表更新正常
                            
                            // 资金管理是否开启
                            if (self::MONEY_MANAGEMENT_SWITCH) {
                                
                                // 资金流水表capital_flow数据
                                // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                                $flow['shift'] = get_Oper('shift');// 班次标识

                                $o_stime = M('o_record_2_stime')->where("o_id = $o_id")->find();
                                $flow['cTime'] = $o_stime['checkIn'];

                                $flow['in'] = $old_data['price'];// 收入，房费
                                $flow['out'] = 0;// 支出
                                $flow['type'] = 1;// 1房费

                                $capitalAdvModel = D("CapitalAdv");
                                $last_record = $capitalAdvModel->where(array('shift'=>$flow['shift']))->last();

                                // p($last_record);die;

                                $flow['pay_mode'] = $old_data['pay_mode'];// 支付方式
                                if ($flow['pay_mode'] == 0) {

                                    // 只计算现金的资金流
                                    $flow['balance'] = $last_record['balance'] + $flow['in'] - $flow['out'];//余额        
                                }else{

                                    $flow['balance'] = $last_record['balance'];
                                }

                                $flow['info'] = "房间号：".$room_ID;
                                $flow['operator'] = get_Oper("name");// 经办人

                                // p($old_data);
                                // p($flow);die;
                                $capitalModel = M('capital_flow');
                                if ($old_data['status'] == self::STATUS_NEW) {// 预订未支付
                                    
                                    if ($capitalModel->add($flow) === false) {// 房费

                                        $order_model->rollback();
                                        $this->error('写-房费-资金流量表-失败！');
                                        return;
                                    }
                                }elseif($old_data['status'] == self::STATUS_PAY){// 预订已支付
                                    // do nothing
                                }

                                $flow['in'] = $checkIN['deposit'];// 收入，押金
                                $flow['type'] = 3;// 3押金
                                $flow['pay_mode'] = I('post.mode');// 支付方式
                                if ($flow['pay_mode'] == 0) {

                                    // 只计算现金的资金流
                                    $flow['balance'] = $last_record['balance'] + $flow['in'] - $flow['out'];//余额        
                                }else{

                                    $flow['balance'] = $last_record['balance'];
                                }

                                // p($flow);die;
                                if ($capitalModel->add($flow) === false) {// 押金

                                    $order_model->rollback();
                                    $this->error('写-押金-资金流量表-失败！');
                                    return;
                                }
                                // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                            }

                            $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CHECK_IN, 'check_in', array('订单id' => $o_id, '总价' => $old_data['price']));
                            //                     0                 1                2             3                4                            5
                            if (write_log_all_array($log_Arr)) {// 写日志成功，事务提交;失败，事务圆润

                                // $vipModel->commit();// vip表提交事务

                                $this->success('办理入住成功！', U('Home/Order/dealing'));
                                return;
                            }else{
                                
                                // $vipModel->rollback();// vip表回滚事务
                                $this->error('办理入住失败！写日志出错！');
                                return;
                            }
                            
                        }else{

                            echo "会员卡消费，扣费失败！<br/>";
                            // echo $order_model->getError();

                            // $vipModel->rollback();// vip表回滚事务
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

                    $order_model->rollback();
                    $this->error($order_model->getError());
                    return;
                }
            }else{

                echo "create失败<br/>";
                echo $order_model->getError();

                // P($order_model);die;

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

            $temp = I('post.');
            $o_id = $temp['id'];
            $order2_model = D('OrderRecordView');
            $old_data = $order2_model->where("o_record.o_id = $o_id")->find();
            // p($old_data);die;
            
            $leave_time = self::OUT_TIME;// 普通顾客离店时间

            if ($old_data['style'] != self::STYLE_1) {/*非钟点房，去除时间*/
                $temp['aDay'] = substr($old_data['B_date'], 0, 10);
            }else{
                $temp['aDay'] = $old_data['B_date'];
            }
            $today = date("Y-m-d");
            $yesterday = date("Y-m-d",strtotime("$today -1 day"));
            if (strtotime($temp['aDay']) >= strtotime($temp['bDay']) || strtotime($temp['aDay']) < strtotime($yesterday)) {
                // (入住时间 < 离店时间) && (入住时间 >= 昨天)
                $this->error('入住/退房时间错误！');
                return;
            }

            if ($old_data['pay_mode'] == 2) {/*会员卡续住*/
                
                $new_order['pay_mode'] = 2;
                $leave_time = self::OUT_TIME_2;// 会员离店时间
            }else{

                $new_order['pay_mode'] = $temp['mode'];
            }
            // o_record_2_room数据
            // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            $new_order_2_room['room_ID'] = $old_data['room_ID'];
            $new_order_2_room['A_date'] = $temp['aDay'];
            $new_order_2_room['B_date'] = $temp['bDay'];
            // 计算2个日期间隔天数
            $interval = date_diff(date_create($new_order_2_room['A_date']), date_create($new_order_2_room['B_date']));
            $new_order_2_room['nights'] = $interval->format('%a');
            if ($temp['style'] != self::STYLE_1) {/*非钟点房，加上时间*/
                
                $new_order_2_room['A_date'] .= self::IN_TIME;// 入住时间
                $new_order_2_room['B_date'] .= $leave_time;// 离店时间(已判断是否为会员)
            }
            // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

            $new_order['client_ID'] = $old_data['client_ID'];

            $book_info = json_decode($old_data['book_info'], true);
            $book_info['note'] = $temp['note'];
            $new_order['book_info'] = json_encode($book_info, JSON_UNESCAPED_UNICODE);

            $new_order['style'] = $old_data['style'];
            $new_order['type'] = $old_data['type'];
            $new_order['source'] = $old_data['source'];
            $new_order['g_id'] = $old_data['g_id'];
            $new_order['a_id'] = $old_data['a_id'];

            $_price = $old_data['price'] / $old_data['nights'];// 单价
            if ($temp['style'] == self::STYLE_1) {/*钟点房，总价另算*/
                
                $new_order['price'] = $_price * $temp['quantity'];// 总价
            }else {

                $new_order['price'] = $_price * $new_order_2_room['nights'];// 总价
            }

            $new_order['phone'] = $old_data['phone'];
            $new_order['status'] = self::STATUS_NEW;// 统一固定，预订未支付
            $operator['new'] = get_Oper("name");// 经办人
            $new_order['operator'] = json_encode($operator, JSON_UNESCAPED_UNICODE);// unicode格式

            // p($new_order);
            // p($new_order_2_room);
            // die;

            $order_model = D('OrderRecord');
            $order_model->startTrans();// 启动事务

            if ($order_model->create($new_order)) {
                echo "create成功<br/>";
                
                $o_id = $order_model->add();

                // p($order_model);die;

                if ($o_id === false) {
                    $order_model->rollback();
                    $this->error('写入数据库失败！');
                    return;
                }
                
                $new_order_2_room['o_id'] = $o_id;
                if (init_o_room($new_order_2_room) && init_o_sTime($o_id,$new_order['status'])) {
                    // 初始化o_record_2_room表中记录 && 初始化o_record_2_stime表中记录
                    
                    // 订单信息标志
                    $STPSaPR = $new_order['style']."-".$new_order['type'].".".$new_order['price'].".".$new_order['source']."-.".$new_order['a_id'].".-".$new_order['pay_mode']."-".$new_order_2_room['room_ID']."-";
                    if ($new_order['status'] == self::STATUS_PAY) {/*前面有固定status为1，不会进入此条件*/
                        $STPSaPR .= "已支付";

                        // 资金管理是否开启
                        if (self::MONEY_MANAGEMENT_SWITCH) {
                            
                            // 资金流水表capital_flow数据
                            // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                            $flow['shift'] = get_Oper('shift');// 班次标识

                            $o_stime = M('o_record_2_stime')->where("o_id = $o_id")->find();
                            $flow['cTime'] = $o_stime['pay'];// 支付时间

                            $flow['in'] = $new_order['price'];// 收入，房费
                            $flow['out'] = 0;// 支出
                            $flow['type'] = 1;// 1房费

                            $capitalAdvModel = D("CapitalAdv");
                            $last_record = $capitalAdvModel->where(array('shift'=>$flow['shift']))->last();

                            // p($last_record);die;

                            $flow['pay_mode'] = $temp['mode'];// 支付方式
                            if ($flow['pay_mode'] == 0) {

                                // 只计算现金的资金流
                                $flow['balance'] = $last_record['balance'] + $flow['in'] - $flow['out'];//余额        
                            }else{

                                $flow['balance'] = $last_record['balance'];
                            }       

                            $flow['info'] = "房间号：".$new_order_2_room['room_ID'];
                            $flow['operator'] = get_Oper("name");// 经办人

                            // p($flow);die;

                            if (M('capital_flow')->add($flow) === false) {

                                $order_model->rollback();
                                $this->error('写-房费-资金流量表-失败！');
                                return;
                            }
                            // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                        }
                    }else{
                        $STPSaPR .= "未付款";
                    }
                    $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_HELP_SUBMIT_ORDER, 'submit_order', array('订单id' => $o_id, '客户id' => $client_ID, '订单类型' => $STPSaPR));
                    //                     0                 1                2             3                4                            5
                    if (write_log_all_array($log_Arr)){

                        $this->success('[助]提交订单成功！', U('Home/Order/dealing'));
                        return;
                    }else {
                        $this->error('[助]提交订单失败！');
                        return;
                    }
                }

            }else{

                echo "create失败<br/>";

                $order_model->rollback();
                $this->error($order_model->getError());
                return;
            }

        }else {

            if (I('get.id') == '') {

                $this->error('ERROR, id不能为空！');
                return;
            }

            $o_id = I('get.id');
            $o_record_model = D('OrderRecordView');
            $one = $o_record_model->where("o_record.o_id = $o_id")->find();

            // p($one);
            // die;

            // 简单不需处理的数据
            $data['o_id'] = $o_id;
            // $data['client_ID'] = $one['client_ID'];
            $data['ID_card'] = $one['ID_card'];
            $data['pay_mode'] = $one['pay_mode'];
            $data['paid'] = $one['status'] == self::STATUS_NEW ? 0 : 1;
            if ($one['style'] != self::STYLE_1) {/*非钟点房，去除时间*/
                $data['A_date'] = substr($one['A_date'], 0, 10);
                $data['B_date'] = substr($one['B_date'], 0, 10);
            }else{
                $data['A_date'] = $one['A_date'];
                $data['B_date'] = $one['B_date'];

                $data['price'] = $one['price'];// 订单总价
                // 计算2个日期间隔天数
                $interval = date_diff(date_create($data['A_date']), date_create($data['B_date']));
                $data['quantity'] = $interval->format('%h') / 3;// 份数
            }
            $book_info = json_decode($one['book_info'], true);
            $data['book_info'] = $book_info;
            // $data['note'] = $book_info['note'];

            $this->assign("style", $one['style']);

            $typeStr = M('style_type')->where("style = %d", $one['style'])->getField('map_types');// 符合的房型字符串
            $map['type'] = array('in', $typeStr);
            $types = M('type')->where($map)->getField('type,name');// style入住可选的房型
            $data['type'] = $one['type'];// 订单房间类型
            $this->assign('types', $types);
            // echo "types";
            // p($types);

            if ($one['style'] != self::STYLE_2) {/*非团购*/
                
                $sources = M('order_source')->getField('source,name');
                unset($sources[count($sources) - 1]);// 去除 最后1个"团购"
                $data['source'] = $one['source'];// 订单来源
            }else {

                $sources = M('groupon')->getField('g_id,name');// 团购来源
                $data['source'] = $one['g_id'];// 订单来源
            }
            $this->assign('sources', $sources);

            if ($one['style'] == self::STYLE_0 || $one['style'] == self::STYLE_3) {
                // 普通 和 (节假日)普通，才有代理人
                $agents = M("agent")->field('a_id, name, phone')->select();// 代理人
                if ($one['a_id'] != '') {
                    $data['agent'] = $one['a_id'];// 订单代理人
                }
                $this->assign('agents', $agents);
            }

            $prices = M($one['style'].'_price')->find($one['type']);// style入住，type价钱
            // 找到订单所使用的价格
            if ($data['pay_mode'] == 2) {/*会员价*/
                if ($vipInfo = is_IDcard_Vip($data['ID_card'])){
                    $vipInfo_str = ' 会员卡号：<span style="color:#5bc0de">'. $vipInfo['card_ID'] .'</span>';

                    if ($vipInfo['balance'] < $one['price']) {
                        $vipInfo_str .=' 余额：<span style="color:#d9534f">'. $vipInfo['balance'] .'</span>';
                    }else{
                        $vipInfo_str .=' 余额：<span style="color:#5cb85c">'. $vipInfo['balance'] .'</span>';
                    }

                    $this->assign('vipInfo_str', $vipInfo_str);
                    $data['price_type'] = 'vip';
                }else{
                    $this->error("订单数据非法！<br/>非会员用户使用了会员卡支付！");
                    return;
                }
            }elseif($data['agent'] != ''){/*代理价*/
                $data['price_type'] = 'agent';
            }elseif($one['g_id'] != ''){/*团购价*/
                $data['price_type'] = 'groupon';
            }else{

                $one_night_price = $one['price'] / $one['nights'];// 单价
                $price_type = "spec_price";// 特殊价格
                foreach ($prices as $key => $value) {
                    if ($value == $one_night_price) {// 确定订单享用的价格
                        // 记录下该key。break;
                        $price_type = $key;
                        break;
                    }
                }

                // 确定优惠类型
                switch ($price_type) {
                    case 'bid_price':/*标价*/
                        $data['price_type'] = 'bid';
                        break;
                    case 'stu_price':/*学生价*/
                        $data['price_type'] = 'stu';
                        break;
                /*
                    case 'agent_price':
                        $discount_type = "代理";
                        break;
                    case 'vip_price':
                        $discount_type = "会员";
                        break;
                    case 'groupon_price':
                        $discount_type = "团购";
                        break;
                */
                    default:
                        $data['spec_price'] = $one_night_price;// 特殊价，单价
                        $data['price_type'] = 'spec';
                        break;
                }
            }
            $this->assign('prices', $prices);
            // echo "prices";
            // echo $data['price_type'];
            // p($prices);

            // 过滤得到空闲的房间
            $rooms = get_available_rooms($one, $one['room_ID']);
            $data['room_ID'] = $one['room_ID'];// 订单房间号
            $this->assign('rooms', $rooms);

            // p($data);
            // die;
            
            $this->assign('data', $data);
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

            $order_model = D('OrderRecord');
            $old_data = $order_model->where("o_id = $o_id")->find();
            echo "old_status = ".$old_data['status'];
            if ($old_data['status'] == self::STATUS_CHECKOUT) {// 状态未改变

                echo "状态未改变，不需要更新<br/>";
                return false;
            }

            // 办理退房，需要更新的信息
            // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            $checkOut['status'] = self::STATUS_CHECKOUT;
            
            $operator = json_decode($old_data['operator'],true);
            $operator['checkOut'] = get_Oper("name");// 经办人，增加"办理入住"
            $checkOut['operator'] = json_encode($operator, JSON_UNESCAPED_UNICODE);// unicode格式
            // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

            $order_model->startTrans();// 启动事务

            if ($order_model->where("o_id = $o_id")->create($checkOut ,2)) {
                echo "create成功<br/>";

                echo " *** ". $result = $order_model->scope('allowUpdateField, checkIn')->save();

                // p($order_model);die;

                if ($result) {
                    echo "办理退房成功！<br/>";

                    // 资金管理开启
                    if (self::MONEY_MANAGEMENT_SWITCH) {
                        $return_deposit = I('post.deposit');
                        // TODO
                        
                    }

                    // 需要更新d_record_2_stime表中记录
                    if (update_o_sTime($o_id, $checkOut['status'])) {

                        // 资金管理是否开启
                        if (self::MONEY_MANAGEMENT_SWITCH) {
                            
                            // 资金流水表capital_flow数据
                            // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                            $flow['shift'] = get_Oper('shift');// 班次标识

                            $o_stime = M('o_record_2_stime')->where("o_id = $o_id")->find();
                            $flow['cTime'] = $o_stime['checkOut'];

                            $flow['in'] = 0;// 收入
                            $flow['out'] = I('post.deposit');// 支出，退押
                            $flow['type'] = 4;// 4退押

                            $capitalAdvModel = D("CapitalAdv");
                            $last_record = $capitalAdvModel->where(array('shift'=>$flow['shift']))->last();

                            // p($last_record);die;

                            $flow['pay_mode'] = 0;// 支付方式，固定为现金退还押金
                            if ($flow['pay_mode'] == 0) {

                                // 只计算现金的资金流
                                $flow['balance'] = $last_record['balance'] + $flow['in'] - $flow['out'];//余额        
                            }else{

                                $flow['balance'] = $last_record['balance'];
                            }

                            $room_info = M('o_record_2_room')->where("o_id = $o_id")->find();
                            $flow['info'] = "房间号：".$room_info['room_ID'];
                            
                            $flow['operator'] = get_Oper("name");// 经办人

                            // p($flow);die;
                            $capitalModel = M('capital_flow');
                            if ($capitalModel->add($flow) === false) {// 房费

                                $order_model->rollback();
                                $this->error('写-房费-资金流量表-失败！');
                                return;
                            }
                            // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                        }

                        $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CHECK_OUT, 'check_out', array('订单id' => $o_id));
                        //                     0                 1                2             3                4                            5
                        // write_log_all($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CHECK_OUT, 'check_out', array('房间id' => $o_id));
                        if (write_log_all_array($log_Arr)){
                            
                            $this->success('办理换退房成功！', U('Home/Order/dealing'));
                            return;
                        }else{
                            $this->error('办理换退房失败！');
                            return;
                        }
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

    /**
     * 空净房间
     */
    public function cleared(){
        
        if (!I('get.id')) {
            $this->error('ERROR, id不能为空！');
            return;
        }

        $o_id = I('get.id');

        $order_model = D('OrderRecord');
        $old_data = $order_model->where("o_id = $o_id")->find();
        echo "old_status = ".$old_data['status'];
        if ($old_data['status'] == self::STATUS_CLEARED) {// 状态未改变

            echo "状态未改变，不需要更新<br/>";
            return false;
        }

        // 空净，需要更新的信息
        // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        $cleared['status'] = self::STATUS_CLEARED;
        // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        
        if ($order_model->where("o_id = $o_id")->create($cleared ,2)) {
            echo "create成功<br/>";

            echo " *** ". $result = $order_model->scope('allowUpdateField, checkOut')->save();

            if ($result) {
                echo "空净成功！<br/>";

                // 需要更新d_record_2_stime表中记录
                if (update_o_sTime($o_id, $cleared['status'])) {
                    $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CLEARED, 'cleared', array('订单id' => $o_id));
                    //                     0                 1                2             3                4                            5
                    // write_log_all($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_CLEARED, 'cleared', array('房间id' => $o_id));
                    if (write_log_all_array($log_Arr)){
                        
                        $this->success('空净成功！', U('Home/Index/index'));
                        return;
                    }else{
                        $this->error('空净失败！');
                        return;
                    }
                }
            }else{

                echo "空净失败！<br/>";
                // echo $order_model->getError();

                $this->error($order_model->getError());
                return;
            }
        }
    }

    /**
     * 获取验证码
     */
    public function verify(){

        // 调用function
        // verify();

        $config =    array(
            'fontSize'    =>    15,    // 验证码字体大小
            'useNoise'    =>    false, // 关闭验证码杂点
            'imageW'      =>    0,     // 验证码宽度
            'imageH'      =>    0,     // 验证码高度
            'codeSet'     =>    '123456789',//验证码字符
            'length'      =>    3,     // 验证码位数
        );
        $Verify =     new \Think\Verify($config);
        $Verify->entry();
    }
}