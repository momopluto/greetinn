<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 商品管理
 * 
 */
class GoodController extends AdminController {

    // 商品列表
    function lists(){

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

        // var_dump($good[100]['price'] == 3);
        // p($good);die;

        $this->assign('data', $good);

    	$this->display();
    }

    // 新增商品
    function add_good(){
        // p(I('post.'));
        // die;
        //商品名必须
        //价格非负，可为0
        //序号不填，默认为0

        if(IS_POST){

            if(I('post.new_good') != '' && I('post.pid') != ''){//商品名不空 && pid不空

                $data['pid'] = I('post.pid');
                $data['name'] = I('post.new_good');
                if(I('post.price') == ''){
                    $data['price'] = 0;
                }elseif(I('post.price') >= 0){
                    $data['price'] = I('post.price');
                }else{
                    $this->error('价格为负？');
                    return;
                }

                $data['desc'] = I('post.description');

                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                // p($data);die;

                $model = M('good');
                $model->startTrans();

                if($model->add($data)){
                    $map['good_ID'] = $data['pid'];
                    // 所属类别的商品数+1
                    if ($model->where($map)->setInc('price') === false) {
                        $model->rollback();
                        $this->error('新增商品失败！');
                    }else{
                        $model->commit();
                        $this->success('新增商品成功！');
                    }
                }else{
                    $this->error($this->model->getError());
                }            
            }else{
                $this->error('商品名不能为空！');
            }
        }else{
            redirect(U('Admin/Good/lists'));
        }
    }    

    // 删除商品
    function del_good(){

        // p(I('get.'));
        // die;

        if(I('get.id') != '' && I('get.pid') != ''){

            $model = M('good');
            $map['good_ID'] = I('get.id');

            $model->startTrans();// 开启事务

            if(!$model->where($map)->delete()){// 返回0或者false都直接算删除失败

                $model->rollback();
                $this->error('删除商品失败！');
            }else{

                $map['good_ID'] = I('get.pid');
                // 所属类别的商品数-1
                if (!$model->where($map)->setDec('price')) {// 所属类别一定存在，所以返回0或者false都算失败
                    
                    $model->rollback();
                    $this->error('更新商品所属类别失败！');
                }else{

                    $model->commit();
                    $this->success('删除商品成功！');
                }
            }
        }else{
            redirect(U('Admin/Good/lists'));
        }
    }

    // 编辑商品
    function edit_good(){

        if(IS_POST){
            // p(I('post.'));
            // die;

            if (I('post.id') == '') {
                $this->error('商品ID不能为空！');
                return;
            }

            if(I('post.name') != ''){//商品名不空

                $data['name'] = I('post.name');
                if(I('post.price') == ''){
                    $data['price'] = 0;
                }elseif(I('post.price') >= 0){
                    $data['price'] = I('post.price');
                }else{
                    $this->error('价格为负？');
                }
                $data['desc'] = I('post.description');
                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                // p($data);die;

                $map['good_ID'] = I('post.id');
                $model = M('good');
                if($model->where($map)->save($data)){

                    redirect(U('Admin/Good/lists'));
                }else{
                    $this->error('更新商品信息失败！');
                }            
            }else{
                $this->error('商品名不能为空！');
            }
        }else{
            redirect(U('Admin/Good/lists'));
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
        // 商品名必须
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

                $model = M('good');
                if($model->add($data)){

                    $this->success('新增分类成功！');
                }else{
                    $this->error($model->getError());
                }            
            }else{
                $this->error('分类名不能为空！');
            }
        }else{
            redirect(U('Admin/Good/lists'));
        }
    }

    // 删除分类
    function del_cate(){

        if(I('get.id') != ''){
            $map1['good_ID'] = I('get.id');

            $model = M('good');
            $model->startTrans();// 开启事务
            if(!$model->where($map1)->delete()){// 返回值为0或者false都直接算删除失败
                
                $model->rollback();// 回滚事务
                $this->error('删除分类失败！');
            }else{

                $map2['pid'] = $map1['good_ID'];
                //删除该分类下的所有商品
                if ($model->where($map2)->delete() === false) {// 返回值为false才算失败，0说明该分类下没有商品

                    $model->rollback();// 回滚事务
                    $this->error('删除分类下所有商品失败！');
                }else {
                    $model->commit();// 提交事务
                    $this->success('删除分类成功！');
                }
            }
        }else{
            redirect(U('Admin/Good/lists'));
        }
    }

    // 编辑分类
    function edit_cate(){

        if(IS_POST){

            if(I('post.name') != '' && I('post.id') != ''){//类别名不空 && 商品id不为空

                $data['name'] = I('post.name');
                $data['desc'] = I('post.description');
                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                $map['good_ID'] = I('post.id');
                $model = M('good');
                if(!$model->where($map)->setField($data)){
                    
                    $this->error('更新分类信息失败');
                }else{

                    redirect(U('Admin/Good/lists'));
                }            
            }else{
                $this->error('类别名不能为空！');
            }
        }else{
            redirect(U('Admin/Good/lists'));
        }
    }
}

?>