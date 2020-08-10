<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;

class Base extends BaseController
{
    protected $admin;

    // 权限校验
    public function initialize() {
        // 获取session中的用户数据
        $admin = session('admin');
        // 是否已经登录（已经登录：放行；未登录：跳转到登录页面
        if (!$admin) {
            header('Location: login');
            exit;
        }

        // 判断用户是否被分配权限组（角色）
        $roles = Db::table('admin_roles')->where('role_id', $admin['role_id'])->find();
        if (!$roles) {
            $this->outputError('该角色不存在');
        }

        // 获取该角色权限
        $permissions = json_decode($roles['permissions']);

        // 获取用户访问的控制器名称、方法名称
        $controller = request()->controller();
        $method = request()->action();

        // 判断当前控制器和方法（菜单）是否存在
        $menu = Db::table('admin_menus')->where(['controller'=>$controller, 'method'=>$method])->find();
        if (!$menu) {
            $this->outputError('该菜单不存在');
        }

        // 判断当前用户是否具有当前菜单的权限
        if (!in_array($menu['menu_id'], $permissions)) {
            $this->outputError('对不起，您没有权限');
        }

        // 将用户数据传递给对应的模板
        $this->admin = $admin;
        View::assign('admin', $admin);
    }

    // 输出错误信息
    private function outputError($msg) {
        if (request()->isAjax()) {
            exit(json_encode(['code'=>1, 'msg'=>$msg]));
        } else {
            exit('<div style="color: red;text-align: center;">' . $msg . '</div>');
        }
    }
}