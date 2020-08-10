<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;

/*
 * 处理账号登录
 */
class Account extends BaseController
{
    public function login()
    {
        return view();
    }

    public function checkLogin()
    {
        $username = trim(input('post.username'));
        $password = trim(input('post.password'));
        $captcha = trim(input('post.captcha'));

        if ($username == '') {
            exit(json_encode(array('code'=>1, 'msg'=>'用户名不能为空')));
        }
        if ($password == '') {
            exit(json_encode(array('code'=>1, 'msg'=>'密码不能为空')));
        }
        if(!captcha_check($captcha)){
            exit(json_encode(array('code'=>1, 'msg'=>'验证码不正确')));
        };

        $admin = Db::table('admins')->where(array('username'=>$username))->find();
        if (empty($admin)) {
            exit(json_encode(array('code'=>1, 'msg'=>'用户名错误')));
        }
        if (password_verify($username.$password, $admin['password']) !== true) {
            exit(json_encode(array('code'=>1, 'msg'=>'密码错误')));
        }

        session('admin', $admin);
        // 显示保存session数据，否则exit后，session会丢失
        \think\facade\Session::save();
        exit(json_encode(array('code'=>0, 'msg'=>'登录成功')));
    }

    // 退出登录
    public function logout()
    {
        session('admin', null);
        exit(json_encode(['code'=>0, 'msg'=>'退出成功']));
    }
}