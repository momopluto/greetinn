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

        echo "hello!";
        
        // 表单名称name,ID,sex,phone
        $reg_data['name'] = I('post.name');// '刘momo';//
        $reg_data['ID_card'] = I('post.ID');// '441423199305165018';//
        $reg_data['gender'] = I('post.sex');
        $reg_data['birthday'] = $reg_data['ID_card'];// 先将ID_card赋值给birthday，用于自动完成时的输入数据
        $reg_data['phone'] = I('post.phone');

        $test_model = D('Client');

        if ($test_model->create($reg_data)){
            echo "测试，自动验证&完成，成功！";
            // echo $test_model->birthday."<br/>";
            // echo $test_model->cTime."<br/>";

            // p($test_model);

            // $test_model->add();
        }else{
            echo "测试，自动验证&完成，失败！";
            echo $test_model->getError();
        }

        // if ($row = is_IDcard_exists('441423199305165018')) {
        //     p($row);
        // }else{
        //     echo "此身份证未注册！";
        // }

        echo "byebye!";
        die;
        
        $this->display();
    }
}