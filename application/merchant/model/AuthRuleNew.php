<?php
namespace app\merchant\model;
use think\Model;

class AuthRuleNew extends Model
{
	//定义数据表
	protected $table = 'lotus_auth_rule_new';

    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

}