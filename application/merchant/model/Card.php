<?php
namespace app\merchant\model;
use think\Model;

class Card extends Model
{
	//定义数据表
	protected $table = 'lotus_card';


    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

}