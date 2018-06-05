<?php
namespace app\merchant\controller;

use app\lib\event\PushEvent;    //引用文件

/**
 * 推送demo
 *
 * Class PushDemo
 * @package app\demo\controller
 */
class PushDemo 
{
    /**
     * 推送一个字符串
     */
    public function pushAString()
    {
        $string = 'Man Always dddddd';  //推送的消息内容
        // $string = input('msg') ? : $string;  //判断是否有输入消息
        $push = new PushEvent();    //实例化对象
        $push->setUser('123')->setContent($string)->push();   //setUser指定推送给的用户123，设置空则全部用户都推送。setContent推送的内容

    }

    /**
     * 推送目标页
     *
     * @return \think\response\View
     */
    public function targetPage()
    {
        return view();
    }
}