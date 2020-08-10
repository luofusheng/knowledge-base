<?php


namespace app\admin\controller;

use think\facade\Db;

class ArticleClass extends Base
{
    // 文章分类
    public function articleClass($type = '', $value = 0)
    {
        // 如果要返回上级菜单
        if ($type == 'pid') {
            // 找到该上级分类的目录
            $pclass = Db::table('article_class')->where('class_id', $value)->find();
            // 根据该上级菜单记录的pid，找到与上级菜单同级的所有记录
            $data['classes'] = Db::table('article_class')->where('pid', $pclass['pid'])->order('ord')->select()->toArray();
            // 设置渲染出的新的菜单列表中的返回上级菜单的按钮的id，值为该级菜单的pid
            $data['backId'] = $pclass['pid'];
        } else {
            // 如果不返回上级菜单
            // 接收子分类按钮传来的id，即该子分类按钮所在行的分类的class_id
            // 如果能接收到值，就说明子分类按钮被点击，此时数据库要查询的pid就是传入的class_id
            // 如果接收不到值，就说明子分类按钮没有被点击，系统第一次进入分类列表页面，此时数据库要查询的pid就是0
            $data['classId'] = $value;
            $data['classes'] = Db::table('article_class')->where('pid', $data['classId'])->order('ord')->select()->toArray();
            // 返回上级分类的按钮的id
            $data['backId'] = $data['classId'];

        }

        // 所有分类
        $data['allClass'] = Db::table('article_class')->field('class_id, name')->select()->toArray();

        return view('', $data);
    }

    // 保存文章分类
    public function saveClass()
    {
        $pid = (int)input('post.pid');
        $parents = input('post.parent');    // 子级分类的新上级
        $ords = input('post.ords');
        $names = input('post.names');

        // 按照ord排序进行修改
        foreach ($ords as $k=>$v) {
            // 如果是添加新分类
            $k==0 && $names[0] && Db::table('article_class')->insert(['ord'=>$v, 'name'=>$names[$k], 'pid'=>$pid]);
            // 如果是修改分类
            $k>0 && Db::table('article_class')->where('class_id', $k)->update(['ord'=>$v, 'name'=>$names[$k], 'pid'=>$parents[$k]]);
        }
        exit(json_encode(['code'=>0, 'msg'=>'保存成功']));
    }
}