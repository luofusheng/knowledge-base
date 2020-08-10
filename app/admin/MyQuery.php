<?php
namespace app\admin;

use think\db\Query;

/*
 * 扩展数据库查询方法
 */
class MyQuery extends Query
{
    // 分页查询
    public function pages($pageSize = 10)
    {
        $paginate = $this->paginate($pageSize);
        // 该对象的items方法可以获取具体页的数据
        $pageRecords = $paginate->items();
        // 获取总记录数
        $recordCount = $paginate->total();
        // 获取分页显示
        $pageShow = $paginate->render();

        return ['pageRecords'=>$pageRecords, 'recordCount'=>$recordCount, 'pageShow'=>$pageShow];
    }

    /**
     * 将数据集转换成二维数组，并将数组的键设置为$key字段
     * @param $key
     * @return array
     */
    public function resetKey($key)
    {
        $records = $this->select()->toArray();
        return array_column($records, null, $key);
    }
}