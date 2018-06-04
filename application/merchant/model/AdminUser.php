<?php
namespace app\merchant\model;
use think\Model;

class AdminUser extends Model
{
	//定义数据表
	protected $table = 'lotus_admin_user_new';
	//定义自动更新时间戳
	protected $autoWriteTimestamp = 'update_time';

}