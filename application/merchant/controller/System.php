<?php
namespace app\merchant\controller;

use org\Auth;
use think\Controller;
use think\Db;
use think\Session;
use think\Loader;

class System extends Main{
	//渲染页面
	function index(){
		$list = Db::name('system_log')
			->order('id desc')
			->paginate('15');
		return $this->fetch('index',['list'=>$list]);
	}
	function setting(){
        $site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
        return $this->fetch('setting', ['site_config' => $site_config]);
	}
	/**
     * 更新配置
     */
    public function updateSiteConfig()
    {
        if ($this->request->isPost()) {
            $site_config                = $this->request->post('site_config/a');
            // $site_config['code'] = htmlspecialchars_decode($site_config['code']);
            $data['value']              = serialize($site_config);
            if (Db::name('system')->where('name', 'site_config')->update($data) !== false) {
                $this->success('提交成功');
            } else {
                $this->error('提交失败');
            }
        }
    }
}