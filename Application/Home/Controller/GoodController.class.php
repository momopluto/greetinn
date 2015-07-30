<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 商品控制器
 */
class GoodController extends HomeController{


	public function lists(){

		$model = M('good');

        $map['pid'] = 0;
        //pid=0的类别信息数组$good，按sort排序，其中的price为该类别下的商品数量
        $good = $model->where($map)->order('pid, sort')->getField('good_ID,pid,name,price,desc,sort');

        // 将子商品加入到商品类别
        foreach ($good as $key => $value2) {
            $map['pid'] = $key;

            $sub_good = $model->where($map)->order('sort')->getField('good_ID,pid,name,price,desc,sort');//->select();
            $good[$key]['sub_good'] = $sub_good;
        }

        // p($good);die;

        $this->assign('data', $good);

    	$this->display();
	}

	public function sell(){

		// p(I('post.'));
		// die;
		
		$post = I('post.');

		$good = M('good')->getField("good_ID,pid,name,price,desc,sort");
		// p($good);die;

		// 检验post过来的id，都有与之对应的good_ID
		foreach ($post['id'] as $value) {

			if (!array_key_exists($value, $good)) {

				$this->error("参数错误！");
				return;
			}
		}

		// 生成GUID
		$guid = strval(1800 + mt_rand(1, 5000)).mt_rand(1000, 9999).strval(NOW_TIME);//18位

		// echo strlen($guid);
		// die;

		// 组合(多条)商品购买记录
		$_i = 0;
		$total = 0;// 总价
		$now = getDatetime();
		for ($i=0; $i < count($post['id']); $i++) {
			if ($post['quantity'][$i] > 0) {

				$data[$_i]['guid'] = $guid;

				$data[$_i]['good_ID'] = $post['id'][$i];
				$data[$_i]['quantity'] = $post['quantity'][$i];
				$data[$_i]['note'] = $post['note'][$i];
				$data[$_i]['cTime'] = $now;

				$data[$_i]['total'] = $good[$post['id'][$i]]['price'] * $post['quantity'][$i];
				$total += $data[$_i]['total'];
				$_i++;
			}
		}

		// p($data);
		// die;

		// 插入"商品销售记录表"
		$good_model = M('g_record');
		$good_model->startTrans();// 开启事务

		if ($good_model->addAll($data)) {

			// 资金管理开启
			if (self::MONEY_MANAGEMENT_SWITCH) {
				
			}

			// 日志信息
			// 商品售卖成功，商品guid: xxx，总价: xx
			
			// 写日志
			$log_Arr = array($this->log_model, $this->log_data, $good_model, self::RECEPTIONIST_SELL_GOOD, 'sell_good', array('商品guid' => $guid, '总价' => $total));
			//                     0                 1                2             3                4                            5
			if (write_log_all_array($log_Arr)) {

				$this->success("商品售卖成功！",U("Home/Good/record"));
			}
		}else{

			$this->error("商品售卖失败！");
			return;
		}
	}

	public function record(){

		$model = D('GoodView');

		$map['guid'] = "626981371438224777";
		$temp = $model->order('cTime desc')->select();

		foreach ($temp as $key => $value) {

			$data[$value['guid']][] = $value;
		}

		// p($data);
		// die;

		$this->assign('data', $data);
		$this->display();
	}
}