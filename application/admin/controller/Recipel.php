<?php
namespace app\admin\controller;

use org\Auth;
use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Loader;

class Recipel extends Main {

    public function index () {
        $where=array();
        if ($this->request->get()) {    //如果是搜索进来的
            $get = $this->request->get();
                if($get['clinic'] != ''){
                    $where['s.clinic']=array('like','%'.$get['clinic'].'%');
                }
                if($get['name'] != ''){
                    $where['a.name']=array('like','%'.$get['name'].'%');
             }
        }

        $list = Db::name('recipel')
        ->alias('a')
        ->join('settled s','a.settled_id = s.id')
        ->where($where)
        ->field('a.*,s.clinic')
        ->paginate("10", false, ['query'=>request()->param()])
        ->each(function($item, $key){
            // $drugarr = json_decode($item['drug']);
            // $aa=implode(',',$drugarr);
            // $item['alldrug'] = $aa;
            // return $item;
        });        
        $this->assign('item', ['item1'=>'处方管理','item2'=>'处方列表']);
        $this->assign('list',$list);

        return  $this->fetch();
    }

    /*****************************导出处方******************************/
    //导出处方
    public function export(){

        $post=$this->request->post();
        $where=array();
        if ($post['clinic'] != '') {
            $where['clinic'] = array('like', '%' . $post['clinic'] . '%');
        }
        if ($post['name'] != '') {
            $where['r.name'] = array('like', '%' . $post['name'] . '%');
        }

        //excel数据 
        $alldetail = Db::name('recipel')
            ->alias('r')
            ->where($where)
            ->join('settled s','r.settled_id = s.id','left')
            ->field('r.*,s.clinic')
            ->order('id desc')
            ->select();

         foreach ($alldetail as $key => $value) {
            $arr = json_decode($alldetail[$key]['drug']);
            $alldetail[$key]['drugall'] = implode(',',$arr);
            
         } 
        //excel表头
        $xlsCell  = array(
            array('id','编号'),
            array('clinic','诊所名称'),
            array('name','姓名'),
            array('sex','性别'),
            array('age','年龄'),
            array('category','科别 '),
            array('address','住址电话'),
            array('diagnosis','诊断'),
            array('allergy','过敏史'),
            array('drugall','药物'),
            array('doctor','医生'),
            array('amount','药费'),
            array('total_amount','总计'),
            array('time','日期'),
            array('remarks','备注'),
        );

        //调用导出excel方法
        export_excel("处方表",$xlsCell,$alldetail);
    }

}

?>
