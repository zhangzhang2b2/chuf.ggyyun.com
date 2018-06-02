<?php
namespace app\admin\validate;

use think\Validate;

class AuthGroup extends Validate
{
    protected $rule = [
        'title'     => 'require|min:2|max:15|unique:auth_rule',
        'rules'     => 'require'
    ];
    protected $message = [
    	'title.require'  =>'角色名称不能为空',
    	'title.min'		 =>'角色名称太短',
    	'title.max'		 =>'角色名称太长',
    	'title.unique'	 =>'系统中已经存在该角色名称',
        'rules.require'  =>'节点不能为空',     
    ];
    protected $scene = [

    ];

}
