<?php
namespace app\merchant\controller;

use think\Db;
use app\merchant\model\AdminUser as UserModel;

class Index extends Main{

	//主界面
	public function index(){
        //锁屏跳转
        if(!empty(session('lock'))){
            $this->redirect('merchant/index/lockscreen');
        }
        
        $clinic = Db::name('settled')->where('type',1)->count();  //统计诊所
        $recipel = Db::name('recipel')->count();  //统计处方
        $user = Db::name('user')->count();  //统计用户
        $drug = Db::name('drug')->count();  //统计药物
        $this->assign('arr',['clinic'=>$clinic,'recipel'=>$recipel,'user'=>$user,'drug'=>$drug]);

        //统计柱形图
        $alljfze=array();   //缴费

        $marray=['01','02','03','04','05','06','07','08','09','10','11','12'];  //12个月

        foreach ($marray as $k => $v) { 
            //时间段条件，每个月份的1号和月末
            $to=date('Y')."-".$v."-01 00:00:00";
            $so=date('Y-m-d H:i:s',strtotime("$to +1 month"));
            $whereimg['register']=array(array('egt',$to),array('lt',$so));

            //某个月已金额
            $jfze = Db::name('user')
            ->where($whereimg)
            ->count();
            $alljfze[$k]= $jfze;

        }

        $jf=implode(",", $alljfze);
        $this->assign('jf', $jf);
		return $this->fetch();
	}

    public function mp3(){
        $prompt=Db::name('book')->where('type','0')->select();
        return count($prompt);
    }

    //锁屏
    function lockscreen(){
        session('lock',time());
        return $this->fetch('lockscreen');
    }

    //解锁
    function  unlock($password){
        $sql_passwd = UserModel::get(session('uid'))->password;
        if(md5($password)==$sql_passwd){
            session('lock',null);
            $this->success('验证成功','admin/index/index',1000);

        }else{
            $this->error('别逗我，密码不对');
        }

    }

}
