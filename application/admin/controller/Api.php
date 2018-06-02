<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;

class Api extends Controller
{

    //获取openId并缓存
    public function getSession()
    {
        $code = input('post.');
        $weiAPI = "https://api.weixin.qq.com/sns/jscode2session ";
        $data['appid']  = 'wx712bc33e00268c0b';
        $data['secret']  = '08f85233965aee5a28b037740a210ad7';
        $data['js_code']  = $code['code'];
        $data['grant_type']  = 'authorization_code';
        $token = makeRequest($weiAPI, $data);
        $res=json_decode($token['result']);
        if ($res->openid) {
            $user=Db::name('user')->where('openid', $res->openid)->find();
            if (empty($user)) {
                $shuju['openid'] = $res->openid;
                $shuju['username'] = $code['nickName'];
                $shuju['avatarUrl']=$code['avatarUrl'];
                $shuju['tel']='';
                
                $user=Db::name('user')->insertGetId($shuju);
                $shuju['id']=$user;
                $shuju['sessionKey']= $res->session_key;
                return  json_encode(array('status'=>1, 'userInfo'=>$shuju));
            } else {
                $shuju = $user;
                $shuju['sessionKey']= $res->session_key;
                return  json_encode(array('status'=>1, 'userInfo'=>$shuju));
            }
            
        }
        return json_encode(['status'=>0,'message'=>'请求Token失败']);
    }

    //授权获取手机号码
    public function getPhone()
    {
        $encryptedData = input('param.encryptedData');
        $iv = input('param.iv');
        $sessionKey = input('param.sessionKey');
        $appid = 'wx712bc33e00268c0b';
        $phone=phone($encryptedData, $iv, $sessionKey, $appid);

        if ($phone['errCode'] == 0) {
            $data = json_decode($phone['data'], true);
            return json_encode(array('status'=>$phone['errCode'],'data'=>$data));
        } else {
            return json_encode($phone['errCode']);
        }
    }

    //手机注册
    public function register()
    {
        $post=input('post.');
        $data=$post['data'];
        $data['province']=$data['region'][0];
        $data['city']=$data['region'][1];
        $data['area']=$data['region'][2];
        unset($data['region']);
        $data['register']=date('Y-m-d H:i:s', time());
        $data['password']=md5($data['password']);
        $user=Db::name('user')->where('tel', $data['tel'])->find();
        if (!empty($user)) {
            return json_encode(['status'=>'0','msg'=>'用户已存在']);
        } else {
            $return=Db::name('user')->update($data);
            if ($return) {
                return json_encode(['status'=>'1','msg'=>'注册成功','data'=>$data]);
            } else {
                return json_encode(['status'=>'0','msg'=>'注册失败']);
            }
        }
    }

    //手机登录
    public function login()
    {
        $post=input('post.');
        $data=$post['data'];
        $user=Db::name('user')->where('tel', $data['tel'])->find();
        if (empty($user)) {
            return json_encode(['status'=>'0','msg'=>'用户不存在']);
        } else {
            if ($user['password'] != md5($data['password'])) {
                return json_encode(['status'=>'0','msg'=>'密码错误']);
            } else {
                // $edit['id']=$user['id'];
                // $edit['endtime']=date('Y-m-d H:i:s', time());
                // $edit['openid']=$data['openid'];
                // $edit['name']=$data['name'];
                // $edit['avatarUrl']=$data['avatarUrl'];
                // Db::name('user')->update($edit);
                return json_encode(['status'=>'1','msg'=>'登录成功','data'=>$user]);
            }
        }
    }
    //资料编辑
    public function edituser()
    {
        $post=input('post.');
        $data=$post['data'];

        $user=user($data['tel'], $data['password']);

        if (!$user) {
            return json_encode(['status'=>'0','msg'=>'修改失败']);
        } else {
            $data['id']=$user;
            $data['province']=$data['region'][0];
            $data['city']=$data['region'][1];
            $data['area']=$data['region'][2];
            unset($data['region']);
            $data['register']=date('Y-m-d H:i:s', time());
            $data['password']=$data['password'];
            Db::name('user')->update($data);
            return json_encode(['status'=>'1','msg'=>'修改成功','data'=>$data]);
        }
    }
    //获取用户信息
    public function user($openid)
    {
        $user=Db::name('user')->where('openid', $openid)->find();
        return $user;
    }

