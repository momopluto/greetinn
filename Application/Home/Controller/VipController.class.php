<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 会员控制器
 */
class VipController extends HomeController{

    /**
     * 会员列表
     */
    public function lists(){

        $data = M('vip')->select();
        p($data);die;

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 开通
     */
    public function reg(){

        if (IS_POST) {

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

            $data['client_ID'] = $row['client_ID'];
            $data['card_ID'] = I('post.card');
            // $data['card_ID'] = '0090';

            $data['birthday'] = $row['birthday'];

            // card_ID要唯一，自动验证
            $vipModel = D("Vip");
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
                write_log_all_array($log_Arr);

                $this->success('开通会员成功！', U('Home/Vip/lists'));
                return;
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

                $this->error('身份证与会员卡不正确！请重新确认！');
                return;
            }

            // $amount = 300;// 充值金额
            $amount = I('post.money');// 充值金额

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
                $vipData['cTime'] = getDatetime();
                if (M('recharge_record')->add($vipData)) {
                    echo "recharge_record也更新鸟～";

                    $log_Arr = array($this->log_model, $this->log_data, $vipModel, self::RECEPTIONIST_RECHARGE_VIP, 'recharge_vip', array('客户id' => $row['client_ID'], '会员卡id' => $row['card_ID'], '充值金额' => $vipData['amount'], '赠送' => $gift));
                    //                     0                 1                2             3                4                            5
                    write_log_all_array($log_Arr);

                    $this->success('会员充值成功！', U('Home/Vip/lists'), 3);
                    return;
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
     * 明细查询
     */
    public function detail(){

        // 余额
        // 入住房型统计，赠送优惠
        // 消费记录

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

                $Client = A('Client');
                $Client->order_0();// 跨控制器调用

            }else{

                $this->error('该身份证未开通会员！');
                return;
            }
            
        }else{

            $this->display();
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
