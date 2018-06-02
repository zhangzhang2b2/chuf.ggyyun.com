<?php

namespace app\admin\controller;

use think\Db;
use app\admin\model\AdminUser as UserModel;
use app\admin\model\AuthRule  as  AuthRuleModel;
use think\Request;
use think\Validate;

class Drug extends Main
{
	function index(){
        $where=array();
        if ($this->request->get()) {    //如果是搜索进来的
			$get = $this->request->get();    
            if (isset($get['name'])&&isset($get['drug_type'])) {
                if ($get['name'] != '') {
                    $where['name']=array('like','%'.$get['name'].'%');
                }
                if ($get['drug_type'] != '') {
                    $where['drug_type']=$get['drug_type'];
                }
            }
        }
        $list = Db::name('drug')
            ->where($where)
            ->order('id desc')
            ->paginate("10", false, ['query'=>request()->param()]);

        $this->assign('list',$list);
        $this->assign('item',['item1'=>'药物管理','item2'=>'药物列表']);
        return  $this->fetch();
    }
    //删除
    function delete($id){
       Db::name('drug')->where('id',$id)->delete();
       $this->success('删除成功');
	}
	
	
	/*****************************导出药物******************************/
    //导出药物
    public function export(){

        $post=$this->request->post();
        $where=array();
        if ($post['name'] != '') {
            $where['name'] = array('like', '%' . $post['name'] . '%');
        }
        if ($post['drug_type'] != '') {
            $where['drug_type'] = $post['drug_type'];
        }


        //excel数据
        $alldetail = Db::name('drug')
            ->alias('d')
            ->where($where)
            ->join('settled s','d.settled_id = s.id','left')
            ->field('d.*,s.clinic')
            ->order('id desc')
        	->select();
        
        foreach ($alldetail as $key => $value) {
            switch ($alldetail[$key]['drug_type']) {
                case 0:
                    $alldetail[$key]['type'] = '中药';
                    break;
                case 1:
                    $alldetail[$key]['type'] = '西成药';
                    break;
                case 2:
                    $alldetail[$key]['type'] = '收费项目';
                    break;
            }
        }
        //excel表头
        $xlsCell  = array(
            array('id','编号'),
            array('clinic','诊所名称'),
            array('name','名称'),
            array('type','类型'),
            array('method','使用方法'),
            array('unit','单位 '),
            array('price','单价'),
            array('bucket','斗位'),
            array('pinyin','拼音'),
            array('spec','规格'),
            array('remarks','备注'),
            array('time','添加时间'),

        );

        //调用导出excel方法
        export_excel("药品清单",$xlsCell,$alldetail);
    }

    // 导入药物
    public function import(){
        echo 233;
    }
    public function savestudentImport(){
    //import('phpexcel.PHPExcel', EXTEND_PATH);//方法二  
    vendor("PHPExcel.PHPExcel"); //方法一  
    $objPHPExcel = new \PHPExcel();

    //获取表单上传文件  
    $file = request()->file('excel');
    $info = $file->validate(['size'=>15678,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public'. DS . 'excel');
    if($info){
        $exclePath = $info->getSaveName();  //获取文件名  
        $file_name = ROOT_PATH . 'public' . DS . 'excel' . DS . $exclePath;   //上传文件的地址 



        $objReader =\PHPExcel_IOFactory::createReader('Excel2007');
        $obj_PHPExcel =$objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码


        // utf-8  
            echo "<pre>";  
            $excel_array=$obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式  
            array_shift($excel_array);  //删除第一个数组(标题);  
            $data = [];  
            $i=0;  
            foreach($excel_array as $k=>$v) {
                $data[$k]['title'] = $v[0];
                $i++;
            }  
           $success=Db::name('drug')->insertAll($data); //批量插入数据  
           //$i=  
           $error=$i-$success;  
            echo "总{$i}条，成功{$success}条，失败{$error}条。";  
           // Db::name('t_station')->insertAll($city); //批量插入数据  
        }else{
        // 上传失败获取错误信息  
        echo $file->getError();
    }

}

}