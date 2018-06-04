<?php

namespace app\merchant\controller;

use think\Db;
use app\admin\model\AdminUserNew  as  AdminUserNewModel;
use think\Request;
use think\Validate;

class Drug extends Main
{

    /**
     * 药品列表界面
     */
    public function index()
    {
        //当前账号的药品列表
        $list  = model('DrugNew')->where('user_id',session('uid'))->order('id desc')->select();
        $this->assign('list', $list);
        //标题传值
        $this->assign('item', ['item1'=>'药品','item2'=>'药品列表']);
        return  $this->fetch(); 
    }


    /**
     * 新增药品界面
     */
    public function add_drug()
    {
        if (Request::instance()->isPost()) {
            $post = $this->request->post();

            if ($post['name']==''&&$post['unit']==''&&$post['price']==''&&$post['stock']=='') {
                $this->error('请填写完整信息！');
            }

            // 使用model助手函数实例化User模型
            $model = model('DrugNew');
            //状态按钮
            $post['status']=isset($post['status'])?1:0;

            //账号
            $post['admin_id']=session('pid');//上级id
            $post['user_id']=session('uid');//当前用户id
            

            // 模型对象赋值
            $model->data($post);
            $model->save();

            $this->success('登记成功');
        } else {
            return  $this->fetch();
        }
    }

    /**
     * 编辑药品界面
     */
    public function edit_drug($id)
    {
        if (Request::instance()->isPost()) {
            $post = $this->request->post();

            if ($post['name']==''&&$post['unit']==''&&$post['price']==''&&$post['stock']=='') {
                $this->error('请填写完整信息！');
            }

            // 使用model助手函数实例化User模型
            $model = model('DrugNew');
            //状态按钮
            $post['status']=isset($post['status'])?1:0;

            //账号
            $post['admin_id']=session('pid');//上级id
            $post['user_id']=session('uid');//当前用户id

            //更新
            $model->save($post,['id' => $post['id']]);
            $this->success('登记成功');

        } else {
            $data = model('DrugNew')->where('id', $id)->find();
            $this->assign('data', $data);
            return  $this->fetch();
        }
    }

    //删除
    public function delete_drug($id)
    {
        try { 
            model('DrugNew')->where('id', $id)->delete();
        } catch(Exception $e) {
            $this->error('系统繁忙，请稍后重试');
        }
        $this->success('删除成功');
    }


}