    // 诊所入驻
    public function clinics()
    {
        $post=input('post.');
        $user=$this->user($post['openid']);
        if ($user) {
            $data=$post['data'];
            $arr['user_id']=$user['id'];
            $arr['name']=$data['name'];
            $arr['sex']=$data['sex'];
            $arr['card_id']=$data['IDcard'];
            $arr['province']=$data['region'][0];
            $arr['city']=$data['region'][1];
            $arr['area']=$data['region'][2];
            $arr['address']=$post['address'];
            if (isset($post['longitude']) && !empty($post['longitude'])) {
                $arr['longitude']=$post['longitude'];
            }
            if (isset($post['latitude']) && !empty($post['latitude'])) {
                $arr['latitude']=$post['latitude'];
            }
            $arr['tel']=$data['phone'];
            $arr['clinic']=$data['clinicname'];
            $arr['abstract']=$data['abstract'];
            $arr['card_img']=implode(',', $post['imgss']);
            $arr['atlas']=implode(',', $post['imgs2s']);

            $list=Db::name('settled')->where('user_id', $user['id'])->find();
            if (empty($list)) {
                //******************为了小程序审核通过，开放诊所入驻不用后台审核*****************/
                //插入
                // $getid=Db::name('settled')->insertGetId($arr);

                // $data1['type']=1;
                // $data1['id']=$getid;
    
                // try {
                //     //更新诊所入驻审核状态
                //     $return=Db::name('settled')->update($data1);
                //     //查询所有模块id
                //     $modids=Db::name('mod')->where('status', 1)->column('id');
                //     $allmod = json_encode($modids);
                //     //插入角色表，角色类型为1管理者，获取id
                //     $roleid = Db::name('role')->insertGetId(['roletype'=>1,'settled_id'=>$getid,'mod_id'=>$allmod]);
                //     //插入用户权限表
                //     $rolearr=array();
                //     array_push($rolearr,$roleid);
                //     $rolestr =json_encode($rolearr);
                //     $res =  Db::name('userAuth')->insert(['user_id'=>$user['id'],'role_id'=>$rolestr]);
                // } catch (\Exception $e) {
                //     $this->error('系统繁忙');
                // }

                // if ($getid) {
                //     return json_encode(['status'=>'1','msg'=>'审核提交成功']);
                // } else {
                //     return json_encode(['status'=>'0','msg'=>'审核提交失败']);
                // }


                $return=Db::name('settled')->insert($arr);
                if ($return) {
                    return json_encode(['status'=>'1','msg'=>'审核提交成功']);
                } else {
                    return json_encode(['status'=>'0','msg'=>'审核提交失败']);
                }


            } else {
                //入驻审核不通过重新提交
                $card_yuan=explode(',', $list['card_img']);
                $atlas_yuan=explode(',', $list['atlas']);
                if (!empty($post['card_img'])) {
                    foreach ($post['card_img'] as $k => $v) {
                        $file=Db::name('file')->find($v);
                        @unlink(ROOT_PATH.'public'.$file['url']);
                        Db::name('file')->delete($file['id']);
                    }
                    $card=array_diff($card_yuan, $post['card_img']);//数据库保存数组与删除的数组对比返回差值
                    $arr['card_img']=implode(',', array_merge($card, $post['imgss']));//合并数组并且转为字符串
                } else {
                    $arr['card_img']=implode(',', array_merge($card_yuan, $post['imgss']));//合并数组并且转为字符串
                }

                if (!empty($post['atlas'])) {
                    foreach ($post['atlas'] as $k => $v) {
                        $file=Db::name('file')->find($v);
                        @unlink(ROOT_PATH.'public'.$file['url']);
                        Db::name('file')->delete($file['id']);
                    }
                    $atlas=array_diff($atlas_yuan, $post['atlas']);//数据库保存数组与删除的数组对比返回差值
                    $arr['atlas']=implode(',', array_merge($atlas, $post['imgs2s']));//合并数组并且转为字符串
                } else {
                    $arr['atlas']=implode(',', array_merge($atlas_yuan, $post['imgs2s']));//合并数组并且转为字符串
                }
                $arr['id']=$list['id'];
                $return=Db::name('settled')->update($arr);
                if ($return) {
                    return json_encode(['status'=>'1','msg'=>'审核提交成功']);
                } else {
                    return json_encode(['status'=>'0','msg'=>'审核提交失败']);
                }
            }
        }
    }

    //入驻详情不通过详情
    public function clinics_details()
    {
        $post=input('post.');
        $user=$this->user($post['openid']);
        $settled=Db::name('settled')->where('user_id', $user['id'])->find();
        if (!empty($settled)) {
            $settled['card_img']=Db::name('file')->where('id', 'in', $settled['card_img'])->select();
            $settled['atlas']=Db::name('file')->where('id', 'in', $settled['atlas'])->select();
        }
        return json_encode($settled);
    }
    
