<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 资金流量控制器
 */
class CapitalController extends HomeController{


	public function lists(){

		$capitalAdvModel = D("CapitalAdv");
		$data = $capitalAdvModel->join("capital_type ON capital_flow.type=capital_type.type")->order('cTime')->select();

		$types = M('capital_type')->getField('type, type_name');
		unset($types[0]);// 去除"交班"选项

		// p($types);die;
		// p($data);die;

        $this->assign('data', $data);
        $this->assign('types', $types);
    	$this->display();
	}

	/**
	 * 手动添加资金流量记录
	 */
	public function add(){

		if (IS_POST) {
			
			// p(I('post.'));die;

			$temp = I('post.');

			if ($temp['in'] == '') {
				$temp['in'] = 0;
			}

			if ($temp['out'] == '') {
				$temp['out'] = 0;
			}

			// 资金管理是否开启
			if (self::MONEY_MANAGEMENT_SWITCH) {
			    
			    // 资金流水表capital_flow数据
			    // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
			    $flow['shift'] = get_Oper('shift');// 班次标识
			    $flow['cTime'] = getDatetime();
			    $flow['in'] = $temp['in'];// 收入
			    $flow['out'] = $temp['out'];// 支出
			    $flow['type'] = $temp['type'];// 分类

			    $capitalAdvModel = D("CapitalAdv");
			    $last_record = $capitalAdvModel->where(array('shift'=>$flow['shift']))->last();

			    // p($last_record);die;

			    $flow['pay_mode'] = $temp['mode'];// 支付方式
			    if ($flow['pay_mode'] == 0) {

			        // 只计算现金的资金流
			        $flow['balance'] = $last_record['balance'] + $flow['in'] - $flow['out'];//余额        
			    }else{

			        $flow['balance'] = $last_record['balance'];
			    }       

			    $flow['info'] = "[手动]: ".$temp['info'];// 开头增加[手动]添加记录的标志
			    $flow['operator'] = get_Oper("name");// 经办人

			    // p($flow);die;

			    $capital_model = M('capital_flow');
			    if ($capital_model->add($flow) === false) {

			        $this->error('写-手动记录-资金流量表-失败！');
			        return;
			    }else{
			    	$this->success('写-手动记录-资金流量表-成功！',U('Home/Capital/lists'));
			        return;
			    }
			    // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
			}else {
				$this->error('资金流量管理-未开启！');
		        return;
			}
		}
	}

	/**
	 * 交班
	 */
	public function shift(){

		// p(I('post.'));
		// p(I('get.'));
		// die;

		if (IS_POST) {

			// 验证
			$rst = validate_login(I('post.'));// 验证前台登录
			if ($rst['errcode'] != 0) {

				$this->error($rst['errmsg']);
				return;
			}

			$prev_oper = I('get.prev_oper');// 交班者
			$shouldHave_balance = I('get.shouldHave_balance');// 应有余额(系统余额)
			$actual_balance = I('get.actual_balance');// 实际余额
			
			// 资金管理是否开启
			if (self::MONEY_MANAGEMENT_SWITCH) {
			    
			    // 资金流水表capital_flow数据
			    // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
			    $flow['cTime'] = getDatetime();
			    $flow['shift'] = strtotime($flow['cTime']);// 班次标识
			    $flow['in'] = 0;// 收入
			    $flow['out'] = 0;// 支出
			    $flow['type'] = 0;// 0交班

			    $flow['pay_mode'] = 0;// 支付方式，交班固定为现金
		        $flow['balance'] = $shouldHave_balance;// 保留，系统余额


		        $flow['info'] = "交班者: ".$prev_oper;
		        $flow['info'] .= "<br/>应有余额: ".number_format($shouldHave_balance, 1, '.', '').", 实际余额: ".number_format($actual_balance, 1, '.', '');
		        if (($balance = $actual_balance - $shouldHave_balance) < 0) {
			        $flow['info'] .= "<br/>[短款]: ";
		        }else{
		        	$flow['info'] .= "<br/>[长款]: +";
		        }
		        $flow['info'] .= number_format($balance, 1, '.', '');
			    $flow['operator'] = $rst['next_oper'];// 接班人

			    // p($flow);die;

			    $capital_model = M('capital_flow');
			    if ($capital_model->add($flow) === false) {

			        $this->error('写-交班-资金流量表-失败！');
			        return;
			    }else{

			    	$one = is_IDCard_exists(I('post.user'), 'member');

			    	$info['oper_ID'] = $one['member_ID'];
			    	$info['oper_CATE'] = 1;// 前台标志
			    	$info['oper_NAME'] = $one['name'];
			    	$info['shift'] = $flow['shift'];// 更新当前班次标志
			    	// 通过验证，合法，写session
			    	session('H_USER_V_INFO', $info);
			    	session('H_LOGIN_FLAG', true);

			    	$Home = A('home'); 
			    	$Home->login_log();// 调用Home/Controller/login_log方法

			    	$this->success('写-交班-资金流量表-成功！',U('Home/Capital/lists'));
			        return;
			    }
			    // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
			}else {
				$this->error('资金流量管理-未开启！');
		        return;
			}
		}

	}

	/**
	 * [AJAX]验证交班者
	 */
	public function checkShifter(){

		if (IS_AJAX) {

			$rst = validate_login(I('post.'));// 验证前台登录

			$this->ajaxReturn($rst, 'json');
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