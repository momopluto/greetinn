<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 物品控制器
 */
class RentController extends HomeController{


	public function lists(){

		// p(I('get.'));
		// die;
		
		if (!$o_id = I('get.id')) {
			$this->error('订单id不能为空！');
			return;
		}

		$o_record_model = D('OrderRecordView');
        $whe['o_id'] = $o_id;
        $temp = $o_record_model->where($whe)->find();

        if (!$temp) {
        	$this->error('无效的订单id！');
			return;
        }

        if ($temp['status'] != 3) {/*非正在入住*/
        	$this->error('只有"正在入住"的顾客才可以租借物品！');
        	return;
        }
        $this->assign("o_id", $o_id);

        // p($temp);
        // die;

        $room['ID'] = $temp['room_ID'];
        $room['type'] = $temp['type_name'];
        $this->assign("room", $room);
        
        $client['name'] = $temp['name'];
        $client['phone'] = $temp['phone'];
        $client['ID_card'] = $temp['ID_card'];
        $this->assign("client", $client);
        // **************************************以上为房间信息+顾客信息

        // **************************************以下为可租借物品信息
		$model = M('rent_things');
        $map['pid'] = 0;
        //pid=0的类别信息数组$thing，按sort排序，其中的price为该类别下的物品数量
        $thing = $model->where($map)->order('pid, sort')->getField('thing_ID,pid,name,price,deposit,desc,sort');

        // 将子物品加入到物品类别
        foreach ($thing as $key => $value2) {
            $map['pid'] = $key;

            $sub_thing = $model->where($map)->order('sort')->getField('thing_ID,pid,name,price,deposit,desc,sort');//->select();
            $thing[$key]['sub_thing'] = $sub_thing;
        }

        // p($thing);die;

        $this->assign('data', $thing);

    	$this->display();
	}

	public function rent(){

		// p(I('post.'));
		// die;
		
		$post = I('post.');

		$thing = M('rent_things')->getField("thing_ID,pid,name,price,deposit,desc,sort");
		// p($thing);die;

		// 检验post过来的id，都有与之对应的thing_ID
		foreach ($post['id'] as $value) {

			if (!array_key_exists($value, $thing)) {

				$this->error("参数错误！");
				return;
			}
		}

		// 生成GUID
		$guid = strval(1800 + mt_rand(1, 5000)).mt_rand(1000, 9999).strval(NOW_TIME);//18位

		// echo strlen($guid);
		// die;

		// 组合(多条)物品购买记录
		$_i = 0;
		$deposit = 0;// 押金
		$total = 0;// 总共费用
		$now = getDatetime();
		for ($i=0; $i < count($post['id']); $i++) {
			if ($post['quantity'][$i] > 0) {

				// $data[$_i]['guid'] = $guid;
				$data[$_i]['o_id'] = $post['o_id'];

				$data[$_i]['thing_ID'] = $post['id'][$i];
				$data[$_i]['quantity'] = $post['quantity'][$i];
				$data[$_i]['note'] = $post['note'][$i];
				$data[$_i]['cTime'] = $now;

				$data[$_i]['total'] = $thing[$post['id'][$i]]['price'] * $post['quantity'][$i] + $thing[$post['id'][$i]]['deposit'];// 小计
				$deposit += $thing[$post['id'][$i]]['deposit'];// 押金
				$total += $data[$_i]['total'];// 总共费用
				$_i++;
			}
		}

		// p($data);
		// die;

		// 插入"物品出租记录表"
		$thing_model = M('r_record');
		$thing_model->startTrans();// 开启事务

		if ($thing_model->addAll($data)) {

			// 资金管理开启
			if (self::MONEY_MANAGEMENT_SWITCH) {
				
			}

			// 日志信息
			// 物品出租成功，物品guid: xxx，总价: xx
			
			// 写日志
			$log_Arr = array($this->log_model, $this->log_data, $thing_model, self::RECEPTIONIST_RENT_THING, 'rent_thing', array('关联订单id' => $o_id, '押金'=> $deposit, '物品费用'=>($total-$deposit), '总共' => $total));
			//                     0                 1                2             3                4                            5
			if (write_log_all_array($log_Arr)) {

				$this->success("物品出租成功！",U("Home/Rent/record"));
				return;
			}else {
				$this->error("物品出租失败！");
				return;
			}
		}else{

			$this->error("物品出租失败！");
			return;
		}
	}

	public function record(){

		$model = D('RentView');

		$temp = $model->order('cTime desc')->select();

		foreach ($temp as $key => $value) {

			$data[$value['o_id']][] = $value;
		}

		// p($data);
		// die;

		$this->assign('data', $data);
		$this->display();
	}
}