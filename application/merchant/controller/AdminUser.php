<?php

namespace app\merchant\controller;

use think\Db;
use app\merchant\model\AdminUser as UserNewModel;
use app\merchant\model\AuthRule  as  AuthRuleNewModel;
use think\Request;
use think\Validate;

class AdminUser extends Main
{
    protected $auth_rule_model;

    function _initialize()
    {
        parent:: _initialize();
        $this->auth_rule_model  = new AuthRuleNewModel();
    }

	function index(){

		$list  = Db::name('admin_user_new')->where('pid',session('uid'))->order('id desc')->select();
        $role  = Db::name('auth_group_new')->field('id,title')->where('status',1)->select();
        $this->assign('role',$role);
		$this->assign('list',$list);
		//标题传值
		$this->assign('item',['item1'=>'用户','item2'=>'用户列表']);
		return  $this->fetch();
	}


	//新增用户
    public function add_user()
    {
        if(Request::instance()->isPost()){
            $post     = $this->request->post();
            $group_id = $post['group_id'];
            $validate = validate('User');
            
            $res      = $validate->check($post);
            
            if ($res !== true) {
                $this->error($validate->getError());
            } else {
                $post['password'] = md5($post['password']);
                $post['last_login_ip'] = '0.0.0.0';
                $post['pid'] = session('uid');//上级id
                // $post['create_time']   = date('Y-m-d h:i:s', time());
                $user =  new UserNewModel();
                $user->data($post);
                $user->allowField(true)->save();
                $userId = $user->getLastInsID();

                $location_arr = getCity($this->request->ip());
                $location = $location_arr['country'].'*'.$location_arr['area'].'*'.$location_arr['region'].'*'.$location_arr['city'];
                Db::name('auth_group_access_new')
                    ->insert(['uid'=>$userId,'group_id'=>$group_id]);
                $param = ['name'=>'增加用户'.$post['username'],'way'=>'AdminUser/addUser','username'=>session('username'),'create_time'=>date('Y-m-d H:i:s',time()),'ip'=>$this->request->ip(),'location'=>$location];
                \think\Hook::listen('log',$param);
                $this->success('success');
            }
        }else{
            $role  = Db::name('auth_group_new')->field('id,title')->where('status',1)->select();
            $this->assign('role',$role);
            return $this->fetch();
        }
    }

    //得到修改数据
    function get_edit_data($id){
    	$data = Db::name('admin_user_new')
    		->alias('a')
    		->join('auth_group_access_new b','b.uid=a.id','left')
    		->where('a.id',$id)
    		->field('a.*,b.group_id')
    		->find();
    	return $data;
    }

    //编辑数据
    function edit_user($id){
        
        if(Request::instance()->isPost()){
            
            if($id==1){
                $this->error('超级用户无法操作');
            }
            // if((int)$id!==session('uid')){
            //     $this->error('当前数据保护');
            // }
        	$post = $this->request->post();
            $userModel = new UserNewModel;
            $res =  $this->validate($post,'User.edit');
            if($res!==true){
                    $this->error($res);
            }else{
                unset($post['password']);
                $userModel->allowField(true)->save($post,['id' => $post['id']]);
                Db::name('auth_group_access_new')
                ->where('uid',$post['id'])
                ->update([
                    'group_id'=>$post['group_id']
                ]);
                $this->success('修改成功');
            }
        }else{
            $data =  $this->get_edit_data($id);
            $role  = Db::name('auth_group_new')->field('id,title')->where('status',1)->select();
            $this->assign('role',$role);
            $this->assign('data',$data);
            return $this->fetch();
        }
    }

    //删除用户
    function delete_user($id){
        //无法删除自己
        if((int)$id==session('uid')){
            $this->error('当前登录用户不能删除');
        }

    	if($id!=='1'){
            Db::name('admin_user_new')
                ->where('id',$id)
                ->delete();
            $this->success('删除成功');
        }else{
            $this->error('超级用户无法删除');
        }
    }

    //权限列表渲染
    function AuthList(){
        $data = Db::name('auth_rule_new')
            ->order(['sort' => 'DESC', 'id' => 'ASC'])
            ->select();
        $list = array2Level($data);
        //标题传值
        return $this->fetch('authList',[
            'item'=>['item1'=>'用户','item2'=>'权限列表'],
            'list'=>$list
        ]);
    }

    //渲染增加后台菜单页面
    function show_add_menu(){
        $top_menu = Db::name('auth_rule_new')
            ->order(['sort' => 'DESC', 'id' => 'ASC'])
            ->select();
        $top_menu = array2Level($top_menu);
        return $this->fetch('add_menu',['top_menu'=>$top_menu]);
    }

    //增加后台菜单
    function add_menu(){
        $post = $this->request->post();
        $res =  $this->validate($post,'AuthRule');
        if($res!==true){
            $this->error($res);
        }else{
            $this->auth_rule_model->allowField(true)->save($post);
            $this->success('success');
        }
    }

