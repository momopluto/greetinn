<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 资金分类管理
 * 
 */
class CapitalController extends AdminController {

    // 分类列表
    function lists(){

        $model = M('capital_type');

        $data = $model->getField('type, type_name');
        unset($data[0]);

        $this->assign('data', $data);
    	$this->display();
    }

    // 新增分类
    function add_type(){
        // p(I('post.'));
        // die;
        //分类名必须

        if(IS_POST){

            if(I('post.type') != '' && I('post.type_name') != ''){//分类编号不空 && 分类名不空

                $data['type'] = I('post.type');
                $data['type_name'] = I('post.type_name');

                // p($data);die;

                $model = M('capital_type');

                if($model->add($data)){

                    $this->success('新增分类成功！', U('Admin/Capital/lists'));
                }else{

                    $this->error($this->model->getError());
                }            
            }else{

                $this->error('分类编号及分类名不能为空！');
            }
        }else{

            redirect(U('Admin/Capital/lists'));
        }
    }    

    // 删除分类
    function del_type(){

        if(I('get.type') != ''){

            $model = M('capital_type');
            $map['type'] = I('get.type');


            if(!$model->where($map)->delete()){// 返回0或者false都直接算删除失败

                $this->error('删除分类失败！');
            }else{

                $this->success('删除分类成功！', U('Admin/Capital/lists'));
            }
        }else{

            redirect(U('Admin/Capital/lists'));
        }
    }

    // 编辑分类
    function edit_type(){

        if(IS_POST){

            if(I('post.type') != '' && I('post.type_name') != ''){//分类编号不空 && 分类名不空

                $data['type'] = I('post.type');
                $data['type_name'] = I('post.type_name');

                $model = M('capital_type');
                if($model->save($data)){
                    redirect(U('Admin/Capital/lists'));
                }else{

                    $this->error('更新分类名失败！');
                }
            }else{

                $this->error('分类名不能为空！');
            }
        }else{
            redirect(U('Admin/Capital/lists'));
        }
    }
}
?>