<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 会员控制器
 */
class VipController extends HomeController{


    /**
     * [隐藏的]vip表增加first_free_checkIn字段
     * 将已往免费首住的时间o_record_2_stime.checkIn写入first_free_checkIn字段
     */
    public function update_first_free_checkIn(){

        /*
        # sql语句
        # 未有first_free_checkIn字段，从o_record_2_stime表里面取

SELECT
    vip.client_ID,vip.card_ID,vip.birthday,vip.balance,vip.first_free,
    o_record_2_stime.checkIn AS first_free_checkIn,
    vip.cTime,
    client.`name`,client.ID_card,client.phone
FROM
    (client JOIN vip ON client.client_ID=vip.client_ID)
    LEFT JOIN
    (o_record JOIN o_record_2_stime ON o_record.o_id=o_record_2_stime.o_id AND o_record.price=0)
    ON vip.client_ID=o_record.client_ID
        */

        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $queryStr = "SELECT"
            ." vip.client_ID,vip.card_ID,vip.birthday,vip.balance,vip.first_free,"
            ." o_record_2_stime.checkIn AS first_free_checkIn,"
            ." vip.cTime,"
            ." client.`name`,client.ID_card,client.phone"
        ." FROM"
            ." (client JOIN vip ON client.client_ID=vip.client_ID)"
            ." /*LEFT*/ JOIN"
            ."  (o_record JOIN o_record_2_stime ON o_record.o_id=o_record_2_stime.o_id AND o_record.price=0)"
            ." ON vip.client_ID=o_record.client_ID";

        $data = $Model->query($queryStr);

        foreach ($data as $key => $value) {

            if (!$value["first_free_checkIn"]) {

                $vip[$value['client_ID']] = NULL;

                // 如果已使用免费首住，但是没有入住时间(原因：前台未处理系统内该顾客的首住订单)
                // $vip[$value['client_ID']] = $value["cTime"];
                
                continue;
            }

            $vip[$value['client_ID']] = $value["first_free_checkIn"];
        }

        // p($Model);
        // p($data);
        // p($vip);die;

        $sql = "UPDATE vip SET first_free_checkIn =  CASE client_ID ";
        $client_IDs = implode(',', array_keys($vip));
        foreach ($vip as $key => $value) {
            
            $sql .= sprintf("WHEN %d THEN '%s' ", $key, $value);
        }
        $sql .= "END WHERE client_ID IN ($client_IDs)";
        echo "<pre>";
        echo $sql;
        echo "<pre>";

        die;

        $Model->query($sql);

        /*注：
        
        如果vip.first_free为0，表明已经使用首住免费
        但是上面又没找到会员的免费首住订单记录，
        可能的原因：该会员属于早期一批，当时前台未使用系统录入会员信息。
        解决办法：
            通过以前的excel表格找到日期，大致给出时间
         */
    }

