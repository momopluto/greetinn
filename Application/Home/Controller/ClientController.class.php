<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 客户管理控制器
 */
class ClientController extends HomeController {
	
	/**
	 * 查询该用户是否注册
	 */
    public function check(){
        
        $this->display();
    }

    /**
	 * [助]客户注册
	 */
    public function reg(){

        if (IS_POST) {

            if (!check_verify(I('post.verify'))) {
                
                $this->error('验证码不正确！');
                return;
            }

            // 表单名称name,ID,sex,phone
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

                $this->success('注册成功！', U('Home/Client/order'));
                return;
            }else{
                echo "测试，自动验证&完成，失败！";
                // echo $Client->getError();

                $this->error($Client->getError());
                return;
            }
        }else{
            
            $this->display();
        }
    }

    /**
     * [助]提交订单
     */
    public function order(){

        // 存在问题：
        // 钟点房 入住日期-退房日期，改成 入住时间，退房时间，且下单时可选择购买几份钟点房，金额累加

        if (IS_POST) {
            // p(I('post.'));die;

            if (!check_verify(I('post.verify'))) {
                
                $this->error('验证码不正确！');
                return;
            }
            
            // ID身份证,aDay,bDay,type对应价钱,note,info,phone

            $client = M('client')->where(array('ID_card'=>I('post.ID')))->find();

            if (!$client) {
                $this->error('该身份证未注册！', U('Home/Client/reg'));
                return;
            }
            // echo strtotime(I('post.aDay'))."***".NOW_TIME;die;
            if (strtotime(I('post.aDay')) > strtotime(I('post.bDay')) || strtotime(I('post.aDay')) < strtotime(date('Y-m-d',time()))) {
                $this->error('入住/退房时间错误！');
                return;
            }
            
            // p($client);die;
            $client_ID = $client['client_ID'];
            $info = I('post.info');
            // p($info);
            if ($info[1]['name'] == '' || $info[1]['ID'] == '') {
                // 去除入住人(二)信息
                unset($info[1]);
            }
            $people_info = $info;
            // p($info);die;

            $book_info['start_date'] = I('post.aDay');
            $book_info['leave_date'] = I('post.bDay');

            // $people_info = array(// 入住人身份信息
            //     array('name'=>'王一', 'ID_card'=>'520222196306159670'),
            //     array('name'=>'李二', 'ID_card'=>'230230197409256612')
            // );


            // $book_info['start_date'] = '2015-02-02';
            // $book_info['leave_date'] = '2015-02-03';

            // 计算2个日期间隔天数
            $interval = date_diff(date_create($book_info['start_date']), date_create($book_info['leave_date']));
            $book_info['nights'] = $interval->format('%a');
            
            $book_info['number'] = count($people_info);// 入住人数
            $book_info['people_info'] = $people_info;
            $book_info['note'] = I('post.note');

            $new_order['client_ID'] = $client_ID;

            // o_record_2_room数据
            $new_order_2_room['nights'] = $book_info['nights'];
            $new_order_2_room['A_date'] = $book_info['start_date'];
            $new_order_2_room['B_date'] = $book_info['leave_date'];
            unset($book_info['nights']);
            unset($book_info['start_date']);
            unset($book_info['leave_date']);
            $new_order['book_info'] = json_encode($book_info, JSON_UNESCAPED_UNICODE);// unicode格式

            $new_order['style'] = I('post.style');// 订单类型
            $new_order['type'] = I('post.type');// 房型
            if (!is_null(I('post.groupon'))) {
                $new_order['g_id'] = I('post.groupon');// 团购平台
            }
            $new_order['source'] = I('post.source');// 来源
            $new_order['pay_mode'] = I('post.mode');// 支付方式

            // $type_price = M('type_price')->find(I('post.type'));
            // $new_order['price'] = $type_price['price'] * $new_order_2_room['nights'];
            $new_order['price'] = I('post.price');
            $new_order['phone'] = I('post.phone');

            // p($new_order);die;

            $order_model = D('OrderRecord');

            $order_model->startTrans();// 启动事务

            if ($order_model->create($new_order)) {
                echo "create成功<br/>";
                
                $o_id = $order_model->add();

                if ($o_id === false) {
                    $this->error('写入数据库失败！');
                    return;
                }

                $new_order_2_room['o_id'] = $o_id;

                // init_o_room($new_order_2_room);// 初始化o_record_2_room表中记录
                // init_o_sTime($o_id);// 初始化o_record_2_stime表中记录
                if (init_o_room($new_order_2_room) && init_o_sTime($o_id)) {

                    $STgSPP = $new_order['style']."-".$new_order['type']."-".$new_order['g_id']."-".$new_order['source']."-".$new_order['pay_mode']." | ".$new_order['price'];
                    $log_Arr = array($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_HELP_SUBMIT_ORDER, 'submit_order', array('订单id' => $o_id, '客户id' => $client_ID, '订单类型' => $STgSPP));
                    //                     0                 1                2             3                4                            5
                    write_log_all_array($log_Arr);
                    // write_log_all($this->log_model, $this->log_data, $order_model, self::RECEPTIONIST_HELP_SUBMIT_ORDER, 'submit_order', array('房间id' => $o_id, '客户id' => $client_ID, '订单类型' => $STgSP));

                    $this->success('[助]提交订单成功！', U('Home/Order/dealing'));
                    return;
                }

            }else{

                echo "create失败<br/>";
                echo $order_model->getError();

                // $this->error($order_model->getError());
                return;
            }   
        }else{

            // 1.选择style
            // 2.根据style_type，AJAX加载相应的type，IN type，记录下style的编号
            // 3.根据style的编号和type，AJAX加载相应的价格(在0_price/1_price/2_price中得到)
            //      钟点房特殊，多出选择“份数”
            //      团购特殊，多出选择团购平台，AJAX加载团购平台信息

            // 增加“来源”   --done
            // 增加“网上支付/现金支付”    --done


            // $types = M('type_price')->getField('type,name,price');
            // $this->assign('types', $types);

            $styles = M('style')->getField('style,name');// 普通入住
            $this->assign('styles', $styles);
            $types = M('type')->getField('type,name');// 普通入住可选的房型
            $this->assign('types', $types);
            $prices = M('0_price')->find(0);// 普通入住，标单价钱
            $this->assign('prices', $prices);
            // p($prices);die;

            $sources = M('order_source')->getField('source,name');
            $this->assign('sources', $sources);
            $this->display();
        }
    }

    /**
     * [AJAX]根据style获取type
     */
    public function getType_PriceByStyle(){

        // 1.从AJAX得到style
        // 2.通过style在style_type表中找到对应的type字符串，在type表中 IN，得到结果types
        // 3.通过style和结果types[0]的type，在0_price/1_price/2_price表中，得到结果prices
        // 4.$data[] = $types;$data[] = $prices;

        $style = I('post.style');
        // $style = 2;

        $typeStr = M('style_type')->find($style);
        $map['type'] = array('in', $typeStr['map_types']);
        $types = M('type')->where($map)->getField('type,name');//结果types
        
        $type = key($types);// types数组第1个键

        $prices = M($style."_price")->find($type);// 结果types中第1个房型的价格

        $data['types'] = $types;
        $data['prices'] = $prices;

        if ($style == 2) {// 团购
            $data['groupons'] = M('groupon')->getField('g_id,name');
        }
        
        // p($data);die;
        $this->ajaxReturn($data, 'json');
    }

    /**
     * [AJAX]根据style和type获取price
     */
    public function getPriceByStyle_Type(){

        // $post = I('post.data');
        
        $style = I('post.style');
        $type = I('post.type');

        // $style = 0;
        // $type = 4;

        $data['prices'] = M($style."_price")->find($type);// 对应style类型和type房型的价格

        // p($data);die;
        $this->ajaxReturn($data, 'json');
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