    // 诊所入驻图片上传
    public function uploadimg()
    {
        $file = request()->file('images');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                $str='/uploads/'.$info->getSaveName();
                $id=Db::name('file')->insertGetId(['url'=>$str,'time'=>date('Y-m-d H:i:s', time())]);
                return $id;
            } else {
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
    }

    // 药品录入
    public function drug()
    {
        $post=input('post.');
        $user=$this->user($post['openid']);
        if ($user) {
            $data=$post['data'];
            $clinicid = $post['clinicid'];
            $tab = $post['tab'];
            $id = $post['id'];
            $time = $post['time'];
            $arr['user_id']=$user['id'];
            $arr['settled_id']=$clinicid;
            $arr['name']=$data['name'];
            $arr['drug_type']=$tab;
            $arr['method']=$data['jf'];
            $arr['unit']=$data['unit'];
            $arr['pinyin']=$data['py'];
            $arr['remarks']=$data['remarks'];
            $arr['time']=$time;
            if (isset($data['gg']) && !empty($data['gg'])) {
                $arr['spec']=$data['gg'];
            }
            if (isset($data['unitprice']) && !empty($data['unitprice'])) {
                $arr['price']=$data['unitprice'];
            }
            if (isset($data['dw']) && !empty($data['dw'])) {
                $arr['bucket']=$data['dw'];
            }
            if (isset($data['stock']) && !empty($data['stock'])) {
                $arr['stock']=$data['stock'];
            }
           
            $list=Db::name('drug')->where('user_id', $user['id'])->find();
            
            if ($id == '0') {
                $code=Db::name('drug')->insert($arr);
            } else {
                $code=Db::name('drug')->where('id', $id)->update($arr);
            }
            if ($code) {
                return json_encode(['status'=>'1','msg'=>'药品录入成功']);
            } else {
                return json_encode(['status'=>'0','msg'=>'药品录入失败']);
            }
        }
    }

    // 药物维护
    public function durgwh()
    {
        $dataall = array();
        $post=input('post.');
        $clinicid = $post['clinicid'];
        $dataall['data0'] = Db::name('drug')->where('settled_id', $clinicid)->order('id desc')->where('drug_type', 0)->select();
        $dataall['data1'] = Db::name('drug')->where('settled_id', $clinicid)->order('id desc')->where('drug_type', 1)->select();
        $dataall['data2'] = Db::name('drug')->where('settled_id', $clinicid)->order('id desc')->where('drug_type', 2)->select();
  
        return json_encode($dataall);
    }


    // 药物维护--药物编辑
    public function durgbj()
    {
        $post = input('post.');
        $id = $post['id'];
        
        $data = Db::name('drug')->where('id', $id)->find();

        return json_encode($data);
    }

    // 药物维护--药物删除
    public function durgdelete()
    {
        $post = input('post.');
        $id = $post['id'];
        
        $code = Db::name('drug')->where('id', $id)->delete();
        if ($code) {
            return json_encode(['status'=>'1','msg'=>'删除成功']);
        } else {
            return json_encode(['status'=>'0','msg'=>'删除失败']);
        }
    }

    // 药物查询
    public function selectdrug()
    {
        $post = input('post.');
        $piny = $post['piny'];
        $data = Db::name('drug')->where('pinyin', $piny)->find();
        return json_encode($data);
    }
    // 处方编辑
    public function chuf()
    {
        $data = array();
        $post=input('post.');
        $cfid = $post['cfid'];
        $pageid = $post['pageid'];
        if ($pageid == '0') {
            $cf = Db::name('template')->where('id', $cfid)->find();
        } else {
            $cf = Db::name('recipel')->where('id', $cfid)->find();
            $cilnic = Db::name('settled')->where('id',$cf['settled_id'])->find();
            $data['clinicname'] = $cilnic['clinic'];
        }
        $drug = json_decode($cf['drug']);
        $data['cf'] = $cf;
        $data['drug'] = $drug;
        return json_encode($data);
    }

