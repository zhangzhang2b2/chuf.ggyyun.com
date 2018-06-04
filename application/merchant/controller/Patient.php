<?php

namespace app\merchant\controller;

use think\Db;
use app\merchant\model\AuthRule  as  AuthRuleModel;
use app\merchant\model\AdminUserNew  as  AdminUserNewModel;
use think\Request;
use think\Validate;

class Patient extends Main
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
        $this->assign('item', ['item1'=>'病患信息','item2'=>'病患信息登记列表']);
        return  $this->fetch();
    }

    
    //编辑数据
    public function edit_patient($id)
    {
        
        if (Request::instance()->isPost()) {
            
            $post = $this->request->post();

             // 使用model助手函数实例化User模型
             $model = model('Patient');
             
             if($post['status']){
                $post['status']=$post['status']=='on'?1:0;
             }

             //更新
             $model->save($post,['id' => $post['id']]);
 
             $this->success('添加成功');
        
        } else {
            $data = model('Patient')->where('id', $id)->find();
            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    //删除
    public function delete_patient($id)
    {
        try { 
            model('Patient')->where('id', $id)->delete();
        } catch(Exception $e) {
            $this->error('系统繁忙，请稍后重试');
        }
        
        $this->success('删除成功');
    }


}
