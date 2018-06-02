<?php
namespace app\merchant\model;
use think\Model;

class Payitems extends Model
{
	//定义数据表
	protected $table = 'lotus_payitems';
	//定义自动更新时间戳
	protected $autoWriteTimestamp = 'addtime';	

    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

}