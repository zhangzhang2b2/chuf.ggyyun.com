<?php
namespace app\merchant\controller;

use think\Controller;
use think\Db;
/**
*
*/
class Login extends Controller
{
	//登陆页面渲染
	function index(){
		$uid = session('uid');
		if(!empty($uid)){
			$this->success('您已经登陆，无需重复登陆','merchant/index/index');
		}
		return $this->fetch();
	}

	//登陆
	function login(){
		$post = $this->request->post();
		$db = Db::name('admin_user_new')
			->where('username',$post['username'])
			->find();
		if(empty($db['password'])){
			$this->error('用户名不存在！');
		}
		if(md5($post['password'])!==$db['password']){
			$this->error('密码不正确');
		}else{
			//记住我
			if(array_key_exists('remember',$post)&&$post['remember']=='on'){
				cookie('username',$post['username'],3600*24*7);
				cookie('password',$post['password'],3600*24*7);
			}else{
				$this->clearCookie();
			}
			session('uid',$db['id']);//当前用户id
			session('pid',$db['pid']);//上级用户id
			session('username',$db['username']);
			$location_arr = getCity($this->request->ip());
			$location = $location_arr['country'].'*'.$location_arr['area'].'*'.$location_arr['region'].'*'.$location_arr['city'];
			//钩子
	        $param = ['name'=>'成功登陆','way'=>'Login/login','username'=>session('username'),'create_time'=>date('Y-m-d H:i:s',time()),'ip'=>$this->request->ip(),'location'=>$location];
	        \think\Hook::listen('log',$param);
			$this->success('成功登陆','merchant/index/index');
		}
	}
	//注销
	function logout(){
		session('uid',null);
		session('username',null);
		$this->redirect('merchant/login/index');
	}
	//清除cookie
	function clearCookie(){
		cookie('username',null);
		cookie('password',null);
		cookie(null, 'think_');
	}

}