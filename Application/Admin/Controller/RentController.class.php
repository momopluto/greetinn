<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 物品管理
 * 
 */
class RentController extends AdminController {

    // 物品列表
    function lists(){

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

        // var_dump($thing[100]['price'] == 3);
        // p($thing);die;

        $this->assign('data', $thing);

    	$this->display();
    }

    // 新增物品
    function add_thing(){
        // p(I('post.'));
        // die;
        //物品名必须
        //价格非负，可为0
        //押金非负，可为0
        //序号不填，默认为0

        if(IS_POST){

            if(I('post.new_thing') != '' && I('post.pid') != ''){//物品名不空 && pid不空

                $data['pid'] = I('post.pid');
                $data['name'] = I('post.new_thing');
                if(I('post.price') == ''){
                    $data['price'] = 0;
                }elseif(I('post.price') >= 0){
                    $data['price'] = I('post.price');
                }else{
                    $this->error('价格为负？');
                    return;
                }

                if(I('post.deposit') == ''){
                    $data['deposit'] = 0;
                }elseif(I('post.deposit') >= 0){
                    $data['deposit'] = I('post.deposit');
                }else{
                    $this->error('押金为负？');
                    return;
                }

                $data['desc'] = I('post.description');

                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                // p($data);die;

                $model = M('rent_things');
                $model->startTrans();

                if($model->add($data)){
                    $map['thing_ID'] = $data['pid'];
                    // 所属类别的物品数+1
                    if ($model->where($map)->setInc('price') === false) {
                        $model->rollback();
                        $this->error('新增物品失败！');
                    }else{
                        $model->commit();
                        $this->success('新增物品成功！');
                    }
                }else{
                    $this->error($this->model->getError());
                }            
            }else{
                $this->error('物品名不能为空！');
            }
        }else{
            redirect(U('Admin/Rent/lists'));
        }
    }    

    // 删除物品
    function del_thing(){

        // p(I('get.'));
        // die;

        if(I('get.id') != '' && I('get.pid') != ''){

            $model = M('rent_things');
            $map['thing_ID'] = I('get.id');

            $model->startTrans();// 开启事务

            if(!$model->where($map)->delete()){// 返回0或者false都直接算删除失败

                $model->rollback();
                $this->error('删除物品失败！');
            }else{

                $map['thing_ID'] = I('get.pid');
                // 所属类别的物品数-1
                if (!$model->where($map)->setDec('price')) {// 所属类别一定存在，所以返回0或者false都算失败
                    
                    $model->rollback();
                    $this->error('更新物品所属类别失败！');
                }else{

                    $model->commit();
                    $this->success('删除物品成功！');
                }
            }
        }else{
            redirect(U('Admin/Rent/lists'));
        }
    }

    // 编辑物品
    function edit_thing(){

        if(IS_POST){
            // p(I('post.'));
            // die;

            if (I('post.id') == '') {
                $this->error('物品ID不能为空！');
                return;
            }

            if(I('post.name') != ''){//物品名不空

                $data['name'] = I('post.name');
                if(I('post.price') == ''){
                    $data['price'] = 0;
                }elseif(I('post.price') >= 0){
                    $data['price'] = I('post.price');
                }else{
                    $this->error('价格为负？');
                }
                if(I('post.deposit') == ''){
                    $data['deposit'] = 0;
                }elseif(I('post.deposit') >= 0){
                    $data['deposit'] = I('post.deposit');
                }else{
                    $this->error('押金为负？');
                }
                $data['desc'] = I('post.description');
                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                // p($data);die;

                $map['thing_ID'] = I('post.id');
                $model = M('rent_things');
                if($model->where($map)->save($data)){

                    redirect(U('Admin/Rent/lists'));
                }else{
                    $this->error('更新物品信息失败！');
                }            
            }else{
                $this->error('物品名不能为空！');
            }
        }else{
            redirect(U('Admin/Rent/lists'));
        }
    }

/*--------------------------------------------------------------*/

    // 新增分类
    function add_cate(){
/*
 Array
(
    [sort] => 排序号
    [new_cate] => 分类名
    [description] => 分类描述
)
*/
        // p(I('post.'));die;
        // 物品名必须
        // 序号，默认为0 
        // 分类描述，可不填

        if(IS_POST){

            $new_cate = I('post.new_cate');
            if($new_cate != ''){//类别名不空
                $data['pid'] = 0;
                $data['name'] = $new_cate;
                $data['price'] = 0;
                $data['desc'] = I('post.description');
                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                // p($data);
                // die;

                $model = M('rent_things');
                if($model->add($data)){

                    $this->success('新增分类成功！');
                }else{
                    $this->error($model->getError());
                }            
            }else{
                $this->error('分类名不能为空！');
            }
        }else{
            redirect(U('Admin/Rent/lists'));
        }
    }

    // 删除分类
    function del_cate(){

        if(I('get.id') != ''){
            $map1['thing_ID'] = I('get.id');

            $model = M('rent_things');
            $model->startTrans();// 开启事务
            if(!$model->where($map1)->delete()){// 返回值为0或者false都直接算删除失败
                
                $model->rollback();// 回滚事务
                $this->error('删除分类失败！');
            }else{

                $map2['pid'] = $map1['thing_ID'];
                //删除该分类下的所有物品
                if ($model->where($map2)->delete() === false) {// 返回值为false才算失败，0说明该分类下没有物品

                    $model->rollback();// 回滚事务
                    $this->error('删除分类下所有物品失败！');
                }else {
                    $model->commit();// 提交事务
                    $this->success('删除分类成功！');
                }
            }
        }else{
            redirect(U('Admin/Rent/lists'));
        }
    }

    // 编辑分类
    function edit_cate(){

        if(IS_POST){

            if(I('post.name') != '' && I('post.id') != ''){//类别名不空 && 物品id不为空

                $data['name'] = I('post.name');
                $data['desc'] = I('post.description');
                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                $map['thing_ID'] = I('post.id');
                $model = M('rent_things');
                if(!$model->where($map)->setField($data)){
                    
                    $this->error('更新分类信息失败');
                }else{

                    redirect(U('Admin/Rent/lists'));
                }            
            }else{
                $this->error('类别名不能为空！');
            }
        }else{
            redirect(U('Admin/Rent/lists'));
        }
    }
}

?>