<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;

class Home extends Base
{
    // 主页页面
    public function index()
    {
        return view();
    }

    // 欢迎页面
    public function welcome()
    {
        return '<div style="text-align: center;color: #666;font-size: 28px;margin-top: 80px;">欢迎使用</div>';
    }

    // 个人中心
    public function personalCenter()
    {
        $menus = [];

        $role = Db::table('admin_roles')->where('role_id', $this->admin['role_id'])->find();
        if (!is_null($role)) {
            $role['permissions'] = (!empty($role['permissions'])) ? json_decode($role['permissions'], true) : [];
        }
        if ($role['permissions']) {
            $menus = Db::table('admin_menus')->where([['menu_id', 'in', $role['permissions']], ['ishidden', '=', 0], ['status', '=', 0]])->resetKey('menu_id');
            $menus && $menus = $this->getTree($menus);
        }

        View::assign('menus', $menus);

        return view();
    }

    /**
     * 将重置键后的二维数据转换成树
     * @param $menus 二维数组
     * @return array 转换完成后的树
     */
    private function getTree($menus)
    {
        $tree = [];
        foreach ($menus as $item) {
            if (isset($menus[$item['pid']])) {
                // 如果有父级菜单走这里
                $menus[$item['pid']]['children'][] = &$menus[$item['menu_id']];
            } else {
                // 如果没有父级菜单（既为顶级菜单）走这里
                $tree[] = &$menus[$item['menu_id']];
            }
        }
        return $tree;
    }
}