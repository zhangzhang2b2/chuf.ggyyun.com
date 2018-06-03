<?php

namespace app\merchant\controller;

use think\Db;
use app\admin\model\AuthRule  as  AuthRuleModel;
use app\admin\model\AdminUserNew  as  AdminUserNewModel;
use think\Request;
use think\Validate;

class Register extends Main
{
    //定义
    protected $auth_rule_model;

    //初始化
    public function _initialize()
    {
        parent:: _initialize();
        $this->auth_rule_model  = new AuthRuleModel();
    }

    //登记列表界面
    public function index()
    {
        $list  = model('Patient')->order('id desc')->select();
        $this->assign('list', $list);
        //标题传值
        $this->assign('item', ['item1'=>'登记','item2'=>'病患信息登记列表']);
        return  $this->fetch();
    }

    //添加登记
    public function add_register()
    {
        if (Request::instance()->isPost()) {
            $post = $this->request->post();

            if ($post['card_id']=='' && $post['phone_id']=='') {
                $this->error('卡号和绑定手机不能同时为空');
            }

            // 使用model助手函数实例化User模型
            $model = model('Patient');
            //已存在的相同手机或卡号无法添加
            $res =  $model->where('card_id',$post['card_id'])->whereOr('phone_id',$post['phone_id'])->find();

            if ($res) {
                $this->error('卡号和绑定手机已存在');
            }

            if($post['status']){
                $post['status']=$post['status']=='on'?1:0;
            }

            // 模型对象赋值
            $model->data($post);
            $model->save();

            $this->success('添加成功');
        } else {
            //打开添加界面
            return $this->fetch();
        }
    }

    
    //编辑数据
    public function edit_register($id)
    {
        if (Request::instance()->isPost()) {
            $post = $this->request->post();

             // 使用model助手函数实例化User模型
             $model = model('Patient');
             
             if($post['status']){
                $post['status']=$post['status']=='on'?1:0;
             }
             // 模型对象赋值
             $model->save($post,['id' => $post['id']]);
 
             $this->success('添加成功');
        
        } else {
            $data = model('Patient')->where('id', $id)->find();
            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    //删除
    public function delete_register($id)
    {
        try { 
            model('Patient')->where('id', $id)->delete();
        } catch(Exception $e) {
            $this->error('系统繁忙，请稍后重试');
        }
        
        $this->success('删除成功');
    }
}
