<?php
namespace app\admin\controller;

use org\Auth;
use think\Controller;
use think\Db;
use think\Session;
use think\Loader;

class Config extends Main{

	function index(){
        $config = Db::name('config')->find();
        $this->assign('config',$config);
        return $this->fetch();
	}
	/**
     * 更新配置
     */
    public function update()
    {
        if ($this->request->isPost()) {

           $post= input('post.');  //获取post提交过来的数据

           //插入多条数据
           $data['appid']=$post['appid'];
           $data['appsecret']=$post['appsecret'];
           $data['mchid']=$post['mchid'];
           $data['machid_key']=$post['machid_key'];
           $data['x_appid']=$post['x_appid'];
           $data['x_appsecret']=$post['x_appsecret'];

           $config=Db::name('config')->find();
           if(!empty($config)){
            //判断表里是否有数据,有数据则使用update方法执行
            $data['id']=$config['id'];
            $return= Db::name('config')->update($data);

        }else{
         $return= Db::name('config')->insert($data);   //表里没数据时用insert方法
     }

     if ( $return) {
         $this->success('提交成功');
     } else {
         $this->error('提交失败');
     }
}
}
}