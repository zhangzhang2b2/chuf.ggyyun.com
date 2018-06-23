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

    //病人数据
    public function patientInfo()
    {
        return $this->hasOne('Patient','id','patient_id'); //关联的模型，外键，当前模型的主键
    }

    //病例数据
    public function recordsInfo()
    {
        return $this->hasOne('Records','reserve_id','id');
    }

    //处方表数据
    public function recipelNewInfo()
    {
        return $this->hasMany('RecipelNew','reserve_id','id');
    }

    //其他费用表
    public function otherpayInfo()
    {
        return $this->hasOne('Otherpay','reserve_id','id');
    }

    //检查表数据
    public function checkInfo()
    {
        return $this->hasOne('Check','reserve_id','id');
    }





}