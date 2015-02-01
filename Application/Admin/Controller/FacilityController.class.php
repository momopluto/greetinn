<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 设施管理控制器
 */
class FacilityController extends AdminController {

    // 房间类型
    const TYPE_DOUBLE           =   0;      //  双人床
    const TYPE_SINGLE           =   1;      //  单人床，大床房



	/**
	 * 房间列表
	 */
	public function room(){

		$this->display();
	}

    /**
     * 添加房间
     */
    public function addR(){

        echo "添加房间，begin<br/>";

        $new_room['room_ID'] = '228 test12';
        $new_room['price'] = '118';
        $new_room['type'] = self::TYPE_SINGLE;
        $new_room['desc'] = '可以为空';

        $room_model = D('Room');

        $room_model->startTrans();// 启动事务

        if ($room_model->create($new_room)) {
            echo "create成功<br/>";

            $r_id = $room_model->add();// 新房间记录写入room表
            

            /* 添加房间，写日志需要信息：

            日志事件类型self::ADMIN_ROOM_ADD
            新增所得的           '房间id' => $r_id
            新增的字段数据        '房间号' => $new_room['room_ID']
                
            */

            $log_Arr = array($this->log_model, $this->log_data, $room_model, self::ADMIN_ROOM_ADD, 'add', array('房间id' => $r_id, '房间号' => $new_room['room_ID']));
            //                     0                 1                2             3                4                            5
            write_log_all_array($log_Arr);
            // write_log_all($this->log_model, $this->log_data, $room_model, self::ADMIN_ROOM_ADD, 'add', array('房间id' => $r_id, '房间号' => $new_room['room_ID']));


            // // 日志事件
            // $this->log_data['event'] = self::ADMIN_ROOM_ADD . "，房间id: " . $r_id . "，房间号: " . $new_room['room_ID'];
            
            // // 写日志
            // if (!write_log($this->log_model, $this->log_data)) {
            //     // 如果不成功，rollback
                
            //     $room_model->rollback();// 回滚事务
            // }else{// 成功

            //     $room_model->commit();// 提交事务
            // }

            
        }else{

            echo "create失败<br/>";
            echo $room_model->getError();
        }

        echo "添加房间，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 编辑房间
     */
    public function editR(){

        echo "编辑房间，begin<br/>";

        $r_id = 54;// 模拟操作的房间id
        // $room_ID = '228';// 模拟操作的房间号

        $edit_room['r_id'] = $r_id;// 主键也必须加上，却不对其更行更改
        $edit_room['room_ID'] = '228 test edit2';
        $edit_room['price'] = '238';
        $edit_room['type'] = self::TYPE_SINGLE;
        // $edit_room['desc'] = '';

        $room_model = D('Room');

        $room_model->startTrans();// 启动事务

        if ($room_model->where("r_id = $r_id")->create($edit_room, 2)) {// 2，显示标识此次操作为更新
            echo "create成功<br/>";

            // p($room_model);

            echo "*** " . $result = $room_model->where("r_id = $r_id")->save();
            if ($result) {
                echo "更新成功<br/>";
                unset($edit_room['r_id']);// 去掉主键
                
                /* 编辑房间，写日志需要信息：

                日志事件类型self::ADMIN_ROOM_EDIT
                编辑的对象           '房间id' => $r_id
                // 新增的字段数据        '房间号' => $new_room['room_ID']
                更新的信息数组         $edit_room   需要调用format_edit_info($edit_room)得到 $edit_info
                    
                */

                $log_Arr = array($this->log_model, $this->log_data, $room_model, self::ADMIN_ROOM_EDIT, 'edit', array('房间id' => $r_id), $edit_room);
                //                     0                 1                2             3                4                            5         6
                write_log_all_array($log_Arr);
                // write_log_all($this->log_model, $this->log_data, $room_model, self::ADMIN_ROOM_EDIT, 'edit', array('房间id' => $r_id), $edit_room);

                // // 记录所更新的字段信息
                // $edit_info = format_edit_info($edit_room);

                // // 日志事件
                // $this->log_data['event'] = self::ADMIN_ROOM_EDIT . "，房间id: " . $r_id . "，" . $edit_info;
                
                // // 写日志
                // if (!write_log($this->log_model, $this->log_data)) {
                //     // 如果不成功，rollback
                    
                //     $room_model->rollback();// 回滚事务
                // }else{// 成功

                //     $room_model->commit();// 提交事务
                // }
            }else{

                echo "更新失败<br/>";
                echo $room_model->getError();
            }
        }else{

            echo "create失败<br/>";
            echo $room_model->getError();
        }

        echo "编辑房间，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 删除房间
     */
    public function delR(){

        echo "删除房间，begin<br/>";

        // $r_id = 16;
        $r_id = array(31, 49, 54);// 模拟操作的房间id
        // $room_ID = '228';// 模拟操作的房间号

        $room_model = M('room');

        /* 删除房间，写日志需要信息：

        日志事件类型self::ADMIN_ROOM_DELETE
        编辑的对象           '房间id' => $r_id（可能为数组）
        在数据真正被删之前需要收集被删房间的：房间id，房间号，组合成$del_info 
        // 新增的字段数据        '房间号' => $new_room['room_ID']
        // 更新的信息数组         $edit_room   需要调用format_edit_info($edit_room)得到 $edit_info
            
        */

        
        // 记录所删除的数据信息
        $del_info = get_delete_info($room_model, array('房间id', $r_id, '房间号', 'room_ID'));
        
        echo "组合所得的del_info =: ".$del_info."  !!!!<br/>";

        if (is_array($r_id)) {// 如果是数组，组合成以','为连接符的字符串
            $r_id = implode(',', $r_id);
        }
        // if (is_array($r_id)) {// 如果是数组，组合成以','为连接符的字符串

        //     foreach ($r_id as $value) {
        //         // echo $value."??????<br>";
        //         $del_room_1 = $room_model->find($value);
        //         // p($del_room_1);
        //         $del_info .= $del_room_1['room_ID'] . ",";
        //     }

        //     $del_info = "房间号: " . substr($del_info, 0, strlen($del_info) - 1);
            
        //     $r_id = implode(',', $r_id);
            
        //     $del_info = "房间id: " . $r_id . "，" . $del_info;
        // }else{
            
        //     $del_room_1 = $room_model->find($r_id);
        //     $del_info = "房间id: " . $r_id . "，房间号: " . $del_room_1['room_ID'];
        // }
        
        $room_model->startTrans();// 启动事务
        
        if ($room_model->delete($r_id)){
            
            echo $r_id."删除成功<br/>";

            $log_Arr = array($this->log_model, $this->log_data, $room_model, self::ADMIN_ROOM_DELETE, 'delete', $del_info);
            //                     0                 1                2             3                    4           5
            write_log_all_array($log_Arr);
            // write_log_all($this->log_model, $this->log_data, $room_model, self::ADMIN_ROOM_DELETE, 'delete', $del_info);

            // // 日志事件
            // $this->log_data['event'] = self::ADMIN_ROOM_DELETE . "，" . $del_info;
            
            // // 写日志
            // if (!write_log($this->log_model, $this->log_data)) {
            //     // 如果不成功，rollback
                
            //     $room_model->rollback();// 回滚事务
            // }else{// 成功

            //     $room_model->commit();// 提交事务
            // }
        }
        
        echo "删除房间，end<br/>";
        die;
        
        $this->display();
    }


    /**
	 * 设备列表
	 */
    public function device(){
        
        $this->display();
    }

    /**
     * 添加设备
     */
    public function addD(){

        echo "添加设备，begin<br/>";

        $new_device['pid'] = '2';
        $new_device['name'] = '阿瓦隆';
        $new_device['price'] = '2';
        // $new_device['desc'] = '可以为空';
        // $new_device['on_use'] = '1';
        $new_device['allNum'] = '7';
        $new_device['stock'] = '7';

        $device_model = D('Device');

        // p($new_device);

        $device_model->startTrans();// 启动事务

        if ($device_model->create($new_device)) {
            
            echo "create成功<br/>";
            
            $device_ID = $device_model->add();


            $log_Arr = array($this->log_model, $this->log_data, $device_model, self::ADMIN_DEVICE_ADD, 'add', array('设备id' => $device_ID, '名称' => $new_device['name']));
            //                     0                 1                2                  3               4                                 5
            write_log_all_array($log_Arr);
            // write_log_all($this->log_model, $this->log_data, $device_model, self::ADMIN_DEVICE_ADD, 'add', array('设备id' => $device_ID, '名称' => $new_device['name']));

            // // 日志事件
            // $this->log_data['event'] = self::ADMIN_DEVICE_ADD . "，设备id: " . $device_ID;
            
            // // 写日志
            // if (!write_log($this->log_model, $this->log_data)) {
            //     // 如果不成功，rollback
                
            //     $device_model->rollback();// 回滚事务
            // }else{// 成功

            //     $device_model->commit();// 提交事务
            // }

        }else{

            echo "create失败<br/>";
            echo $device_model->getError();
        }

        echo "添加设备，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 编辑设备
     */
    public function editD(){

        echo "编辑设备，begin<br/>";

        $device_ID = 25;// 模拟操作的设备id

        $edit_device['device_ID'] = $device_ID;// 为配合更新name，字段必须存在
        $edit_device['pid'] = '1';
        $edit_device['name'] = '阿瓦隆2';
        // $edit_device['price'] = '2';
        // $edit_device['desc'] = '可以为空';
        // $edit_device['on_use'] = '0';
        $edit_device['allNum'] = '9';
        $edit_device['stock'] = '7';// 要修改stock，就要一起给出allNum，用于自动验证

        $device_model = D('Device');

        // p($edit_device);

        $device_model->startTrans();// 启动事务
        
        if ($device_model->where("device_ID = $device_ID")->create($edit_device, 2)) {
            
            echo "create成功<br/>";

            // p($device_model);
            
            echo "***" . $result = $device_model->where("device_ID = $device_ID")->save();
            if ($result) {
                
                echo "更新成功<br/>";
                unset($edit_device['device_ID']);// 去掉主键


                $log_Arr = array($this->log_model, $this->log_data, $device_model, self::ADMIN_DEVICE_EDIT, 'edit', array('设备id' => $device_ID), $edit_device);
                //                     0                 1                2                   3                4                   5                 6
                write_log_all_array($log_Arr);
                // write_log_all($this->log_model, $this->log_data, $device_model, self::ADMIN_DEVICE_EDIT, 'edit', array('设备id' => $device_ID), $edit_device);

                // // 记录所更新的字段信息
                // $edit_info = format_edit_info($edit_device);
                
                // // 日志事件
                // $this->log_data['event'] = self::ADMIN_DEVICE_EDIT . "，设备id: " . $device_ID . "，" . $edit_info;
                
                // // 写日志
                // if (!write_log($this->log_model, $this->log_data)) {
                //     // 如果不成功，rollback
                    
                //     $device_model->rollback();// 回滚事务
                // }else{// 成功

                //     $device_model->commit();// 提交事务
                // }
            }else{

                echo "更新失败<br/>";
                echo $device_model->getError();
            }

        }else{

            echo "create失败<br/>";
            echo $device_model->getError();
        }

        echo "编辑设备，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 删除设备
     */
    public function delD(){

        echo "删除设备，begin<br/>";

        $device_ID = 25;
        // $device_ID = array(20,24);// 模拟操作的设备id

        // if (is_array($device_ID)) {// 如果是数组，组合成以','为连接符的字符串
            
        //     $device_ID = implode(',', $device_ID);
        // }
        
        $device_model = M('device');


        // 记录所删除的数据信息
        $del_info = get_delete_info($device_model, array('设备id', $device_ID, '名称', 'name'));
        
        echo "组合所得的del_info =: ".$del_info."  !!!!<br/>";

        if (is_array($device_ID)) {// 如果是数组，组合成以','为连接符的字符串
            $device_ID = implode(',', $device_ID);
        }

        $device_model->startTrans();// 启动事务
        
        if ($device_model->delete($device_ID)){
            
            echo "删除成功<br/>";

            $log_Arr = array($this->log_model, $this->log_data, $device_model, self::ADMIN_DEVICE_DELETE, 'delete', $del_info);
            //                     0                 1                2                 3                    4           5
            write_log_all_array($log_Arr);
            // write_log_all($this->log_model, $this->log_data, $device_model, self::ADMIN_DEVICE_DELETE, 'delete', $del_info);

            // // 日志事件
            // $this->log_data['event'] = self::ADMIN_DEVICE_DELETE . "，设备id: " . $device_ID;
            
            // // 写日志
            // if (!write_log($this->log_model, $this->log_data)) {
            //     // 如果不成功，rollback
                
            //     $device_model->rollback();// 回滚事务
            // }else{// 成功

            //     $device_model->commit();// 提交事务
            // }
        }

        echo "删除设备，end<br/>";
        die;
        
        $this->display();
    }
}