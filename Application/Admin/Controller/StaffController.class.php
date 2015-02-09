<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 人员管理控制器
 */
class StaffController extends AdminController {

    // 职位
    const POSITION_RECEPTIONIST     =   1;      //  前台
    // const POSITION_SUPERVISOR    =   5;      //  主管
    const POSITION_ADMINISTRATOR    =   9;      //  管理员

	/**
	 * 客户列表
	 */
    public function client(){
        
        $model = M('client');

        // 编号，姓名，身份证，性别，生日，微信号，手机，注册时间(按此降序排序)，累计预订晚数
        $qryStr = "SELECT client.client_ID,`name`,ID_card,s_ID,gender,birthday,openid,phone,client.cTime,COUNT(nights) as countNights
                   FROM `client`  LEFT JOIN o_record NATURAL JOIN o_record_2_room
                       on client.client_ID=o_record.client_ID AND o_record.o_id=o_record_2_room.o_id
                   GROUP BY client_ID
                   ORDER BY client.cTime desc";// countNights
        
        $data = $model->query($qryStr);
        
        // p($data);

        // p($model);die;

        $this->assign('data', $data);
        $this->display();
    }
    
    /**
     * 工作人员列表
     */
    public function member(){

        $model = M('member');

        $data = $model->select();
        
        // p($data);

        // p($model);
        // die;

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 添加工作人员
     */
    public function add(){

        if (IS_POST) {
            
            // echo "添加工作人员，begin<br/>";

            // $new_member['name'] = '刘恩坚';
            // $new_member['ID_card'] = '441423199305165018';
            // $new_member['gender'] = '1';
            // $new_member['phone'] = '18826481053';
            // $new_member['position'] = self::POSITION_ADMINISTRATOR;
            // $new_member['salary'] = '800';


            // name,ID,sex,phone,state,salary
            $new_member['name'] = I('post.name');
            $new_member['ID_card'] = I('post.ID');
            $new_member['gender'] = I('post.sex');
            $new_member['phone'] = I('post.phone');
            if (I('post.state') == 9) {
                
                $new_member['position'] = self::POSITION_ADMINISTRATOR;
            }elseif (I('post.state') == 1) {

                $new_member['position'] = self::POSITION_RECEPTIONIST;
            }else{
                $this->error('职位信息有误！');
                return;
            }
            
            $new_member['salary'] = I('post.salary');// 需要进一步限制为非负数字

            $Member = D('Member');

            $Member->startTrans();// 启动事务

            if ($Member->create($new_member)) {
                // echo "create成功<br/>";

                // echo $Member->position."<br/>";
                // echo $Member->birthday."<br/>";
                // echo $Member->on_job."<br/>";
                // echo $Member->cTime."<br/>";

                $member_ID = $Member->add();

                if ($member_ID === false) {
                    
                    $this->error('写入数据失败！');
                    return;
                }

                // 写日志
                $log_Arr = array($this->log_model, $this->log_data, $Member, self::ADMIN_MEMBER_ADD, 'add', array('成员id' => $member_ID, '姓名' => $new_member['name']));
                //                     0                 1                2             3                4                            5
                write_log_all_array($log_Arr);

                $this->success('新增工作人员成功！', U('Admin/Staff/member'));
            }else{

                $this->error($Member->getError());
                return;
            }
        }else{
            
            $this->display();
        }
    }

    /**
     * 编辑工作人员
     */
    public function edit(){
        // die;
        // echo "编辑工作人员，begin<br/>";

        if (IS_POST) {
            // $member_ID = 1;// 模拟操作的成员id
            // $edit_member['phone'] = '18826481053';
            // $edit_member['position'] = self::POSITION_ADMINISTRATOR;
            // $edit_member['salary'] = '800';

            $member_ID = I('get.id');
            $edit_member['phone'] = I('post.phone');
            $edit_member['position'] = I('post.state');
            $edit_member['salary'] = I('post.salary');
            $edit_member['on_job'] = I('post.status');
            
            $Member = D('Member');

            $Member->startTrans();// 启动事务
            
            if ($Member->where("member_ID = $member_ID")->create($edit_member, 2)) {
                echo "create成功<br/>";

                echo "***" . $result = $Member->scope('allowUpdateField')->save();

                if ($result) {
                    echo "更新成功<br/>";

                    $log_Arr = array($this->log_model, $this->log_data, $Member, self::ADMIN_MEMBER_EDIT, 'edit', array('成员id' => $member_ID), $edit_member);
                    //                     0                 1                2             3                4                            5         6
                    write_log_all_array($log_Arr);
                }else{

                    // echo "更新失败<br/>";
                    // echo $Member->getError();
                    $this->error($Member->getError());
                    return;
                }

            }else{

                // echo "create失败<br/>";
                // echo $Member->getError();
                $this->error($Member->getError());
                return;
            }
        }

        // echo "编辑工作人员，end<br/>";
        // die;
        
        // $this->display();
    }

    /**
     * 删除人员列表
     */
    public function del(){

        // echo "删除人员列表，begin<br/>";

        $member_ID = I('get.id');

        $Member = M('member');

        // 记录所删除的数据信息
        $del_info = get_delete_info($Member, array('成员id', $member_ID, '姓名', 'name'));

        echo "组合所得的del_info =: ".$del_info."  !!!!<br/>";

        if (is_array($member_ID)) {// 如果是数组，组合成以','为连接符的字符串
            
            $member_ID = implode(',', $member_ID);
        }

        $Member->startTrans();// 启动事务
        if ($Member->delete($member_ID)){
            
            // echo "删除成功<br/>";
            $log_Arr = array($this->log_model, $this->log_data, $Member, self::ADMIN_MEMBER_DELETE, 'delete', $del_info);
            //                     0                 1                2             3                    4           5
            write_log_all_array($log_Arr);

            $this->success('删除成功！', U('Admin/Staff/member'));
        }else{

            $this->error('删除工作人员失败！');
        }

        // echo "删除人员列表，end<br/>";
        // die;
        
        // $this->display();
    }
}