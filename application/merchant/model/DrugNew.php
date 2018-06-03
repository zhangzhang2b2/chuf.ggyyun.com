<?php
namespace app\merchant\model;
use think\Model;

class DrugNew extends Model
{
	//定义数据表
	protected $table = 'lotus_drug_new';


    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

}