    // 处方录入
    public function chufadd()
    {
        $post=input('post.');
        $data = $post['data'];
        $drug = json_encode($post['drug']);
    
        $userInfo = $post['userInfo'];
        $clinicid = $post['clinicid'];
        $user_id = $post['userid'];
        $arr['user_id'] = $user_id;
        $arr['doctor_id'] = $userInfo['id'];
        $arr['settled_id'] = $clinicid;
        $arr['name'] = $data['name'];
        $arr['sex'] = $data['sex'];
        $arr['age'] = $data['age'];
        $arr['category'] = $data['category'];
        $arr['time'] = $data['time'];
        $arr['address'] = $data['address'];
        $arr['diagnosis'] = $data['diagnosis'];
        $arr['allergy'] = $data['allergy'];
        $arr['drug'] = $drug;
        $arr['doctor'] = $data['doctor'];
        $arr['dosage'] = $data['dosage'];
        $arr['review'] = $data['review'];
        $arr['amount'] = $data['amount'];
        $arr['remarks'] = $data['remarks'];
        $arr['total_amount'] = $data['total_amount'];
        
        $code = Db::name('recipel')->insert($arr);
      
        if ($code) {
            return json_encode(['status'=>'1','msg'=>'处方录入成功']);
        } else {
            return json_encode(['status'=>'0','msg'=>'处方录入失败']);
        }
    }

    // 处方查询
    public function selectcf()
    {
        $post=input('post.');
        $name = '%'.$post['name'].'%';
        $clinicid = $post['clinicid'];
        $inint = $post['inint'];
        if($inint == 0){
            $data = Db::name('recipel')->where('name','like', $name)->order('created_at desc')->where('settled_id', $clinicid)->limit(10)->select();
        }else{
            $startdate = $post['startdate'];
            $enddate = $post['enddate'];
            
            $data = Db::name('recipel')->where('name','like', $name)->where("time >= '$startdate' and time <='$enddate'")->order('created_at desc')->where('settled_id', $clinicid)->limit(10)->select();
            
        }
        // echo Db::getLastSql();
        // dump($data);exit;
        //  ->where("createtime > '$start_time' and createtime <= '$end_time'")->select();
        if ($data) {
            return json_encode($data);
        } else {
            return json_encode(['status'=>'0','msg'=>'没有符合条件的处方']);
        }
    }


    // 处方模版添加
    public function templateadd()
    {
        $post=input('post.');
        $data = $post['data'];
        $drug = json_encode($post['drug']);
        

        $userInfo = $post['userInfo'];
        $clinicid = $post['clinicid'];
        $arr['settled_id'] = $clinicid;
        $arr['name'] = $data['name'];
        $arr['sex'] = $data['sex'];
        $arr['age'] = $data['age'];
        $arr['category'] = $data['category'];
        $arr['time'] = $data['time'];
        $arr['address'] = $data['address'];
        $arr['diagnosis'] = $data['diagnosis'];
        $arr['allergy'] = $data['allergy'];
        $arr['drug'] = $drug;
        $arr['doctor'] = $data['doctor'];
        $arr['dosage'] = $data['dosage'];
        $arr['review'] = $data['review'];
        $arr['amount'] = $data['amount'];
        $arr['remarks'] = $data['remarks'];
        $arr['total_amount'] = $data['total_amount'];
        
        $code = Db::name('template')->insert($arr);
      
        if ($code) {
            return json_encode(['status'=>'1','msg'=>'模版添加成功']);
        } else {
            return json_encode(['status'=>'0','msg'=>'模版添加失败']);
        }
    }
    // 模版列表
    public function templatelist()
    {
        $post=input('post.');
        $clinicid = $post['clinicid'];
        $data = Db::name('template')->where('settled_id', $clinicid)->order('id desc')->select();
        return json_encode($data);
    }
    // 获取附近诊所
    public function nearbyclinic() {
        $data = Db::name('settled')->field('id,user_id,name,atlas,clinic,latitude,longitude')->select();
        //循环所有诊所
        foreach ($data as $k => $v) {
            $img_arr = explode(',', $v['atlas']);   //拆分诊所图片id为数组
            $img=array();   
            foreach ($img_arr as $ki => $vi) {  //循环所有图片数组
                //根据图片id查询路劲
                $url=Db::name('file')->where('id',$vi)->value('url');
                array_push($img,$url);//拼接地址
            }
            $data[$k]['img']=$img;//返回地址
        }
        return json_encode($data);

    }

