<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 设备管理控制器
 */
class DeviceController extends HomeController {

    // 操作状态
    const STATUS_CANCEL          =   0;      //  状态值，取消
    const STATUS_NEW             =   1;      //  状态值，新建
    const STATUS_RESPONSE        =   2;      //  状态值，响应
    const STATUS_RETURN          =   3;      //  状态值，归还
    
    /**
     * 未完成订单
     */
    public function dealing(){
        
        $this->display();
    }

    /**
     * 已完成订单
     */
    public function complete(){
        
        $this->display();
    }

    /**
     * 设备清单，[助]客户借设备
     */
    public function lists(){
        
        $this->display();
    }

    /**
     * 提交申请
     */
    public function submit(){
        
        echo "[前台]提交申请，begin<br/>";

        // TODO,*******提供房间号，对过房间号找到记录o_id

        $brw_data['o_id'] = '1';// 外键约束
        $brw_data['device_IDs'] = '12,13,16';
        $brw_data['price'] = '0';
        // $brw_data['openid'] = 'jhskwqujsdlkwql';

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

        echo "[前台]提交申请，end<br/>";
        die;

        $this->display();
    }

    /**
     * 编辑申请
     */
    public function edit(){

        echo "[前台]编辑申请，begin<br/>";

        $d_id = 1;// 模拟操作的记录id

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

        echo "[前台]编辑申请，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 取消申请
     */
    public function cancel(){

        echo "[前台]取消申请，begin<br/>";

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

        echo "[前台]取消申请，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 响应请求
     */
    public function response(){

        echo "[前台]响应申请，begin<br/>";

        $d_id = 1;// 模拟操作的记录id

        $response['status'] = self::STATUS_RESPONSE;


        $test_model = D('DeviceRecord');

        $old_status = $test_model->where("d_id = $d_id")->getField('status');
        echo "old_status = $old_status";
        if ($old_status == $response['status']) {// 状态未改变

            echo "状态未改变，不需要更新<br/>";
            return false;
        }

        if ($test_model->where("d_id = $d_id")->create($response, 2)) {// 2标志更新，不可缺
            echo "create成功<br/>";

            echo "***".$result = $test_model->scope('allowUpdateField,new')->where("d_id = $d_id")->save();

            // p($test_model);

            if ($result) {
                
                echo "更新成功<br/>";
                
                // 需要更新d_record_2_stime表中记录
                dump(update_d_sTime($d_id, $response['status']));
            }else{

                echo "更新失败<br/>";
                echo $test_model->getError();
            }

        }else{

            echo "create失败<br/>";
            echo $test_model->getError();
        }

        echo "[前台]响应申请，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 确认归还
     */
    public function c_return(){

        echo "[前台]确认归还，begin<br/>";

        $d_id = 1;// 模拟操作的记录id

        $return['status'] = self::STATUS_RETURN;


        $test_model = D('DeviceRecord');

        $old_status = $test_model->where("d_id = $d_id")->getField('status');
        echo "old_status = $old_status";
        if ($old_status == $return['status']) {// 状态未改变

            echo "状态未改变，不需要更新<br/>";
            return false;
        }

        if ($test_model->where("d_id = $d_id")->create($return, 2)) {// 2标志更新，不可缺
            echo "create成功<br/>";

            echo "***".$result = $test_model->scope('allowUpdateField,response')->where("d_id = $d_id")->save();

            // p($test_model);

            if ($result) {
                
                echo "更新成功<br/>";
                
                // 需要更新d_record_2_stime表中记录
                dump(update_d_sTime($d_id, $return['status']));
            }else{

                echo "更新失败<br/>";
                echo $test_model->getError();
            }

        }else{

            echo "create失败<br/>";
            echo $test_model->getError();
        }

        echo "[前台]确认归还，end<br/>";
        die;
        
        $this->display();
    }
    
}