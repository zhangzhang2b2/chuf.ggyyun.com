<?php

namespace app\merchant\controller;

use think\Db;
use app\admin\model\AuthRule  as  AuthRuleModel;
use app\admin\model\AdminUserNew  as  AdminUserNewModel;
use think\Request;
use think\Validate;

class Fee extends Main
{
    //定义
    protected $auth_rule_model;

    //初始化
    public function _initialize()
    {
        parent:: _initialize();
        $this->auth_rule_model  = new AuthRuleModel();
    }


    /**
     * 其他收费首页
     */
    public function other()
    {
        //当前医院id
        $pid=session('pid');
        if($pid==0){ //上级id为0，则为
            $pid==session('uid');
        }
        $list  = model('Otherlist')->where('admin_id','eq',$pid)->order('id desc')->select();
        $this->assign('list', $list);
        //标题传值
        $this->assign('item', ['item1'=>'其他收费','item2'=>'其他收费列表']);
        return  $this->fetch();
    }

    /**新增收费项目 */
    public function add_otherlist(){
        if (Request::instance()->isPost()) {
            $post = $this->request->post();

            if ($post['name']==''||$post['unit']==''||$post['price']=='') {
                $this->error('请填写完整信息！');
            }

            // 使用model助手函数实例化User模型
            $model = model('Otherlist');
            //状态按钮
            $post['status']=isset($post['status'])?1:0;

            //账号
            $post['admin_id']=session('pid');//上级id
            $post['user_id']=session('uid');//当前用户id
            
            // 模型对象赋值
            $model->data($post);
            $model->save();

            $this->success('添加成功');
        } else {
            return  $this->fetch();
        }
    
    }

     /**
     * 编辑其他收费项目
     */
    public function edit_otherlist($id)
    {
        if (Request::instance()->isPost()) {
            $post = $this->request->post();

            if ($post['name']==''||$post['unit']==''||$post['price']=='') {
                $this->error('请填写完整信息！');
            }

            // 使用model助手函数实例化User模型
            $model = model('Otherlist');
            //状态按钮
            $post['status']=isset($post['status'])?1:0;

            //账号
            $post['admin_id']=session('pid');//上级id
            $post['user_id']=session('uid');//当前用户id

            //更新
            $model->save($post,['id' => $post['id']]);
            $this->success('编辑成功');

        } else {
            $data = model('Otherlist')->where('id', $id)->find();
            $this->assign('data', $data);
            return  $this->fetch();
        }
    }


    /**删除其他收费项目 */
    public function delete_otherlist($id)
    {
        try { 
            model('Otherlist')->where('id', $id)->delete();
        } catch(Exception $e) {
            $this->error('系统繁忙，请稍后重试');
        }
        $this->success('删除成功');
    }

}
