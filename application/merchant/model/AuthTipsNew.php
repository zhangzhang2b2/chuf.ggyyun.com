<?php
namespace app\merchant\model;
use think\Model;

class AuthTipsNew extends Model
{
	//定义数据表
	protected $table = 'lotus_auth_tips_new';
	//定义自动更新时间戳
	protected $autoWriteTimestamp = 'datetime';	

    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

}