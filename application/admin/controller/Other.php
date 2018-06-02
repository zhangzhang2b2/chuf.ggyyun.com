<?php
namespace app\admin\controller;

use org\Auth;
use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Loader;

class Other extends Main
{

    public function _initialize()  //做Controller的初始化
    {
        parent:: _initialize();//parent::_initialize() 是调用父类的_initialize方法，如果你的父类_initialize函数没有任何内容，不需要写parent::_initialize()
        $this->other_model  = Db::name('other');   //杂项表，轮播图
    }

    //渲染页面
    public function index()
    {
        if ($this->request->get()) {    //如果是搜索进来的
            $get = $this->request->get();
            
        }
        //查询全部列表内容
        $list = $this->other_model->paginate("10", false, ['query'=>request()->param()]);//->paginate("10",false,['query'=>request()->param()])这段是分页的作用

        $this->assign('list', $list);
        $this->assign('item', ['item1'=>'轮播图','item2'=>'轮播图列表']);
        
        return $this->fetch();
    }


    //新增
    public function add_other()
    {
        if (Request::instance()->isPost()) {
            $post = $this->request->post();
            //提交
            try {
                //插入数据库
                $res =  $this->other_model->insert($post);
            } catch (\Exception $e) {
                $this->error('系统繁忙');
            }
            $this->success('success');
        } else {
            //显示界面
            $other  = $this->other_model->select();

            $this->assign('other', $other);
            return $this->fetch();
        }
    }

    //编辑
    public function edit_other($id)
    {
        if(Request::instance()->isPost()){
            $post = $this->request->post();
            //提交
            //插入数据库
            $res =  $this->other_model->where('id',$post['id'])->update(['files'=>$post['files']]);
            $this->success('success');

        }else{
            //显示界面
            $data  = $this->other_model->where('id',$id)->find();
            $li = explode(',', $data['files']);
			$file_id = array();
			foreach ($li as $k => $v) {
				$file_id[] = Db::name('file')->field('url')->find($v);
				$spath = array();
				foreach ($file_id as $ks => $vs) {
					$spath[] = $vs['url'];
				}
            }
			$imgurl = implode(',', $spath);
            $data['imgurl'] = $imgurl;
            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    
    

    //查看诊所资料
    public function view_clinic($id)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();  //获取post提交过来的数据
            $data['type']=$post['type'];
            $data['remarks']=$post['remarks'];
            $data['id']=$id;

            try {
                //更新诊所入驻审核状态
                $return=Db::name('settled')->update($data);
                //查询所有模块id
                $modids=Db::name('mod')->where('status', 1)->column('id');
                $allmod = json_encode($modids);
                //插入角色表，角色类型为1管理者，获取id
                $roleid = Db::name('role')->insertGetId(['roletype'=>1,'settled_id'=>$id,'mod_id'=>$allmod]);
                //插入用户权限表
                $rolearr=array();
                array_push($rolearr, $roleid);
                $rolestr =json_encode($rolearr);
                $res =  Db::name('userAuth')->insert(['user_id'=>$post['user_id'],'role_id'=>$rolestr]);
            } catch (\Exception $e) {
                $this->error('系统繁忙');
            }
            $this->success('审核成功');
        } else {
            $data=Db::name('settled')->find($id);
            $data['card_img']=Db::name('file')->where('id', 'in', $data['card_img'])->select();
            $data['atlas']=Db::name('file')->where('id', 'in', $data['atlas'])->select();
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //删除诊所
    public function delete_clinic($id)
    {
        Db::name('settled')
         ->where('id', $id)
         ->delete();
        $this->success('删除成功');
    }
}
