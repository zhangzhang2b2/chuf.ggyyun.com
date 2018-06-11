<?php
namespace app\merchant\model;
use think\Model;

class Reserve extends Model
{
	//定义数据表
	protected $table = 'lotus_reserve';


    public function file()
    {
        return $this->hasOne('File','id','file_id');
    }

    public function patientInfo()
    {
        return $this->hasOne('Patient','id','patient_id');
    }

}