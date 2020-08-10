<?php


namespace app\admin\controller;

use think\facade\Db;

class ArticleCheck extends Base
{
    // 文章审核
    public function checkArticle($page = 1)
    {
        // 每页显示的记录条数
        $data['pageSize'] = 5;
        // 获取的页码
        // 系统会自动接收page参数并处理，只要前端传来的参数名是page就可以
        $data['page'] = $page;
        $data['pageData'] = Db::table('article')->where([
            ['status', '=', 1], ['check', '<>', 2]
        ])->pages($data['pageSize']);

        // 文章分类id
        $class_ids = array_column($data['pageData']['pageRecords'], 'class_id');
        $class_ids = array_unique($class_ids);
        // 文章分类列表
        $data['article_classes'] = Db::table('article_class')->field('class_id, name')->whereIn('class_id', $class_ids)->resetKey('class_id');

        return view('', $data);
    }

    // 文章审核中的查看需要审核的文章
    public function viewArticle($articleId = 0)
    {
        // 查出文章内容
        $data['article'] = Db::table('article')->where('article_id', $articleId)->find();
        $data['detail'] = Db::table('article_content')->where('article_id', $articleId)->find();
        // 查出所有文章分类
        $data['classes'] = Db::table('article_class')->field('class_id, name')->resetKey('class_id');

        return view('', $data);
    }

    // 审核未通过文章的修改意见
    public function modifyAdvice($articleId = 0)
    {
        $data['articleId'] = $articleId;
        $data['checkContent'] = Db::table('check')->where('article_id', $articleId)->find();

        view('', $data);
    }
}