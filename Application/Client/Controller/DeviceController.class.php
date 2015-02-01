<?php
namespace Client\Controller;
use Think\Controller;

/**
 * 设备管理控制器
 * 
 */
class DeviceController extends ClientController {

    // 操作状态
    const STATUS_CANCEL          =   0;      //  状态值，取消
    const STATUS_NEW             =   1;      //  状态值，新建

	/**
	 * 设备清单
	 */
    public function lists(){

        echo "设备清单，begin<br/>";

        // $brw_data['o_id'] = '1';// 外键约束
        // $brw_data['device_IDs'] = '12,13,17';
        // $brw_data['price'] = '0';
        // $brw_data['openid'] = 'jhskwqujsdlkwql';

        // $test_model = D('DeviceRecord');

        // if ($test_model->create($brw_data)) {
        //     echo "create成功<br/>";
        //     echo $test_model->status."<br/>";
        //     echo $test_model->cTime."<br/>";

        //     $d_id = $test_model->add();
        //     dump($d_id);

        //     init_d_sTime($d_id);// 初始化d_record_2_stime表中记录
            
        // }else{

        //     echo "create失败<br/>";
        //     echo $test_model->getError();
        // }
// ===========上提交，下更新
        // $d_id = 1;// 模拟操作的记录id

        // // $brw_data['d_id'] = '1';// 主键须存在，用于create标志此操作为更新，【不需要】

        // $brw_data['device_IDs'] = '12,13,14';
        // $brw_data['price'] = '4';
        // // $brw_data['openid'] = 'jhskwqujsdlkwql';
        // $brw_data['status'] = '0';

        // $test_model = D('DeviceRecord');
        // $old_status = $test_model->where("d_id = $d_id")->getField('status');
        // echo "old_status = $old_status";

        // if ($test_model->where("d_id = $d_id")->create($brw_data, 2)) {// 2标志更新，不可缺
        //     echo "create成功<br/>";
        //     // echo $test_model->status."<br/>";
        //     // echo $test_model->cTime."<br/>";

        //     echo "***".$result = $test_model->scope('allowUpdateField,new')->where("d_id = $d_id")->save();

        //     // p($test_model);

        //     if ($result) {
                
        //         echo "更新成功<br/>";
        //         if ($old_status != $brw_data['status']) {// 状态有改变
        //             // 需要更新d_record_2_stime表中记录
        //             dump(update_d_sTime($d_id, $brw_data['status']));
        //         }
        //     }else{

        //         echo "更新失败<br/>";
        //         echo $test_model->getError();
        //     }

        // }else{

        //     echo "create失败<br/>";
        //     echo $test_model->getError();
        // }

        echo "设备清单，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 历史记录
     */
    public function history(){
    	
    	$this->display();
    }

    /**
     * 提交申请
     */
    public function submit(){
        
        echo "提交申请，begin<br/>";

        $brw_data['o_id'] = '1';// 外键约束
        $brw_data['device_IDs'] = '12,13,16';
        $brw_data['price'] = '0';
        $brw_data['openid'] = 'jhskwqujsdlkwql';

        $test_model = D('DeviceRecord');

        if ($test_model->create($brw_data)) {
            echo "create成功<br/>";
            echo $test_model->status."<br/>";
            echo $test_model->cTime."<br/>";

            $d_id = $test_model->add();
            dump($d_id);

            init_d_sTime($d_id);// 初始化d_record_2_stime表中记录
            
        }else{

            echo "create失败<br/>";
            echo $test_model->getError();
        }

        echo "提交申请，end<br/>";
        die;

        $this->display();
    }

    /**
     * 编辑申请
     */
    public function edit(){

        echo "编辑申请，begin<br/>";

        $d_id = 1;// 模拟操作的记录id

        // $brw_data['d_id'] = '1';// 主键须存在，用于create标志此操作为更新，【不需要】

        $brw_data['device_IDs'] = '12,13,14';
        $brw_data['price'] = '2';
        // $brw_data['openid'] = 'jhskwqujsdlkwql';

        $test_model = D('DeviceRecord');

        if ($test_model->where("d_id = $d_id")->create($brw_data, 2)) {// 2标志更新，不可缺
            echo "create成功<br/>";
            // echo $test_model->cTime."<br/>";

            echo "***".$result = $test_model->scope('allowUpdateField,new')->where("d_id = $d_id")->save();

            // p($test_model);

            if ($result) {
                
                echo "更新成功<br/>";
            }else{

                echo "更新失败<br/>";
                echo $test_model->getError();
            }

        }else{

            echo "create失败<br/>";
            echo $test_model->getError();
        }

        echo "编辑申请，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 取消申请
     */
    public function cancel(){

        echo "取消申请，begin<br/>";

        $d_id = 1;// 模拟操作的记录id

        $cancel['status'] = self::STATUS_CANCEL;


        $test_model = D('DeviceRecord');

        $old_status = $test_model->where("d_id = $d_id")->getField('status');
        echo "old_status = $old_status";
        if ($old_status == $cancel['status']) {// 状态未改变

            echo "状态未改变，不需要更新<br/>";
            return false;
        }

        if ($test_model->where("d_id = $d_id")->create($cancel, 2)) {// 2标志更新，不可缺
            echo "create成功<br/>";
            // echo $test_model->status."<br/>";
            // echo $test_model->cTime."<br/>";

            echo "***".$result = $test_model->scope('allowUpdateField,new')->where("d_id = $d_id")->save();

            // p($test_model);

            if ($result) {
                
                echo "更新成功<br/>";
                
                // 需要更新d_record_2_stime表中记录
                dump(update_d_sTime($d_id, $cancel['status']));
            }else{

                echo "更新失败<br/>";
                echo $test_model->getError();
            }

        }else{

            echo "create失败<br/>";
            echo $test_model->getError();
        }

        echo "取消申请，end<br/>";
        die;
        
        $this->display();
    }
}