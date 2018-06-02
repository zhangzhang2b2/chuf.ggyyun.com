<?php
namespace app\admin\controller;

use org\Auth;
use think\Controller;
use think\Db;
use think\Session;
use think\Loader;

class Settled extends Main
{
    //渲染页面
    public function index()
    {
        $where=array();
        if ($this->request->get()) {    //如果是搜索进来的
            $get = $this->request->get();
            if (isset($get['clinic'])&&isset($get['tel'])) {
                if ($get['clinic'] != '') {
                    $where['clinic']=array('like','%'.$get['clinic'].'%');
                }
                if ($get['tel'] != '') {
                    $where['tel']=array('like','%'.$get['tel'].'%');
                }
            }
        }

        $list = Db::name('settled')
            ->where($where)
            ->order('id desc')
            ->paginate("10", false, ['query'=>request()->param()]);
        $this->assign('list', $list);
        $this->assign('item', ['item1'=>'诊所','item2'=>'诊所列表']);
        
        return $this->fetch();
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
                array_push($rolearr,$roleid);
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
