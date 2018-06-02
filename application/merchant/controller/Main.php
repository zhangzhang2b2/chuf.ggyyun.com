<?php
namespace app\merchant\controller;

use org\AuthNew;
use think\Controller;
use think\Db;
use think\Session;
use think\Loader;
use think\Url;

class Main extends Controller{

	function  _initialize(){
		if(empty(session('uid'))){
			$this->redirect('/merchant/login/index');
		}
		$this->checkAuth();
		$this->getMenu();
		// 输出当前请求控制器（配合后台侧边菜单选中状态）
		// $prompt=Db::name('book')->where('type','0')->select();
		// $this->assign('prompt',count($prompt));
		$this->assign('controller', Loader::parseName($this->request->controller()));
		$this->assign('action', Loader::parseName($this->request->action()));
	}

	  /**
	 * 权限检查
	 * @return bool
	 */
	protected function checkAuth()
	{
		$module     = $this->request->module();
		$controller = $this->request->controller();
		$action     = $this->request->action();
		// 排除权限
		$not_check = ['merchant/AdminUser/get_edit_data','merchant/AdminUser/get_role_list','merchant/AdminUser/getjson','merchant/AdminUser/show_edit_menu','merchant/AdminUser/show_add_menu','merchant/Index/lockscreen','merchant/Index/unlock'];
		$group_id  = Db::name('auth_group_access_new')->where('uid',Session::get('uid'))->value('group_id');
		if (!in_array($module . '/' . $controller . '/' . $action, $not_check)) {
			$auth     = new AuthNew();
			$admin_id = Session::get('uid');
			if (!$auth->check($module . '/' . $controller . '/' . $action, $admin_id) && $admin_id != 1&&$group_id!==1) {
				$this->error('没有权限','');
			}
		}

	}

	/**
	 * 获取侧边栏菜单
	 */
	protected function getMenu()
	{
		$menu           = [];
		$admin_id       = Session::get('uid');

		$auth           = new AuthNew();
		$auth_rule_list = Db::name('auth_rule_new')->where('status', 1)->order(['sort' => 'DESC', 'id' => 'ASC'])->select();
		
		foreach ($auth_rule_list as $value) {
			if ($auth->check($value['name'], $admin_id) || $admin_id == 1) {
				$menu[] = $value;
			}
		}

		$menu = !empty($menu) ? array2tree($menu) : [];
		
		
		$this->assign('menu', $menu);
	}


}