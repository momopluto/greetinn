<?php
namespace Client\Controller;
use Think\Controller;

/**
 * 个人中心控制器
 * 
 */
class UserController extends ClientController {

	/**
	 * 个人中心
     * 若未注册，则显示为注册页面
	 */
    public function index(){

        echo "hello!";
        

        


        echo "byebye!";
        die;
        
        $this->display();
    }


    /**
     * 个人资料
     */
    public function info(){

    	$this->display();
    }


    
    /**
     * 注册
     */
    public function reg(){

        if (IS_POST) {
            echo "hello!";

            // 表单名称name,ID,sex,phone
            $reg_data['name'] = I('post.name');
            $reg_data['ID_card'] = I('post.ID');
            $reg_data['gender'] = I('post.sex');
            $reg_data['birthday'] = $reg_data['ID_card'];// 先将ID_card赋值给birthday，用于自动完成时的输入数据
            $reg_data['phone'] = I('post.phone');

            $test_model = D('Client');

            if ($test_model->create($reg_data)){
                echo "测试，自动验证&完成，成功！";
                // echo $test_model->birthday."<br/>";
                // echo $test_model->cTime."<br/>";

                // p($test_model);

                $test_model->add();
            }else{
                echo "测试，自动验证&完成，失败！";
                echo $test_model->getError();
            }
            
            echo "byebye!";
            die;
        }
        
        $this->display();
    }

    /**
     * 编辑
     */
    public function edit(){

        // $updata['ID_card'] = '441423199305165018';
        // $updata['name'] = '刘恩坚123';

        // $updata['client_ID'] = '1';// 主键须存在，用于create标志此操作为更新，【不需要】
        $updata['openid'] = 'testesttsteteste';
        $updata['phone'] = '18826481053';

        $test_model = D('Client');

        if ($test_model->where('client_ID = 1')->create($updata,2)) {// 2标志更新，不可缺
            echo "create成功！";

            echo "***".$result = $test_model->scope('allowUpdateField')->where('client_ID = 1')->save();
            if ($result) {
                
                echo "更新成功！";
            }else{

                echo "更新失败！";
                // p($test_model);
                echo $test_model->getError();
            }
        }else{
            echo "create失败！";
            echo $test_model->getError();
        }
    }
}