    // 获取诊所
    public function getClinic(){
        $post=input('post.');
        $page=$post['page'];//参数页码
        $count = 10;//每页条数
        $alldata = $count*($page-1);//总条数

        $data = Db::name('settled')->field('id,atlas,clinic')->order('id desc')->limit($alldata, $count)->select();

        foreach ($data as $k => $v) {
            $img_arr = explode(',', $v['atlas']);   //拆分诊所图片id为数组
            $img=array();   
            foreach ($img_arr as $ki => $vi) {  //循环所有图片数组
                //根据图片id查询路劲
                $url=Db::name('file')->where('id',$vi)->value('url');
                array_push($img,$url);//拼接地址
            }
            $data[$k]['img']=$img;//返回地址
        }
        return json_encode($data);
      
    }

    // // 右链接，以右边的表为准，不管type.id
    //     $data = Db::table('commodity')->field('commodity.*,type.name tname')->join('type','commodity.cid = type.id','right')->select();
    //     //给表设置别名使用alias('c')   使用type t
    //     $data = Db::table('commodity c')->field('c.*,t.name tname')->join('type t','c.cid = t.id','right')->select(); 
    //     // union 集合 查user表里的名字和commodity里的名字

    // 获取轮播图
    public function getLB () {
        $allimg = array();
        $indexlb = Db::name('other')->where('area',0)->where('status',1)->find();
        $cliniclb = Db::name('other')->where('area',1)->where('status',1)->find();
        $indeximg = explode(',',$indexlb['files']);
        $clinicimg = explode(',',$cliniclb['files']);
        $imgindex = array();
        $imgclinic = array();
        foreach ($indeximg as $key => $value) { //
            $indexurl = Db::name('file')->where('id',$value)->value('url');
            array_push($imgindex,$indexurl);
        }
        foreach ($clinicimg as $key => $value) { //
            $clinicurl = Db::name('file')->where('id',$value)->value('url');
            array_push($imgclinic,$clinicurl);
        }

        $allimg['imgindex']=$imgindex;
        $allimg['imgclinic']=$imgclinic;
        return json_encode($allimg);
    } 

    // 诊所详情
    public function clinicdetails() {
        $post = input('post.');  
        $clinic1 = Db::name('settled')->where('id',$post['id'])->find();
        $imgid = explode(',',$clinic1['atlas']);
        $img = array();
        foreach ($imgid as $key => $value) {
            $url = Db::name('file')->where('id','eq',$value)->value('url');
            array_push($img,$url);    
        }
        $clinic1['img'] = $img;
        return json_code(200, '请求成功',$clinic1);
    }

    // 个人病例
    public function personalCase() {
        $post = input('post.');
        $userid = $post['userid'];
        $page = $post['page'];
        $count = 10;
        $alldata = $count*($page-1);
        $data = Db::name('recipel')->field('id,time,name,diagnosis')->where('user_id',$userid)->order('id desc')->limit($alldata, $count)->select();
        return json_encode($data);
    }

    // 查询医生，药师
    // public function getDoctor() {
    //     $post = input('post.');
    //     $clinicid = $post['clinicid'];
    //     $data = Db::name('role')->where('settled_id',$clinicid)->select();
    //     dump($data);exit();
    //     if($data){
    //         return json_encode(['status'=>'1','msg'=>'查询成功'],$data);
    //     }else{
    //         return json_encode(['status'=>'0','msg'=>'查询失败'],$data);
    //     }
    // }

    // 诊所搜索
    public function searchClinic() {
        $post = input('post.');
        $cname = $post['clinicname'];
        $clinic = Db::name('settled')->where('clinic',$cname)->field('id,clinic')->find();
        return json_encode($clinic);
    }


    /*************************zly******************************* */
    //获取用户权限
    public function userAuth()
    {

        $post=input('post.');
        $user_id=$post['user_id'];
        try {
            //根据user_id获取角色和模块权限
            $result=Db::name('userAuth')
            ->where('user_id', $user_id)
            ->find();
            $roleid_arr =json_decode($result['role_id']);
            $where=array();
            $where['id']=array('in',$roleid_arr);
            $res_role=Db::name('role')->where($where)->select();

            foreach ($res_role as $k => $v) {
                $set_res=Db::name('settled')->where('id', 'eq', $v['settled_id'])->value('clinic');
                $res_role[$k]['zsname']=$set_res;
            }

            return json_code(200, '请求成功1', $res_role);    //封装json_code返回方法
        } catch (\Exception $e) {
            return json_code(400, '系统繁忙');
        }
    }
    
    //获取模块权限
    public function modId()
    {
        $post=input('post.');
        $mod_id=json_decode($post['mod_id']);
        $where=array();
        $where['id']=array('in',$mod_id);
        $where['pid']=0;
        $where['status']=1;

        try {
            $result = Db::name('mod')
            ->where($where)
            ->select();
            return json_code(200, '请求成功', $result);    //封装json_code返回方法
        } catch (\Exception $e) {
            return json_code(400, '系统繁忙');
        }
    }

