<?php

namespace app\admin\controller;

use think\Db;
use app\admin\model\AdminUser as UserModel;
use app\admin\model\AuthRule  as  AuthRuleModel;
use think\Request;
use think\Validate;

class User extends Main
{
	function index(){
		$list  = Db::name('user')->paginate(20);

		$this->assign('list',$list);
		$this->assign('item',['item1'=>'用户','item2'=>'内容列表']);
		return  $this->fetch();
	}

    //用户详情
    function edit_user($id){
            $list  = Db::name('user')->find($id);
            $this->assign('list',$list);
            return $this->fetch();
        }
    

    //删除用户
    function delete_user($id){
       Db::name('content')->where('id',$id)->delete();
       $this->success('删除成功');
    }



    //图片上传
    public function upload(){
        $file = request()->file('file');
        $url_l = './static/uploads';
        $url='uploads';
        $info = $file->move($url_l,true);
        if($info){
            $url=$url.'/'.$info->getSaveName();
        }
        return json_encode($url);
    
    }
    //重复上传删除图片
    public function del_img(){
        $del_url=input('post.img');
        @unlink('static/'.$del_url);
        return json_encode(1);
    }


    //*******************zly**************************/
    //小程序用户权限角色
    public function user_auth(){
        $where=array();
        if ($this->request->get()) {    //如果是搜索进来的
            $get = $this->request->get();
            if (isset($get['name'])&&isset($get['tel'])) {
                if($get['name'] != ''){
                    $where['a.name']=array('like','%'.$get['name'].'%');
                }
                if($get['tel'] != ''){
                    $where['u.tel']=array('like','%'.$get['tel'].'%');
                }
            }
        }
        /**
         * 连表查询，使用paginate分页后不能用foreach拼接数据$list[$k]['xx'],要在查询数据时使用each
         */
        $list = Db::name('user_auth')
        ->alias('a')
        ->join('user u','a.user_id = u.id')
        ->where($where)
        ->field('a.*,u.tel,u.username')
        ->paginate("10", false, ['query'=>request()->param()])
        ->each(function($item, $key){
            $wherole = json_decode($item['role_id']);
            $rtlist = Db::name('role')->alias('r')
                ->join('settled s','r.settled_id = s.id')
                ->where('r.id','in',$wherole)
                ->field('r.roletype,s.clinic')
                ->select();
            $item['rtlist'] = $rtlist;
            return $item;
        });
        
        $this->assign('item', ['item1'=>'用户角色','item2'=>'用户角色列表']);//传递标题文字
        $this->assign('list',$list);//传递列表数据
        
        // dump($list['drug']);
        return $this->fetch();
    }

}