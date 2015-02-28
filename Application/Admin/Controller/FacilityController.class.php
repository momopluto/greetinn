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

        $Room = M('room');

        $data = $Room->join('type_price ON type_price.type = room.type')->select();// ->order('cTime desc')
        // p($data);
        // die;

        $this->assign('data', $data);
        $types = M('type_price')->getField('type,name,price');
        $this->assign('types', $types);
		$this->display();
	}

    /**
     * 设备房间价格
     */
    public function setR(){
        
        if (IS_POST) {
            // p(I('post.'));die;

            $model = M('type_price');
            $data['type'] = I('post.type');
            $data['name'] = I('post.name');
            $data['price'] = I('post.price');

            if ($model->save($data)) {
                echo "更新房间类型-价格成功！";
                $this->success('更新房间类型-价格成功！', U('Admin/Facility/room'));
                return;
            }else{
                echo "更新房间类型-价格失败！";
                // echo $model->getError();

                $this->error($model->getError());
                return;
            }
            
            // p($data);die;
        }
    }

    /**
     * 添加房间
     */
    public function addR(){

        // echo "添加房间，begin<br/>";

        if (IS_POST) {
            // $new_room['room_ID'] = '228 test12';
            // $new_room['price'] = '118';
            // $new_room['type'] = self::TYPE_SINGLE;
            // $new_room['desc'] = '可以为空';

            $new_room['room_ID'] = I('post.ID');
            // $new_room['price'] = I('post.price');
            $new_room['type'] = I('post.type');
            $new_room['desc'] = I('post.desc');

            $room_model = D('Room');

            $room_model->startTrans();// 启动事务

            if ($room_model->create($new_room)) {
                echo "create成功<br/>";

                $r_id = $room_model->add();// 新房间记录写入room表

                if ($r_id === false) {
                    $this->error('写入数据库失败！');
                    return;
                }
                

                /* 添加房间，写日志需要信息：

                日志事件类型self::ADMIN_ROOM_ADD
                新增所得的           '房间id' => $r_id
                新增的字段数据        '房间号' => $new_room['room_ID']
                    
                */

                $log_Arr = array($this->log_model, $this->log_data, $room_model, self::ADMIN_ROOM_ADD, 'add', array('房间id' => $r_id, '房间号' => $new_room['room_ID']));
                //                     0                 1                2             3                4                            5
                write_log_all_array($log_Arr);
                // write_log_all($this->log_model, $this->log_data, $room_model, self::ADMIN_ROOM_ADD, 'add', array('房间id' => $r_id, '房间号' => $new_room['room_ID']));

                $this->success('新增房间成功！', U('Admin/Facility/room'));
            }else{

                echo "create失败<br/>";
                // echo $room_model->getError();
                $this->error($room_model->getError());
                return;
            }
        }

        // echo "添加房间，end<br/>";
        // die;
        
        // $this->display();
    }

    /**
     * 编辑房间
     */
    public function editR(){

        // p(I('post.'));die;
        // echo "编辑房间，begin<br/>";

        if (IS_POST) {

            if (!I('get.id')) {
                $this->error('ERROR, id不能为空！');
                return;
            }
            
            $r_id = I('get.id');
            // $r_id = 1;// 模拟操作的房间id
            // $room_ID = '228';// 模拟操作的房间号

            // $edit_room['r_id'] = $r_id;// 主键也必须加上，却不对其更行更改
            // $edit_room['room_ID'] = '228 test edit2';
            // $edit_room['price'] = '238';
            // $edit_room['type'] = self::TYPE_SINGLE;
            // $edit_room['desc'] = '';

            $edit_room['r_id'] = $r_id;// 主键也必须加上，却不对其更行更改
            $edit_room['room_ID'] = I('post.ID');
            // $edit_room['price'] = I('post.price');
            $edit_room['type'] = I('post.type');
            $edit_room['desc'] = I('post.desc');
            $edit_room['is_open'] = I('post.status');

            $Room = D('Room');

            $Room->startTrans();// 启动事务

            if ($Room->where("r_id = $r_id")->create($edit_room, 2)) {// 2，显示标识此次操作为更新
                echo "create成功<br/>";

                // p($Room);

                echo "*** " . $result = $Room->save();
                if ($result) {
                    echo "更新成功<br/>";
                    unset($edit_room['r_id']);// 去掉主键
                    
                    /* 编辑房间，写日志需要信息：

                    日志事件类型self::ADMIN_ROOM_EDIT
                    编辑的对象           '房间id' => $r_id
                    // 新增的字段数据        '房间号' => $new_room['room_ID']
                    更新的信息数组         $edit_room   需要调用format_edit_info($edit_room)得到 $edit_info
                        
                    */

                    $log_Arr = array($this->log_model, $this->log_data, $Room, self::ADMIN_ROOM_EDIT, 'edit', array('房间id' => $r_id), $edit_room);
                    //                     0                 1                2             3                4                            5         6
                    write_log_all_array($log_Arr);
                    // write_log_all($this->log_model, $this->log_data, $Room, self::ADMIN_ROOM_EDIT, 'edit', array('房间id' => $r_id), $edit_room);

                    $this->success('更新房间成功！');
                    return;
                }else{

                    echo "更新失败<br/>";
                    // echo $Room->getError();
                    $this->error($Room->getError());
                    return;
                }
            }else{

                echo "create失败<br/>";
                // echo $Room->getError();
                $this->error($Room->getError());
                return;
            }
        }
        

        // echo "编辑房间，end<br/>";
        // die;
        
        // $this->display();
    }

    /**
     * 删除房间
     */
    public function delR(){

        if (!I('get.id')) {
            $this->error('ERROR, id不能为空！');
            return;
        }

        // echo "删除房间，begin<br/>";

        $r_id = I('get.id');
        // $r_id = array(31, 49, 54);// 模拟操作的房间id
        // $room_ID = '228';// 模拟操作的房间号

        $Room = M('room');

        /* 删除房间，写日志需要信息：

        日志事件类型self::ADMIN_ROOM_DELETE
        编辑的对象           '房间id' => $r_id（可能为数组）
        在数据真正被删之前需要收集被删房间的：房间id，房间号，组合成$del_info 
        // 新增的字段数据        '房间号' => $new_room['room_ID']
        // 更新的信息数组         $edit_room   需要调用format_edit_info($edit_room)得到 $edit_info
            
        */
        
        // 记录所删除的数据信息
        $del_info = get_delete_info($Room, array('房间id', $r_id, '房间号', 'room_ID'));
        
        // echo "组合所得的del_info =: ".$del_info."  !!!!<br/>";

        if (is_array($r_id)) {// 如果是数组，组合成以','为连接符的字符串
            $r_id = implode(',', $r_id);
        }
        
        $Room->startTrans();// 启动事务
        
        if ($Room->delete($r_id)){
            
            echo $r_id."删除成功<br/>";

            $log_Arr = array($this->log_model, $this->log_data, $Room, self::ADMIN_ROOM_DELETE, 'delete', $del_info);
            //                     0                 1                2             3                    4           5
            write_log_all_array($log_Arr);
            // write_log_all($this->log_model, $this->log_data, $room_model, self::ADMIN_ROOM_DELETE, 'delete', $del_info);

            $this->success('删除房间成功！', U('Admin/Facility/room'));
            return;
        }else{
            echo $r_id."删除失败<br/>";

            $this->error($Room->getError());
            return;
        }
        
        // echo "删除房间，end<br/>";
        // die;
        
        // $this->display();
    }


    /**
	 * 设备列表
	 */
    public function device(){

        $Device = M('device');

        $data = $Device->getField('device_ID,pid,name,price,desc,on_use,allNum,stock,cTime');

        // p($data);

        // 得到父类ID
        $pids = $Device->where("pid = 0")->getField('device_ID', true);
        // p($pids);
        
        $IDs = array();// 已经分类的数据  父id => 该类别下的设备id数组
        foreach ($pids as $p) {
            $one = $Device->where("pid = $p")->getField('device_ID,device_ID', true);
            $IDs[$p] = $one;
            
            $data[$p]['allNum'] = count($IDs[$p]);// 类别所含设备数量
        }
        // p($IDs);

        // p($data);die;
        
        $this->assign('IDs', $IDs);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 添加设备类别
     */
    public function addD_Cate(){
        
        if (IS_POST) {

            // 此处验证数据合法性
            if (trim(I('post.name')) == '') {
                $this->error('类别名不能为空！');
                return;
            }
            
            // name, desc
            $new_cate['name'] = I('post.name');
            $new_cate['desc'] = I('post.desc');
            $new_cate['cTime'] = getDatetime();

            $Device = M('Device');

            $Device->startTrans();// 启动事务

            if ($Device->create($new_cate)) {
                
                echo "create成功<br/>";
                
                $device_ID = $Device->add();


                $log_Arr = array($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_C_ADD, 'add', array('类别id' => $device_ID, '类名' => $new_cate['name']));
                //                     0                 1                2                  3               4                                 5
                write_log_all_array($log_Arr);
                // write_log_all($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_ADD, 'add', array('设备id' => $device_ID, '名称' => $new_cate['name']));
                $this->success('新增类别成功！', U('Admin/Facility/device'));
                return;
            }else{

                // echo "create失败<br/>";
                // echo $Device->getError();
                $this->error($Device->getError());
                return;
            }
        }

    }

    /**
     * 编辑设备类别
     */
    public function editD_Cate(){
        if (IS_POST) {
            if (!I('get.id')) {
                $this->error('ERROR, id不能为空！');
                return;
            }

            // 此处验证数据合法性
            if (trim(I('post.name')) == '') {
                $this->error('类别名不能为空！');
                return;
            }

            $D_cate_id = I('get.id');

            $edit_cate['device_ID'] = $D_cate_id;// 为配合更新name，字段必须存在
            $edit_cate['name'] = I('post.name');
            $edit_cate['desc'] = I('post.desc');

            $Device = D('Device');

            $Device->startTrans();// 启动事务

            if ($Device->where("device_ID = $D_cate_id")->create($edit_cate, 2)) {
                
                echo "create成功<br/>";

                // p($Device);
                
                echo "***" . $result = $Device->save();
                if ($result) {
                    
                    echo "更新成功<br/>";
                    unset($edit_cate['device_ID']);// 去掉主键

                    $log_Arr = array($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_C_EDIT, 'edit', array('类别id' => $D_cate_id), $edit_cate);
                    //                     0                 1                2                   3                4                   5                 6
                    write_log_all_array($log_Arr);
                    // write_log_all($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_EDIT, 'edit', array('设备id' => $device_ID), $edit_cate);

                    $this->success('更新类别成功！', U('Admin/Facility/device'));
                    return;
                }else{

                    echo "更新失败<br/>";
                    // echo $Device->getError();

                    $this->error($Device->getError());
                    return;
                }

            }else{

                echo "create失败<br/>";
                // echo $Device->getError();

                $this->error($Device->getError());
                return;
            }
        }
    }

    /**
     * 删除设备类别
     * PS: 每次只能删除1个类别
     */
    public function delD_Cate(){

        if (!I('get.id')) {
            $this->error('ERROR, id不能为空！');
            return;
        }

        $D_cate_id = I('get.id');

        $Device = M('device');
        
        // 记录所删除的类别信息
        $del_info = get_delete_info($Device, array('类别id', $D_cate_id, '类别名', 'name'));
        
        echo "组合所得的del_info =: ".$del_info."  !!!!<br/>";

        if (is_array($D_cate_id)) {// 如果是数组，组合成以','为连接符的字符串
            $D_cate_id = implode(',', $D_cate_id);
        }
        
        $Device->startTrans();// 启动事务
        
        if ($Device->delete($D_cate_id)) {
            echo $D_cate_id."删除成功<br/>";

            // 关联删除类别中的设备********************
            $device_IDs = $Device->where("pid = $D_cate_id")->getField('device_ID', true);
            if ($device_IDs) {// 类别中有设备，删设备

                echo $device_IDs." ===== <br/>";
                p($device_IDs);
                // 记录所删除的类别中的设备信息
                $del_info_2 = get_delete_info($Device, array('设备id', $device_IDs, '名称', 'name'));
                echo "组合所得的del_info_2 =: ".$del_info_2."  !!!!<br/>";
                
                $del_info .= "; 涉及的" . $del_info_2;// 最终的删除的数据的信息
                echo $del_info;
                
                if (is_array($device_IDs)) {// 如果是数组，组合成以','为连接符的字符串
                    $device_IDs = implode(',', $device_IDs);
                }

                if (!$Device->delete($device_IDs)) {

                    $this->error('类别中的设备关联删除失败！');
                    return;
                }
            }

            // 写日志
            $log_Arr = array($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_C_DELETE, 'delete', $del_info);
            //                     0                 1                2             3                    4           5
            write_log_all_array($log_Arr);
            // write_log_all($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_C_DELETE, 'delete', $del_info);

            $this->success('删除类别成功！', U('Admin/Facility/device'));
            return;
            
        }else{
            echo $D_cate_id."删除类别失败<br/>";
            
            $this->error($Device->getError());
            return;
        }
    }

    /**
     * 添加设备
     */
    public function addD(){

        // p(I('post.'));die;

        // echo "添加设备，begin<br/>";

        if (IS_POST) {
            // $new_device['pid'] = '2';
            // $new_device['name'] = '阿瓦隆';
            // $new_device['price'] = '2';
            // $new_device['desc'] = '可以为空';
            // $new_device['allNum'] = '7';
            // $new_device['stock'] = $new_device['allNum'];

            $new_device['pid'] = I('post.pid');
            $new_device['name'] = I('post.name');
            $new_device['price'] = I('post.price');
            $new_device['desc'] = I('post.desc');
            $new_device['allNum'] = I('post.allNum');
            $new_device['stock'] = $new_device['allNum'];

            $Device = D('Device');

            // p($new_device);

            $Device->startTrans();// 启动事务

            if ($Device->create($new_device)) {
                
                echo "create成功<br/>";
                
                $device_ID = $Device->add();

                if ($device_ID === false) {
                    $this->error('写入数据库失败！');
                    return;
                }

                $log_Arr = array($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_ADD, 'add', array('设备id' => $device_ID, '名称' => $new_device['name']));
                //                     0                 1                2                  3               4                                 5
                write_log_all_array($log_Arr);
                // write_log_all($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_ADD, 'add', array('设备id' => $device_ID, '名称' => $new_device['name']));

                $this->success('新增设备成功！', U('Admin/Facility/device'));
                return;
            }else{

                echo "create失败<br/>";
                // echo $Device->getError();

                $this->error($Device->getError());
                return;
            }
        }


        // echo "添加设备，end<br/>";
        // die;
        
        // $this->display();
    }

    /**
     * 编辑设备
     */
    public function editD(){

        // echo "编辑设备，begin<br/>";

        if (!I('get.id')) {
            $this->error('ERROR, id不能为空！');
            return;
        }

        $device_ID = I('get.id');

        $edit_device['device_ID'] = $device_ID;// 为配合更新name，字段必须存在
        // $edit_device['pid'] = '1';
        // $edit_device['name'] = '阿瓦隆2';
        // $edit_device['price'] = '2';
        // $edit_device['desc'] = '可以为空';
        // $edit_device['allNum'] = '9';
        // $edit_device['stock'] = '7';// 要修改stock，就要一起给出allNum，用于自动验证

        $edit_device['pid'] = I('post.pid');
        $edit_device['name'] = I('post.name');
        $edit_device['price'] = I('post.price');
        $edit_device['desc'] = I('post.desc');
        $edit_device['allNum'] = I('post.allNum');
        $edit_device['stock'] = I('post.stock');// 要修改stock，就要一起给出allNum，用于自动验证

        $Device = D('Device');

        // p($edit_device);

        $Device->startTrans();// 启动事务
        
        if ($Device->where("device_ID = $device_ID")->create($edit_device, 2)) {
            
            echo "create成功<br/>";

            // p($Device);
            
            echo "***" . $result = $Device->where("device_ID = $device_ID")->save();
            if ($result) {
                
                echo "更新成功<br/>";
                unset($edit_device['device_ID']);// 去掉主键


                $log_Arr = array($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_EDIT, 'edit', array('设备id' => $device_ID), $edit_device);
                //                     0                 1                2                   3                4                   5                 6
                write_log_all_array($log_Arr);
                // write_log_all($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_EDIT, 'edit', array('设备id' => $device_ID), $edit_device);

                $this->success('更新设备成功！', U('Admin/Facility/device'));
                return;
            }else{

                echo "更新失败<br/>";
                // echo $Device->getError();

                $this->error($Device->getError());
                return;
            }

        }else{

            echo "create失败<br/>";
            // echo $Device->getError();

            $this->error($Device->getError());
            return;
        }

        // echo "编辑设备，end<br/>";
        // die;
        
        // $this->display();
    }

    /**
     * 删除设备
     */
    public function delD(){

        if (!I('get.id')) {
            $this->error('ERROR, id不能为空！');
            return;
        }
        // echo "删除设备，begin<br/>";

        $device_ID = I('get.id');
        
        $Device = M('device');

        // 记录所删除的数据信息
        $del_info = get_delete_info($Device, array('设备id', $device_ID, '名称', 'name'));
        
        // echo "组合所得的del_info =: ".$del_info."  !!!!<br/>";

        if (is_array($device_ID)) {// 如果是数组，组合成以','为连接符的字符串
            $device_ID = implode(',', $device_ID);
        }

        $Device->startTrans();// 启动事务
        
        if ($Device->delete($device_ID)){
            
            echo "删除成功<br/>";

            $log_Arr = array($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_DELETE, 'delete', $del_info);
            //                     0                 1                2                 3                    4           5
            write_log_all_array($log_Arr);
            // write_log_all($this->log_model, $this->log_data, $Device, self::ADMIN_DEVICE_DELETE, 'delete', $del_info);

            $this->success('删除设备成功！', U('Admin/Facility/device'));
            return;
        }else{
            echo "删除失败<br/>";
            $this->error($Device->getError());
            return;
        }

        // echo "删除设备，end<br/>";
        // die;
        
        // $this->display();
    }
}