    //所有权限列表
    public function allModId()
    {
        try {
            $result = Db::name('mod')
            ->where('status', 'eq', 1)
            ->field('id,title,pid')
            ->select();
            return json_code(200, '请求成功', $result);    //封装json_code返回方法
        } catch (\Exception $e) {
            return json_code(400, '系统繁忙');
        }
    }

    //保存角色权限
    public function saveRole()
    {
        $post=input('post.');

        //如果是编辑
        if (isset($post['id'])) {
            $result=Db::name('role')->update($post);
            if ($result) {
                return json_code(200, '请求成功', $result);    //封装json_code返回方法
            } else {
                return json_code(400, '系统繁忙');    //封装json_code返回方法
            }
        } else {
            //如果该医院该类型已存在，则不插入
            $findres=Db::name('role')
            ->where('roletype', 'eq', $post['roletype'])
            ->where('settled_id', 'eq', $post['settled_id'])
            ->find();

            if ($findres) {
                return json_code(200, '该角色类型已存在', ['erro'=>1]);
            } else {
                $result = Db::name('role')->insert($post);
                return json_code(200, '请求成功', $result);    //封装json_code返回方法
            }
        }
    }

    //查找所有角色
    public function getRole()
    {
        $post=input('post.');
        //根据诊所的id，查找所有角色
        $result=Db::name('role')
        ->where('settled_id', 'eq', $post['settled_id'])
        ->where('roletype', 'neq', 1)
        ->select();

        if ($result) {
            //再根据模块id，查找角色拥有的模块权限
            foreach ($result as $k => $v) {
                $modarr = json_decode($v['mod_id']);
                $modres = Db::name('mod')
                    ->where('id', 'in', $modarr)
                    ->select();
                $result[$k]['mods']=$modres;
            }
            return json_code(200, '请求成功', $result);
        } else {
            return json_code(400, '请求失败');    //封装json_code返回方法
        }
    }

    //删除该角色
    public function delRole()
    {
        $post=input('post.');
        //根据id删除角色
        try {
            $result=Db::name('role')
            ->where('id', 'eq', $post['id'])
            ->delete();
            return json_code(200, '请求成功', $result);    //封装json_code返回方法
        } catch (\Exception $e) {
            return json_code(400, '系统繁忙');
        }
    }

    //保存用户权限
    public function saveUserAuth()
    {
        $post = input('post.');
        //先找到该手机号的用户
        $userin=Db::name('user')->where('tel', 'eq', $post['phone'])->find();

        if ($userin) {
            $userid=$userin['id'];
            //根据用户id查看用户是否存在
            $findAuth = Db::name('userAuth')->where('user_id', 'eq', $userid)->find();
            if ($findAuth) {    //用户id存在时
                
                
                $ylrole=json_decode($findAuth['role_id']);//原来的权限数组
                $arrauth=array();
                foreach ($ylrole as $ky=> $vy) {
                    if ($vy!=$post['roletype']) {
                        array_push($arrauth, $vy);
                    }
                }

                //该诊所所有角色类型
                //(因为一个手机号要对应多个诊所，每个诊所只能扮演一个角色，所以有了下面的炒蛋代码)
                $zsrole=Db::name('role')->where('settled_id', 'eq', $post['settled_id'])->select();

                for ($i=0;$i<count($arrauth);$i++) {
                    for ($j=0;$j<count($zsrole);$j++) {
                        if ($arrauth[$i]==$zsrole[$j]['id']) {
                            $arrs1=$i;
                        }
                    }
                }
                
                
                    $newarrauth=array();
                    foreach ($arrauth as $kr => $vr) {

                        if (isset($arrs1)) {
                            if ($kr!=$arrs1) {
                                array_push($newarrauth, $vr);
                            }
                        }else{
                            array_push($newarrauth, $vr);
                        }
                    }
                    array_push($newarrauth, $post['roletype']);


                // 把权限数组，重新拼接json字符串
                $rolestr =json_encode($newarrauth);

                
                //更新数据库
                $result = Db::name('userAuth')->where('id', $findAuth['id'])->update(['role_id'=>$rolestr,'name'=>$post['name']]);
                return json_code(200, '请求成功', $result);
            } else {    //不存在用户id被添加
                //把role_id权限数组，重新拼接json字符串
                $role_arr=array();
                array_push($role_arr, $post['roletype']);
                $rolestr =json_encode($role_arr);
                //插入数据库
                $result = Db::name('userAuth')->insert(['user_id'=>$userid,'name'=>$post['name'],'role_id'=>$rolestr]);
                return json_code(200, '请求成功', $result);    //封装json_code返回方法
            }
        } else {
            return json_code(200, '该用户没有注册', ['erro'=>1]);
        }
    }