    //后台菜单编辑
    function show_edit_menu($id){
        $auth_rule  = $this->auth_rule_model->get($id);
        $top_title  = Db::name('auth_rule_new')->where('id',$auth_rule->pid)->value('title');
        if(empty($top_title)){
            $top_title = '顶级菜单';
        }
        $top_menu = Db::name('auth_rule_new')
            ->order(['sort' => 'DESC', 'id' => 'ASC'])
            ->select();
        $top_menu = array2Level($top_menu);
        $this->assign('top_menu',$top_menu);
        $this->assign('auth_rule',$auth_rule);
        $this->assign('top_title',$top_title);
        return $this->fetch('edit_menu');
    }

    //编辑权限
    function edit_menu(){
        $post = $this->request->post();
        $res  =  $this->validate($post,'AuthRule.edit');
        $id   = $post['id'];
        // if($id<210){
        //     $this->error('系统数据,无法编辑');
        // }
        if($res!==true){
            $this->error($res);
        }else{
            $this->auth_rule_model->allowField(true)->save($post,['id'=>$id]);
            $this->success('success');
        }
    }

    //删除权限
    function deleteAuthRule($id){
        if($id<200){
            $this->error('系统数据,无法删除');
        }
        $this->auth_rule_model->where('id',$id)->delete();
        $this->success('success');
    }

    //渲染角色页面
    function roleList(){
        $list = Db::name('auth_group_new')->where('pid',session('uid'))->select();
        
        return $this->fetch('role_list',[
            'item'=>['item1'=>'用户','item2'=>'角色列表'],
            'list'=>$list
        ]);
    }


    //角色列表ajax获取列表数据
    function get_role_list($page,$limit){
        $count = Db::name('auth_group_new')->where('pid',session('uid'))->count();
        $data  = Db::name('auth_group_new')->where('pid',session('uid'))->order('id desc')->page($page,$limit)->select();
        return json([
            'code'=>0,
            'msg'=>'success',
             'count'=>$count,
             'data'=>$data,
        ]);
    }

    //增加角色
    function add_role($title){
        $res = $this->validate(
            ['title'=>$title],
            ['title'=>'require|max:20|min:2|unique:auth_group'],
            [
                'title.require'=>'角色名称不能为空',
                'title.max'=>'太长',
                'title.max'=>'太短',
                'title.unique'=>'角色已经存在',
            ]
        );
        if(true!==$res){
            $this->error($res);
        }else{
            Db::name('auth_group_new')->insert(['title'=>$title,'status'=>1,'pid'=>session('uid')]);
            $auth_group_id  =  Db::name('auth_group_new')->getLastInsID();
            Db::name('auth_tips_new') ->insert(['auth_group_id'=>$auth_group_id]);
            $this->success('success');
        }
    }
    //获取规则数据
    public function getJson()
    {
        $id = $this->request->post('id');
        $auth_group_data = Db::name('auth_group_new')->find($id);
        $auth_rules      = explode(',', $auth_group_data['rules']);
        $auth_rule_list  = Db::name('auth_rule_new')->field('id,pid,title')->select();

        foreach ($auth_rule_list as $key => $value) {
            in_array($value['id'], $auth_rules) && $auth_rule_list[$key]['checked'] = true;
        }
        return $auth_rule_list;
    }
    //角色授权
    function auth($id){
        if($this->request->isPost()){
                $post     =  $this->request->post();
                if(session('uid')!==1&&$post['id']==1){
                    $this->error('无权限');
                }
                $res  = $this->validate($post,'AuthGroup');
                if(true!==$res){
                    $this->error($res);
                }else{
                    Db::name('auth_tips_new')
                        ->where('auth_group_id',$post['id'])
                        ->update(['mark'=>$post['mark']]);
                    unset($post['mark']);
                    $post['rules'] = is_array($post['rules'])?implode(',',$post['rules']):'';
                    Db::name('auth_group_new')
                        ->where('id',$post['id'])
                        ->update($post);
                    $this->success('授权成功');
                }
        }else{
            $data = Db::name('auth_group_new')
            ->alias('a')
            ->join('auth_tips_new b','b.auth_group_id=a.id','left')
            ->field('a.*,b.mark')
            ->where('id',$id)
            ->find();
            return $this->fetch('auth',['data'=>$data]);
        }
    }

    //删除角色
    function delete_role($id){
        if($id==1){
            $this->error('管理员无法删除');
        }else{
            $res = Db::name('auth_group_access_new')
            ->where('group_id',$id)
            ->select();
            if(!empty($res)){
                $this->error('有管理员已经绑定了此角色,无法删除');
            }else{
                Db::name('auth_group_new')
                ->delete($id);
                Db::name('auth_group_access_new')
                ->where('group_id',$id)
                ->delete();
                Db::name('auth_tips_new')
                ->where('auth_group_id',$id)
                ->delete();
                $this->success('已删除');
            }
        }
    }
    //批量删除角色
    function  deleteManyRole(){
        $ids  = $this->request->post();
        foreach ($ids as  $k1 => $v1) {
            foreach ($v1 as $k2 => $v2) {
                if($v2['id']=='1'){
                    $this->success('超级管理员权限无法删除,其余均已经删除');
                }else{
                  $this->delete_role($v2['id']);
                }
            }
        }
        $this->success('已批量删除');
    }

    function doIp(){
        $ip = '182.133.251.148';
        var_dump(getCity($ip));
    }
//class 结束
}



