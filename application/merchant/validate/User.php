<?php
namespace app\merchant\validate;

use think\Validate;

/**
* 
*/
class User extends Validate
{
   protected $rule = [
        'group_id'       => 'require',
        'username'       => 'require|min:1|max:30|unique:admin_user',
        'password'       => 'require|min:1|max:30',
        'check_password' => 'require|confirm:password',
        'mobile'		 => 'require|check_mobile_number|unique:admin_user',
        'email'          => 'require|email|unique:admin_user',

    ];
    
    protected $message = [
        'group_id.require'          =>'请选择用户组',
    	'email.require'		        =>'电子邮箱不能为空',
        'email.unique'           	=> '邮箱已存在，请更换邮箱',
        'username.require'       	=> '用户名不能为空',
        'username.unique'        	=> '用户名重复',
        'username.min'           	=> '用户名最短3位',
        'username.max'           	=> '用户名最长30位',
        'password.require'       	=> '密码必须',
        'password.min'           	=> '用户名最短3位',
        'password.max'           	=> '用户名最长30位',
        'check_password.require' 	=> '请确认密码',
        'check_password.confirm' 	=> '输入密码不一致',
        'mobile.require'		 	=> '电话号码不能为空',
        'mobile.check_mobile_number' => '电话号码格式不对',
        'mobile.unique' => '电话号码不能重复',
    ];
    protected $scene = [
        'login' => ['username' => 'require|min:5|max:30', 'password'],
        //编辑场景
        'edit'  => [
            'group_id',
            'username' => 'require|min:5|max:30|unique:admin_user',
            'mobile' ,
            'email'    => 'require|email',
        ],
    ];

	/**
	*自定义验证 
	* 手机号格式检查
	* @param string $mobile
	* @return bool
	*/
	protected function check_mobile_number($mobile)
	{
	    if (!is_numeric($mobile)) {
	        return false;
	    }
	    $reg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';

	    return preg_match($reg, $mobile) ? true : false;
	}


}