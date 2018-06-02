<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use think\Validate;

class Chart extends Main
{
    //药物统计
	function count(){
        $where=array();
        if ($this->request->get()) {    //如果是搜索进来的
			$get = $this->request->get();    
            if (isset($get['settled_id'])&&isset($get['drug_type'])) {
                if ($get['settled_id'] != '') {
                    $where['settled_id']=$get['settled_id'];
                }
                if ($get['drug_type'] != '') {
                    $where['drug_type']=$get['drug_type'];
                }
            }
        }
        $list = Db::name('drug')
            ->where($where)
            ->order('id desc')
            ->select();

        $drugnum=array();
        $drugnum[0]=0;//中药种类数量
        $drugnum[1]=0;//西成药种类数量
        $drugnum[2]=0;//收费项目数量
        foreach ($list as $k => $v) {
            if($v['drug_type']==0){ 
                $drugnum[0]=$drugnum[0]+1;
            }
            if($v['drug_type']==1){ 
                $drugnum[1]=$drugnum[1]+1;
            }
            if($v['drug_type']==2){ 
                $drugnum[2]=$drugnum[2]+1;
            }
        }

        $alldrug=$drugnum[0]+$drugnum[1];//中药和西药种类总数
        $this->assign('alldrug',$alldrug);

        $drugcount=implode(",", $drugnum);//每种药的种类数
        $this->assign('drugcount',$drugcount);
        $this->assign('item',['item1'=>'药物统计','item2'=>'统计药物']);
        return  $this->fetch();
    }

    //下拉诊所
    public function settlled(){
        $rt = Db::name('settled')
        ->where('type','eq',1)
        ->order('id desc')
        ->field('id,clinic')
        ->select();

        if ($rt) {
            return json_code(200, '请求成功', $rt);
        }
        return json_code(400, '系统繁忙');
    }


    //处方统计
    function cfcount(){
        $where=array();
        if ($this->request->get()) {    //如果是搜索进来的.
			$get = $this->request->get();    
            if (isset($get['settled_id'])&&isset($get['test10'])) {
                if ($get['settled_id'] != '') {
                    $where['settled_id']=$get['settled_id'];
                    $whereimg['settled_id']=$get['settled_id'];
                }
                if($get['test10'] != ''){ 
                    $to=date('Y-m-d H:i:s',strtotime(substr($get['test10'],0,19)));
                    $so=date('Y-m-d H:i:s',strtotime(substr($get['test10'],23,19))+86399);
                    $where['created_at']=array(array('gt',$to),array('lt',$so));
                }
            }
            
        }

        

        //总数量
        $zsl = Db::name('recipel')
            ->where($where)
            ->count();
        $this->assign('zsl', $zsl);

        //总药费
        $zyf = Db::name('recipel')
            ->where($where)
            ->sum('amount');
        $this->assign('zyf', $zyf);

        //总费用
        $zfy = Db::name('recipel')
            ->where($where)
            ->sum('total_amount');
        $this->assign('zfy', $zfy);

        //统计柱形图
        $alljfze=array();   //缴费

        $marray=['01','02','03','04','05','06','07','08','09','10','11','12'];  //12个月

        foreach ($marray as $k => $v) { 
            //时间段条件，每个月份的1号和月末
            $to=date('Y')."-".$v."-01 00:00:00";
            $so=date('Y-m-d H:i:s',strtotime("$to +1 month"));
            $whereimg['created_at']=array(array('egt',$to),array('lt',$so));

            //某个月已金额
            $jfze = Db::name('recipel')
            ->where($whereimg)
            ->sum('total_amount');
            $alljfze[$k]= $jfze;

        }

        $jf=implode(",", $alljfze);
        $this->assign('jf', $jf);

        $this->assign('item',['item1'=>'处方统计','item2'=>'统计处方']);
        return  $this->fetch();
    }



}