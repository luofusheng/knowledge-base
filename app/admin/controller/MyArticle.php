<?php


namespace app\admin\controller;


use think\facade\Db;

class MyArticle extends Base
{
    // 我的文章列表页面
    public function myArticle($page = 1)
    {
        // 每页显示的记录条数
        $data['pageSize'] = 5;
        // 获取的页码
        // 系统会自动接收page参数并处理，只要前端传来的参数名是page就可以
        $data['page'] = $page;
        $data['pageData'] = Db::table('article')->where('admin_id', $this->admin['admin_id'])->pages($data['pageSize']);

        // 文章分类id
        $class_ids = array_column($data['pageData']['pageRecords'], 'class_id');
        $class_ids = array_unique($class_ids);
        // 文章分类列表
        $data['article_classes'] = Db::table('article_class')->field('class_id, name')->whereIn('class_id', $class_ids)->resetKey('class_id');

        return view('', $data);
    }

    // 增加、修改文章页面
    public function addModifyArticle($articleId = 0)
    {
        // 查出文章内容
        $data['article'] = Db::table('article')->where('article_id', $articleId)->find();
        $data['detail'] = Db::table('article_content')->where('article_id', $articleId)->find();

        // 查出所有文章分类
        $data['classes'] = Db::table('article_class')->select()->toArray();

        return view('', $data);
    }

    // 保存文章
    public function save()
    {
        $articleId = (int)input('post.articleId');

        $article['title'] = trim(input('post.title'));
        $article['class_id'] = (int)input('post.classId');
        $article['status'] = (int)input('post.status');
        $article_content['keywords'] = trim(input('post.keywords'));
        $article_content['description'] = trim(input('post.description'));
        $article_content['article_id'] = $articleId;


        // 处理富文本编辑器传来的文本内容
        $content = trim(input('post.content'));
        $newContent = $this->processContent($content);

        // 找到原先content中存在但是ueditor在本次编辑中删除的图片的url，然后删除该图片
        // 找到数据库中存储的content
        $oldContent = Db::table('article_content')->where('article_id', $articleId)->find();
        $oldContent = htmlspecialchars_decode($oldContent['content']);
        // 正则表达式匹配查找图片路径
        $pattern = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
        preg_match_all($pattern, $oldContent, $oldMatches);
        $oldImgs = $oldMatches[1];
        preg_match_all($pattern, $newContent, $newMatches);
        $newImgs = $newMatches[1];
        // 删除图片
        for ($i=0; $i < count($oldImgs); $i++) {
            if (!in_array($oldImgs[$i], $newImgs)) {
                unlink('.' . $oldImgs[$i]);
            }
        }

        // 要写入数据库的处理过的content
        $article_content['content'] = htmlspecialchars($newContent);

        if ($articleId == 0) {
            // 添加
            $article['admin_id'] = $this->admin['admin_id'];
            $article['add_time'] = time();
            $articleId = Db::table('article')->insertGetId($article);
            $article_content['article_id'] = $articleId;
            Db::table('article_content')->insert($article_content);
        } else {
            // 修改
            Db::table('article')->where('article_id', $articleId)->update($article);
            Db::table('article_content')->where('article_id', $articleId)->update($article_content);
        }

        exit(json_encode(['code'=>0, 'msg'=>'保存成功']));
    }

    // 删除文章
    public function delArticle()
    {
        $articleId = (int)input('post.articleId');

        // 删除content中的图片
        $content = Db::table('article_content')->where('article_id', $articleId)->find();
        $content = $content['content'];
        // 正则表达式匹配查找图片路径
        $pattern = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
        preg_match_all($pattern, $content, $matches);
        $images = $matches[1];
        // 删除图片
        for ($i=0; $i < count($images); $i++) {
            unlink('.' . $images[$i]);
        }

        // 删除article和content
        Db::table('article')->where('article_id', $articleId)->delete();
        Db::table('article_content')->where('article_id', $articleId)->delete();

        exit(json_encode(['code'=>0, 'msg'=>'删除成功']));
    }

    /**
     * ueditor文章处理方法
     * @param $content ueditor编辑器传来的文章内容
     * @return bool|mixed|null 如果文章内容不为空，返回处理后的内容，否则返回false
     */
    private function processContent($content)
    {
        // 转移ueditor文件
        if (!empty($content)) {
            // 已经存在的图片
            $imgArray = [];
            // 完成处理的content
            $newContent = null;

            // 正则表达式匹配查找图片路径
            $pattern = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
            preg_match_all($pattern,$content,$matches);
            $count = count($matches[1]);
            for ($i = 0; $i < $count; $i++) {
                $ueditorImg = $matches[1][$i];
                // 判断是否是新上传的图片
                $position = stripos($ueditorImg, "/image_temp/");
                if ($position > 0) {
                    // 新上传的图片走这里
                    // 新建日期文件夹
                    $tempArray = explode('/', $ueditorImg);
                    $imageFloder = './uploads/ueditor/image/' . $tempArray[4];
                    if (!is_dir($imageFloder)) {
                        mkdir($imageFloder, 0777, true);
                    }
                    $tempImg = '.' . $ueditorImg;
                    $newImg = str_replace('/image_temp/', '/image/', $tempImg);
                    // 转移图片
                    rename($tempImg, $newImg);
                } else {
                    // 已经存在的图片走这里
                    $imgArray[] = $ueditorImg;
                }
            }

            // 重新整理content中的图片路径
            $newContent = str_replace('/image_temp/', '/image/', $content);
            return $newContent;
        }

        return false;
    }
}