    //该诊所所有角色
    public function findRole()
    {
        $settled_id = input('post.settled_id');
        //根据诊所id查找除了管理员外的角色类型id
        $result=Db::name('role')
        ->where('settled_id', 'eq', $settled_id)
        ->where('roletype', 'neq', 1)
        ->select();
        //把角色类型id转成名称
        $res=array();
        foreach ($result as $k => $v) {
            switch ($v['roletype']) {
                case 2:
                    $res[$k]['id']=$v['id'];
                    $res[$k]['name']='医师';
                    break;
                case 3:
                    $res[$k]['id']=$v['id'];
                    $res[$k]['name']='药师';
                    break;
                case 4:
                    $res[$k]['id']=$v['id'];
                    $res[$k]['name']='护士';
                    break;
            }
        };
        return json_code(200, '请求成功', $res);
    }

    //该诊所所有人员
    public function settleUser()
    {
        $settled_id = input('post.settled_id');
        //根据诊所id查找所有人员
        //查找该诊所的权限
        $resid=Db::name('role')->where('settled_id', 'eq', $settled_id)->select();

        //根据权限中的角色类型，找到拥有该类型的该用户权限
        $alluser=Db::name('userAuth')->alias('a')
        ->join('user b', 'a.user_id=b.id', 'LEFT')
        ->field('a.*,b.tel')
        ->select();
        $arr_res=array();
        foreach ($alluser as $k => $v) {    //所有权限者
            $rid_arr = json_decode($v['role_id']);
            foreach ($rid_arr as $kr => $vr) {  //某权限者的所有角色
                foreach ($resid as $ki => $vi) {    //该诊所的角色
                    if ($vr==$vi['id']) {
                        array_push($arr_res,['userAuths'=>$v,'roletype'=>$vi['roletype']]);
                        // $arr_res[$k]['userAuths']=$v;
                        // $arr_res[$k]['roletype']=$vi['roletype'];
                    }
                }
            }
        }
        return json_code(200, '请求成功', $arr_res);
    }


    //该诊所会员信息
    public function memberList()
    {
        $post=input('post.');
        $page=$post['page'];//参数页码
        $count = 10;//每页条数
        $yema = $count*($page-1);//总条数

        //搜索条件
        $where=array();
        if ($post['findstr']!='') {
            $where['r.name']=array('like','%'.$post['findstr'].'%');
        }
        $where['r.settled_id']=intval($post['settled_id']);//诊所id
        try {
            $result = Db::name('recipel')->alias('r')
            ->join('user u', 'u.id=r.user_id', 'LEFT')
            ->where($where)
            ->where('user_id','neq',0)
            ->field('r.id,r.user_id,r.name,u.tel')
            ->order('r.id desc')
            ->group("r.user_id")
            ->limit($yema, $count)
            ->select();
            return json_code(200, '请求成功', $result);
        } catch (\Exception $e) {
            return json_code(400, '系统繁忙');
        }
    }

    //该会员信息
    public function memberInfo()
    {
        $post=input('post.');
        $page=$post['page'];//参数页码
        $count = 10;//每页条数
        $yema = $count*($page-1);//总条数

        try {
            //根据用户id获取用户信息
            $res['userinfo']=Db::name('user')->where('id', 'eq', $post['userid'])->find();

            $res['records'] = Db::name('recipel')
            ->where('user_id', 'eq', $post['userid'])
            ->field('id,name,time,diagnosis')
            ->order('id desc')
            ->limit($yema, $count)
            ->select();
            

            return json_code(200, '请求成功',$res);
        } catch (\Exception $e) {
            return json_code(500, '系统繁忙');
        }
    }

    //获取该处方详情
    public function getRecipel(){
        $post=input('post.');

        $res=Db::name('recipel')->alias('r')
        ->join('settled s','s.id=r.settled_id','LEFT')
        ->where('r.id','eq',$post['id'])
        ->field('r.*,s.clinic')
        ->find();

        $res['thedrug'] = json_decode($res['drug']);

        return json_code(200, '请求成功',$res);
    }

