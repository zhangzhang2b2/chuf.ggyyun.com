<?php

namespace app\merchant\controller;

use think\Db;
use app\admin\model\AuthRule  as  AuthRuleModel;
use app\admin\model\AdminUserNew  as  AdminUserNewModel;
use think\Request;
use think\Validate;

class Diagnosis extends Main
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
     * 接诊队列
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
        $this->assign('item', ['item1'=>'接诊','item2'=>'接诊队列']);
        return  $this->fetch();
    }

    /**
     * 正在接诊
     */
    public function treat()
    {
        $id=input('param.id');
        $patientData=model('Patient')->where('id','eq',$id)->find();
        $this->assign('patientData', $patientData);
        //标题传值
        $this->assign('item', ['item1'=>'接诊','item2'=>'正在接诊']);
        return  $this->fetch();
    }

    /**
     * 接诊记录
     */
    public function record()
    {
        //查找登记的人员
        $where=array();
        if ($this->request->get()) {    //如果是搜索进来的
            $get = $this->request->get();
            if (isset($get['allwhere'])) {
                if ($get['allwhere'] != '') {
                    $where['b.name']=array('like','%'.$get['allwhere'].'%');
                }
            }
        }
        //当前医院id
        if(session('pid')==0){ //上级id为0，则为
            $where['a.admin_id']=session('uid');
        }else {
            $where['a.user_id']=session('uid');
        }
        //接诊状态，1未接诊，2接诊中，3结束接诊
        $where['a.status']=3;

       //当前账号的接诊队列
       $list = model('Reserve')
            ->alias('a')->join('patient b','b.id = a.patient_id')
            ->where($where)
            ->order('a.id desc')
            ->paginate("16", false, ['query'=>request()->param()]);
        
        $this->assign('list', $list);
        //标题传值
        $this->assign('item', ['item1'=>'记录','item2'=>'接诊记录']);
        return  $this->fetch();
    }


}