    /**
     * [隐藏的]会员卡办理登记表-核对
     */
    public function checkLists(){

        $vipModel = D("VipView");
        $data = $vipModel->order("card_ID")->select();

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 会员列表
     */
    public function lists(){


        $vipModel = D("VipView");
        $data = $vipModel->select();
        // p($data);die;

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 开通
     */
    public function reg(){

        if (IS_POST) {

            // p(I('post.'));die;

            if (!check_verify(I('post.verify'))) {
                
                $this->error('验证码不正确！');
                return;
            }

            // 身份证（已注册）ID
            // 会员卡号

            // ID, card

            // $ID_card = '441423199305165018';
            $ID_card = I('post.ID');

            $row = is_IDCard_exists($ID_card, 'client');// 取得客户信息

            if (!$row) {
                $this->error($ID_card.'，此身份证不存在或未注册！');
                return;
            }

            $card_ID = I('post.card');

            if (strlen($card_ID) != 4) {

                $this->error('会员卡号应由4位数字组成！请确认！');
                return;
            }

            $data['client_ID'] = $row['client_ID'];
            $data['card_ID'] = $card_ID;
            // $data['card_ID'] = '0090';

            $data['birthday'] = $row['birthday'];

            // $operator['reg'] = get_OperName();// 开通，经办人
            // $data['operator'] = json_encode($operator, JSON_UNESCAPED_UNICODE);
            $data['operator'] = get_OperName();// 开通，经办人

            // card_ID要唯一，自动验证
            $vipModel = D("Vip");
            // nominal_fee插入时为200，自动完成
            // balance插入时为0，自动完成
            // first_free插入时为1，自动完成
            // cTime，自动完成

            $vipModel->startTrans();// 启动事务

            // p($data);die;
            // echo self::RECEPTIONIST_OPEN_VIP;die;


            if ($vipModel->create($data, 1)) {// 已知主键，须强制过程为insert

                echo "开通会员成功！";

                $res = $vipModel->add();
                if ($res === false) {

                    $this->error('写入数据库失败！');
                    return;
                }

                $log_Arr = array($this->log_model, $this->log_data, $vipModel, self::RECEPTIONIST_OPEN_VIP, 'open_vip', array('客户id' => $data['client_ID'], '会员卡id' => $data['card_ID']));
                //                     0                 1                2             3                4                            5
                if (write_log_all_array($log_Arr)){

                    // 会员卡记录表vip_record数据
                    // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                    $record['client_ID'] = $data['client_ID'];
                    $record['card_ID'] = $data['card_ID'];

                    $vip_data = M('vip')->where($record)->find();
                    $record['cTime'] = $vip_data['cTime'];

                    // p($vip_data);die;

                    $record['style'] = self::VIP_STYLE_1;// 开通
                    $record['amount'] = $vip_data['nominal_fee'];// 金额=工本费
                    $record['balance'] = $vip_data['balance'];//余额
                    $record['operator'] = get_OperName();// 经办人

                    while (!M('vip_record')->add($record)) {
                        echo "写-开通-会员卡记录表-失败！";
                    }
                    // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                    
                    $this->success('开通会员成功！', U('Home/Vip/lists'));
                    return;
                }else {

                    $this->error('开通会员失败！');
                    return;
                }

            }else{

                echo "开通会员失败！";
                // echo $vipModel->getError();

                $this->error($vipModel->getError());
                return;
            }

        }else{


            $this->display();
        }
    }

    /**
     * 充值
     */
    public function recharge(){

        if (IS_POST) {

            if (!check_verify(I('post.verify'))) {
                
                $this->error('验证码不正确！');
                return;
            }

            // 身份证+会员卡号（检测该身份证是否是会员）
            // 充值金额
            // 一次性充值200以上500以下，赠送7%，500以上赠送10%

            // ID, card, money
            // $data['card_ID'] = '1220';
            $data['card_ID'] = I('post.card');

            $row = D('VipView')->where($data)->find();
            // p($row);die;
            if (!$row) {

                $this->error('身份证或会员卡不正确！请重新确认！');
                return;
            }

            // $amount = 300;// 充值金额
            $amount = I('post.money');// 充值金额
            if ($amount % 100 != 0) {
                // 充值金额不是整百
                $this->error("充值金额须是100的整数倍！");
                return;
            }

            $gift = 0;
            if ($amount >= 200 && $amount < 500) {
                $gift = 0.07 * $amount;
            }elseif ($amount >= 500) {
                $gift = 0.1 * $amount;
            }

            $new_balance = $amount + $gift + $row['balance'];

            $vipData['client_ID'] = $row['client_ID'];
            $vipData['balance'] = $new_balance;

            $vipModel = M('vip');

            $vipModel->startTrans();// 启动事务

            if ($vipModel->save($vipData)) {
                
                echo "vip更新鸟～<br/>";

                $vipData['amount'] = $amount;
                $vipData['gift'] = $gift;
                $vipData['cTime'] = getDatetime();
                if (M('recharge_record')->add($vipData)) {
                    echo "recharge_record也更新鸟～";

                    $log_Arr = array($this->log_model, $this->log_data, $vipModel, self::RECEPTIONIST_RECHARGE_VIP, 'recharge_vip', array('客户id' => $row['client_ID'], '会员卡id' => $row['card_ID'], '充值金额' => $vipData['amount'], '赠送' => $gift));
                    //                     0                 1                2             3                4                            5
                    if (write_log_all_array($log_Arr)){

                        // 会员卡记录表vip_record数据
                        // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                        $record['client_ID'] = $vipData['client_ID'];
                        $record['card_ID'] = $data['card_ID'];

                        $record['cTime'] = $vipData['cTime'];

                        $record['style'] = self::VIP_STYLE_2;// 充值
                        $record['amount'] = $vipData['amount'];// 金额=充值金额
                        $record['balance'] = $vipData['balance'];//余额
                        $record['operator'] = get_OperName();// 经办人

                        while (!M('vip_record')->add($record)) {
                            echo "写-充值-会员卡记录表-失败！";
                        }

                        $record['style'] = self::VIP_STYLE_3;// 赠送
                        $record['amount'] = $vipData['gift'];// 金额=赠送金额
                        while (!M('vip_record')->add($record)) {
                            echo "写-赠送-会员卡记录表-失败！";
                        }
                        // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

                        $this->success('会员充值成功！', U('Home/Vip/lists'), 3);
                        return;
                    }else {

                        $this->error('会员充值失败！');
                        return;
                    }

                }else{

                    echo "recharge_record更新失败鸟～<br/>";

                    $this->error('echarge_record更新失败！');
                    return;
                }
            }else{

                echo "vip更新失败鸟～<br/>";
                // echo $vipModel->getError();

                $this->error($vipModel->getError());
                return;
            }

        }else{

            $this->display();
        }
    }

    /**
     * 首住免费
     */
    public function first_free(){

        
        // 0元订单信息
        // 入住人信息，默认为会员，从client表中获得
        // style = 0, type = 0, source = 0, pay_mode = 2, price = 0, phone, status = 2
        // 预分配房间

        // deposit和room_ID在办理入住时输入
        // first_free标志

        if (IS_POST) {

            if ($v_data = D('VipView')->where(array('ID_card'=>I('post.ID')))->find()) {
                
                if ($v_data['first_free'] == 0) {

                    $this->error('此会员已享用过首住免费！');
                    return;
                }

                // p($v_data);die;

                // $operator = json_decode($v_data['operator'],true);
                // $operator['new'] = get_OperName();// 首住免费下单，经办人
                // $update_date['operator'] = json_encode($operator, JSON_UNESCAPED_UNICODE);

                // if (M('vip')->where(array('card_ID'=>$v_data['card_ID']))->save($update_date)) {
                    
                $Client = A('Client');
                $Client->order();// 跨控制器调用
                // }else {

                //     $this->error('经办人记录失败！');
                //     return;
                // }


            }else{

                $this->error('该身份证未开通会员！');
                return;
            }
            
        }else{

            $this->display();
        }
    }












    /**
     * [AJAX]获取会员卡的消费记录
     */
    public function getVipRecord(){

        if (IS_AJAX) {
            
            // $this->ajaxReturn(I('post.'), 'json');
            // return;

            $map['card_ID'] = I('post.card');
            $data = D('VipRecordView')->where($map)->order('cTime')->select();
            
            $this->ajaxReturn($data, 'json');
        }
    }


    /**
     * [AJAX]获取会员信息info
     */
    public function getVipInfo(){

        $card = I('post.card');

        $data['info'] = is_VipCard_exists($card);

        $this->ajaxReturn($data, 'json');
    }

    /**
     * [AJAX]通过身份证关联，获取会员卡信息
     */
    public function getVipInfoBy_IDCard(){
        $ID_card = I('post.id');

        $data['info'] = is_IDcard_Vip($ID_card);

        $this->ajaxReturn($data, 'json');
    }

    /**
     * 获取验证码
     */
    public function verify(){

    	// 调用function
    	verify();
    }
}
