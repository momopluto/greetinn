<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 人员管理控制器
 */
class StaffController extends AdminController {

    // 职位
    const POSITION_RECEPTIONIST     =   1;      //  前台
    const POSITION_SUPERVISOR       =   5;      //  主管
    const POSITION_ADMINISTRATOR    =   9;      //  管理员

	/**
	 * 客户列表
	 */
    public function client(){
        
        $this->display();
    }
    
    /**
     * 工作人员列表
     */
    public function member(){
        
        $this->display();
    }

    /**
     * 添加工作人员
     */
    public function add(){

        echo "添加工作人员，begin<br/>";

        $new_member['name'] = '刘恩坚';
        $new_member['ID_card'] = '441423199305165018';
        $new_member['gender'] = '1';
        $new_member['phone'] = '18826481053';
        $new_member['position'] = self::POSITION_ADMINISTRATOR;
        $new_member['salary'] = '800';

        $Member = D('Member');
        
        if ($Member->create($new_member)) {
            echo "create成功<br/>";

            echo $Member->position."<br/>";
            echo $Member->birthday."<br/>";
            echo $Member->on_job."<br/>";
            echo $Member->cTime."<br/>";

            $Member->add();
        }else{

            echo "create失败<br/>";
            echo $Member->getError();
        }

        echo "添加工作人员，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 编辑工作人员
     */
    public function edit(){

        echo "编辑工作人员，begin<br/>";

        $member_ID = 1;// 模拟操作的成员id

        // $new_member['name'] = '刘恩坚';
        // $new_member['ID_card'] = '441423199305165018';
        // $new_member['gender'] = '1';
        $new_member['phone'] = '18826481053';
        $new_member['position'] = self::POSITION_ADMINISTRATOR;
        $new_member['salary'] = '800';

        $Member = D('Member');
        
        if ($Member->where("member_ID = $member_ID")->create($new_member, 2)) {
            echo "create成功<br/>";

            echo $Member->position."<br/>";
            echo $Member->birthday."<br/>";
            echo $Member->on_job."<br/>";
            echo $Member->cTime."<br/>";

            echo "***" . $result = $Member->scope('allowUpdateField')->where("member_ID = $member_ID")->save();

            if ($result) {
                echo "更新成功<br/>";
            }else{

                echo "更新失败<br/>";
                echo $Member->getError();
            }

            $Member->add();
        }else{

            echo "create失败<br/>";
            echo $Member->getError();
        }

        echo "编辑工作人员，end<br/>";
        die;
        
        $this->display();
    }

    /**
     * 删除人员列表
     */
    public function del(){

        echo "删除人员列表，begin<br/>";

        $member_ID = 1;// 模拟操作的成员id

        if (is_array($member_ID)) {// 如果是数组，组合成以','为连接符的字符串
            
            $member_ID = implode(',', $member_ID);
        }
        
        $Member = M('member');

        if ($Member->delete($member_ID)){
            
            echo "删除成功<br/>";
        }

        echo "删除人员列表，end<br/>";
        die;
        
        $this->display();
    }
}