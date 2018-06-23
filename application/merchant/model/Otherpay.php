<?php
namespace app\merchant\model;
use think\Model;

class Otherpay extends Model
{
	//定义数据表
	protected $table = 'lotus_otherpay';


    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

}