    //删除某诊所的某个人员
    public function delStaff(){
        $post=input('post.');
        //根据id查用户权限表
        $theUser=Db::name('userAuth')->where('id','eq',$post['id'])->find();
        $ylrole=json_decode($theUser['role_id']);

        //根据该人员角色数组，找到拥有的该诊所的那个权限
        foreach ($ylrole as $k => $v) {
            //根据角色id找到诊所值
            $settled_id=Db::name('role')->where('id', 'eq', $v)->value('settled_id');
            
            //如果诊所值等于该诊所，则删除这个权限id
            if($settled_id==$post['settled_id']){
                array_splice($ylrole, $k, 1);
            }
        }


        if(empty($ylrole)){
            $res=Db::name('userAuth')->where('id','eq',$post['id'])->delete();
        }else{
            //把新的权限数组更新到用户权限中
            $newrole = json_encode($ylrole);
            $res=Db::name('userAuth')->where('id','eq',$post['id'])->update(['role_id' => $newrole]);
        }
        return json_code(200, '请求成功',$res);
    }

    //获取未处理处方信息
    public function cfInfo()
    {
        $post=input('post.');
        $page=$post['page'];//参数页码
        $count = 16;//每页条数
        $yema = $count*($page-1);//总条数

        //搜索条件
        $where['settled_id']=intval($post['settled_id']);//诊所id
        
        
        if ($post['roletype']==3&&$post['tab']==0) {    //如果是药师且查询最新处方
            $where['schedule']=0;
        }else if($post['roletype']==3&&$post['tab']==1){    //如果是药师且查询已收处方
            $where['schedule']=1;
        }else if ($post['roletype']==4&&$post['tab']==0) {    //如果是护士且查询最新处方
            $where['schedule']=1;
        }else if($post['roletype']==4&&$post['tab']==1){    //如果是护士且查询已收处方
            $where['schedule']=2;
        }
    
        try {
            $result = Db::name('recipel')
            ->where($where)
            ->order('id desc')
            ->limit($yema, $count)
            ->select();
            return json_code(200, '请求成功', $result);
        } catch (\Exception $e) {
            return json_code(400, '系统繁忙');
        }
    }

    
    //处方提醒
    public function cfPrompt()
    {
        $post=input('post.');

        //搜索条件
        $where['settled_id']=intval($post['settled_id']);//诊所id
        
        if ($post['roletype']==3) {    //如果是药师且查询最新处方
            $where['schedule']=0;
        } else if ($post['roletype']==4) {    //如果是护士且查询最新处方
            $where['schedule']=1;
        }

        //查询符合条件的处方
        $result = Db::name('recipel')
        ->where($where)
        ->order('id desc')
        ->select();

        //比较时间得出新订单
        // 由于Ajax三秒钟才执行一次，所以新数据的插入时间要晚于查询的的请求时间（当前时间）5秒钟
        $time = time() - 5;
        if($result){
            foreach ($result as $k => $v) {
                //该处方创建时间戳
                $catime = strtotime($v['created_at']);

                if($catime>$time){
                    $cot=count($result);
                    return json_code(200, '请求成功', $cot);
                }else if($k==count($result)-1){
                    return json_code(200, '请求成功', 0);
                }
            }
        }else {
            return json_code(200, '请求成功', 0);
        }

    }


    //处理处方
    public function dealSchedule(){
        $post=input('post.');

        if($post['roletype']==3){   //角色是药师
            $zt=1;  //处方状态1 
            //锁表查询处方状态
            $schedule = Db::name('recipel')->lock(true)->where('id','eq',$post['id'])->value('schedule');
            if($schedule==1){   //如果状态与当前状态相同
                return json_code(200, '系统繁忙',0);
            }
        }else if($post['roletype']==4){  //角色是护士
            $zt=2;  //处方状态2
            $schedule = Db::name('recipel')->lock(true)->where('id','eq',$post['id'])->value('schedule');
            if($schedule==2){
                return json_code(200, '系统繁忙',0);
            }
        }

        //当前时间
        $thetime=date('Y-m-d H:i:s');
        //开启事务
        DB::startTrans();
        try {
            Db::name('recipel')->where('id','eq',$post['id'])->update(['schedule' => $zt,'created_at'=>$thetime]);
            Db::commit();//提交事务
            return json_code(200, '请求成功', 1);
        } catch (\Exception $e) {
            Db::rollback();//回滚事务
            return json_code(400, '系统繁忙');
        }

    }


            //测试
            public function aaa11(){
                var_dump("测试1111");exit();
            }




}
