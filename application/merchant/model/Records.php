<?php
namespace app\merchant\model;
use think\Model;

class Records extends Model
{
	//定义数据表
	protected $table = 'lotus_records';


    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

}