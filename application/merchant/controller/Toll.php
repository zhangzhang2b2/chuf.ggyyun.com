<?php

namespace app\merchant\controller;

use think\Db;
use app\admin\model\AuthRule  as  AuthRuleModel;
use app\admin\model\AdminUserNew  as  AdminUserNewModel;
use think\Request;
use think\Validate;

class Toll extends Main
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
     * 收费队列
     */
    public function index()
    {
        $where=array();
        //当前医院id
        if(session('pid')==0){ //上级id为0，则为
            $where['admin_id']=session('uid');
        }else {
            $where['user_id']=session('uid');
        }
        //接诊状态，1未接诊，2接诊中，3结束接诊
        $where['status']=1;

        //当前账号的接诊队列
        $list = model('Reserve')->with('patientInfo')
            ->where($where)
            ->order('id desc')
            ->paginate("16", false, ['query'=>request()->param()]);

        $this->assign('list', $list);
        
        //标题传值
        $this->assign('item', ['item1'=>'收费','item2'=>'收费队列']);
        return  $this->fetch();
    }


}
