<?php

namespace app\merchant\controller;

use think\Db;
use app\admin\model\AdminUserNew  as  AdminUserNewModel;
use think\Request;
use think\Validate;

class Drug extends Main
{

    /**
     * 药品列表界面
     */
    public function index()
    {
        //所有药品列表
        $list  = model('DrugNew')->order('id desc')->select();
        $this->assign('list', $list);
        //标题传值
        $this->assign('item', ['item1'=>'药品','item2'=>'药品列表']);
        return  $this->fetch(); 
    }


    /**
     * 新增药品界面
     */
    public function add_drug()
    {
        if (Request::instance()->isPost()) {
            $post = $this->request->post();

            if ($post['name']==''&&$post['unit']==''&&$post['price']==''&&$post['stock']=='') {
                $this->error('请填写完整信息！');
            }

            // 使用model助手函数实例化User模型
            $model = model('DrugNew');
            //状态按钮
            $post['status']=isset($post['status'])?1:0;

            //账号
            $post['admin_id']=session('pid');//上级id
            $post['user_id']=session('uid');//当前用户id
            

            // 模型对象赋值
            $model->data($post);
            $model->save();

            $this->success('登记成功');
        } else {
            return  $this->fetch();
        }
    }

    /**
     * 编辑药品界面
     */
    public function edit_drug($id)
    {
        if (Request::instance()->isPost()) {
            $post = $this->request->post();

            if ($post['name']==''&&$post['unit']==''&&$post['price']==''&&$post['stock']=='') {
                $this->error('请填写完整信息！');
            }

            // 使用model助手函数实例化User模型
            $model = model('DrugNew');
            //状态按钮
            $post['status']=isset($post['status'])?1:0;

            //账号
            $post['admin_id']=session('pid');//上级id
            $post['user_id']=session('uid');//当前用户id

            //更新
            $model->save($post,['id' => $post['id']]);
            $this->success('登记成功');

        } else {
            $data = model('DrugNew')->where('id', $id)->find();
            $this->assign('data', $data);
            return  $this->fetch();
        }
    }

    //删除
    public function delete_drug($id)
    {
        try { 
            model('DrugNew')->where('id', $id)->delete();
        } catch(Exception $e) {
            $this->error('系统繁忙，请稍后重试');
        }
        $this->success('删除成功');
    }


    //上传文件
    public function do_upload(){

        //引入文件
        \think\Loader::import('PHPExcel.Classes.PHPExcel');
        $objPHPExcel = new \PHPExcel();

        //获取表单上传文件
        $file = request()->file('file');
        $info = $file->validate(['ext' => 'xlsx,xls'])->move(ROOT_PATH . 'public' . DS . 'uploads');

        //数据为空返回错误
        if(empty($info)){
            $output['status'] = false;
            $output['info'] = '导入数据失败~';
            $this->ajaxReturn($output);
        }

        //获取文件名
        $exclePath = $info->getSaveName();
        //上传文件的地址
        $filename = ROOT_PATH . 'public' . DS . 'uploads'.DS . $exclePath;

        $extension = strtolower( pathinfo($filename, PATHINFO_EXTENSION) );

        \think\Loader::import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        if ($extension =='xlsx') {
            $objReader = new \PHPExcel_Reader_Excel2007();
        } else if ($extension =='xls') {
            $objReader = new \PHPExcel_Reader_Excel5();
        }
        $objExcel = $objReader ->load($filename,$encode = 'utf-8');

        $excel_array=$objExcel->getsheet(0)->toArray();   //转换为数组格式
        array_shift($excel_array);  //删除第一个数组(标题);

        $data=[];
        foreach ($excel_array as $k=>$v){
            $drug_type='';
            if($v[0]=='西药'){
                $drug_type=0;
            }elseif ($v[0]=='中药') {
                $drug_type=1;
            }
            $data[$k]["type"]=$drug_type;//类型
            $data[$k]["name"]=$v[1];//名称
            $data[$k]["factory"]=$v[2];//生产厂家
            $data[$k]["word"]=$v[3];//国药准字
            $data[$k]["pinyin"]=$v[4];//拼音
            $data[$k]["formul"]=$v[5];//剂型
            $data[$k]["unit"]=$v[6];//单位
            $data[$k]["price"]=$v[7];//单价
            $data[$k]["spec"]=$v[8];//规格
            $data[$k]["stock"]=$v[9];//库存
            $data[$k]["remark"]=$v[10];//备注
        }


        /**获取数据data集合，存入数据库自己写 */
        if(model('DrugNew')->saveAll($data)) 
        {
            $msg=[
                'code'=>1,
                'msg'=>'导入成功',
            ];
            $msg['data']['name']=$exclePath;//文件名
            $msg['data']['src']=$filename;//路径
            $msg['data']['data']=$data;//数据
            return json_encode($msg);
        }else{
            $msg=[
                'code'=>0,
                'msg'=>'导入失败请重试',
            ];
            return json_encode($msg);
        }   

    }





}
