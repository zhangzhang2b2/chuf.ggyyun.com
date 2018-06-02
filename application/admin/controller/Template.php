<?php
namespace app\admin\controller;

use org\Auth;
use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Loader;

class Template extends Main {

    //模板管理首页
    public function index () {
        $where=array();
        if ($this->request->get()) {    //如果是搜索进来的
            $get = $this->request->get();
            if (isset($get['clinic'])&&isset($get['diagnosis'])) {
                if($get['clinic'] != ''){
                    $where['s.clinic']=array('like','%'.$get['clinic'].'%');
                }
                if($get['diagnosis'] != ''){
                    $where['a.diagnosis']=array('like','%'.$get['diagnosis'].'%');
                }
            }      

        }
        
        /**
         * 连表查询，使用paginate分页后不能用foreach拼接数据$list[$k]['xx'],要在查询数据时使用each
         */
        $list = Db::name('template')
        ->alias('a')
        ->join('settled s','a.settled_id = s.id')
        ->where($where)
        ->field('a.id,a.settled_id,a.category,a.time,a.diagnosis,a.drug,a.amount,s.clinic')
        ->paginate("10", false, ['query'=>request()->param()])
        ->each(function($item, $key){
            $drugarr = json_decode($item['drug']);
            $drugstr=implode(',',$drugarr);
            $item['alldrug'] = $drugstr;
            return $item;
        });
        
        $this->assign('item', ['item1'=>'处方模版','item2'=>'模版列表']);//传递标题文字
        $this->assign('list',$list);//传递列表数据
        
        // dump($list['drug']);
        return  $this->fetch();
    }

}

?>



