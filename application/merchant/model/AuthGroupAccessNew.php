<?php
namespace app\merchant\model;
use think\Model;

class AuthGroupAccessNew extends Model
{
	//定义数据表
	protected $table = 'lotus_auth_group_access_new';

    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

}