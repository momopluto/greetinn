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

            if ($Client->create($reg_data)){
                echo "测试，自动验证&完成，成功！";

                $Client->add();

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

        if (IS_POST) {
            // p(I('post.'));die;
            if (!check_verify(I('post.verify'))) {
                
                $this->error('验证码不正确！');
                return;
            }
            
            // ID身份证,aDay,bDay,type对应价钱,note,info,phone

            $client = M('client')->where(array('ID_card'=>I('post.ID')))->find();
            // p($client);die;
            $client_ID = $client['client_ID'];
            $info = I('post.info');
            // p($info);
            if ($info[2]['name'] == '' || $info[2]['ID'] == '') {
                // 去除入住人(二)信息
                unset($info[2]);
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

            $type_price = M('type_price')->find(I('post.type'));
            // p($type_price);die;
            $new_order['price'] = $type_price['price'];
            $new_order['phone'] = I('post.phone');

            p($new_order);die;

            $order_model = D('OrderRecord');

            if ($order_model->create($new_order)) {
                echo "create成功<br/>";
                

                $o_id = $order_model->add();
                // dump($o_id);

                $new_order_2_room['o_id'] = $o_id;
                init_o_room($new_order_2_room);// 初始化o_record_2_room表中记录
                init_o_sTime($o_id);// 初始化o_record_2_stime表中记录

            }else{

                echo "create失败<br/>";
                echo $order_model->getError();
            }   
        }else{

            $price = M('type_price')->getField('type,price');

            $this->assign('price', $price);
            $this->display();
        }
    }

    /**
     * 获取验证码
     */
    public function verify(){

        // 调用function
        verify();
    }
}