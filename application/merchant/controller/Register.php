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
        if (Request::instance()->isPost()) {
            $post = $this->request->post();

            if ($post['name']=='') {
                $this->error('姓名不能为空');
            }

            if ($post['card_id']=='' && $post['phone_id']=='') {
                $this->error('卡号和绑定手机不能同时为空');
            }

            // 使用model助手函数实例化User模型
            $model = model('Patient');
            //已存在的相同手机或卡号无法添加
            $whereCard='%%';
            $wherePhone='%%';
            if ($post['card_id'] != '') {
                $whereCard=$post['card_id'];
            }
            if ($post['phone_id'] != '') {
                $wherePhone=$post['phone_id'];
            }
            $res =  $model->where('card_id',$whereCard)->whereOr('phone_id',$wherePhone)->find();
            if ($res) {
                $this->error('卡号和绑定手机已存在');
            }

            //状态按钮
            if($post['status']){
                $post['status']=$post['status']=='on'?1:0;
            }

            //账号
            $post['admin_id']=session('pid');//上级id
            $post['user_id']=session('uid');//当前用户id

            // 模型对象赋值
            $model->data($post);
            $model->save();

            $this->success('登记成功');
        } else {
            //标题传值
            $this->assign('item', ['item1'=>'登记','item2'=>'病患信息登记列表']);
            return  $this->fetch();
        }
    }

    /**
     * 登记记录
     */
    public function record()
    {
        //查找登记的人员
        $where=array();
        if ($this->request->get()) {    //如果是搜索进来的
            $get = $this->request->get();
            if (isset($get['allwhere'])) {
                if ($get['allwhere'] != '') {
                    $where['card_id|phone_id|name']=array('like','%'.$get['allwhere'].'%');
                }
            }
        }
        $list = model('Patient')->where($where)->order('id desc')->paginate("16", false, ['query'=>request()->param()]);
        $this->assign('list', $list);
        //标题传值
        $this->assign('item', ['item1'=>'记录','item2'=>'病患登记记录']);
        return  $this->fetch();
    }

    //编辑数据
    public function edit_record($id)
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

}
