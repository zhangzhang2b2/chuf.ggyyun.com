<?php
namespace app\merchant\model;
use think\Model;

class RecipelNew extends Model
{
	//定义数据表
	protected $table = 'lotus_recipel_new';


    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

}