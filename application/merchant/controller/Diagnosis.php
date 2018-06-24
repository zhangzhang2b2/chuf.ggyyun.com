<?php

namespace app\merchant\controller;

use think\Db;
use app\admin\model\AuthRule  as  AuthRuleModel;
use app\admin\model\AdminUserNew  as  AdminUserNewModel;
use think\Request;
use think\Validate;

use app\lib\event\PushEvent;    //引用文件

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
        /*获取病例信息数据*/
        $id=input('param.id');//就诊id'
        $this->assign('reserve_id', $id);
        //根据接诊id查询病例数据
        $data = model('Reserve')->with('patientInfo,recordsInfo,recipelNewInfo,otherpayInfo,checkInfo')->where('id','eq',$id)->find();
        $this->assign('data', $data);
             
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


    /**
     * 推送一个字符串
     */
    public function pushAString()
    {
        $string = 'Man Always dddddd';  //推送的消息内容
        // $string = input('msg') ? : $string;  //判断是否有输入消息
        $push = new PushEvent();    //实例化对象
        $push->setUser('123')->setContent($string)->push();   //setUser指定推送给的用户123，设置空则全部用户都推送。setContent推送的内容
        return json_code(200, '请求成功', 1);
    }

    /**
     * 保存诊断病例
     */
    public function cases()
    {
        $post = $this->request->post();

        $records = model('Records');
        try{
            //把病例更新
            $id=$post['id'];
            $records->where('id',$post['id'])->update(
                [
                    'onset' =>  $post['onset'],
                    'talk' =>  $post['talk'],
                    'diagnosis'  =>  $post['diagnosis'],
                    'now' =>  $post['now'],
                    'old' =>  $post['old'],
                    'allergy' =>  $post['allergy'],
                    'opinion' =>  $post['opinion'],
                    'remark' =>  $post['remark'],
                ]
            );
            return json_code(200, '请求成功', $id);
        }catch(\Exception $e){
            return json_code(0, '请稍候重试');
        }

    }


    /**
     * 历史病例
     */
    public function cases_history($id){
        //查找接诊结束的病历
        $data = model('Reserve')->with('patientInfo,recordsInfo')
            ->where('patient_id','eq',$id)
            ->where('status','eq',3)
            ->order('addtime desc')
            ->select();
        $this->assign('data',$data);
        return $this->fetch();
        
    }


}
