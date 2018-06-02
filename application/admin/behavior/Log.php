<?php
namespace app\admin\behavior;//注意应用或模块的不同命名空间

use think\Db;
class Log {
    public function run(&$param){
        Db::name('system_log')
        	->insert($param);
    }
}