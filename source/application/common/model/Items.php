<?php

namespace app\common\model;

use think\Request;

/**
 * 商品模型
 * Class Goods
 * @package app\common\model
 */
class Items extends BaseModel
{
    protected $name = 'items';    
    


    /**
     * 关联专辑表
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo('Goods');
    }






   /**
     * 获取曲目列表
     * @param int $status
     * @param int $items_id
     * @param string $search
     * @param string $sortType
     * @param bool $sortPrice
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($status = null, $items_id = 0, $search = '', $sortType = 'all', $sortPrice = false)
    {
        // 筛选条件
        $filter = [];
        $items_id > 0 && $filter['items_id'] = $items_id;
        $status > 0 && $filter['items_status'] = $status;
        !empty($search) && $filter['items_name'] = ['like', '%' . trim($search) . '%'];

        // 排序规则
        $sort = [];
        if ($sortType === 'all') {
            $sort = ['items_sort', 'items_id' => 'desc'];
        } elseif ($sortType === 'sales') {
            $sort = ['items_sales' => 'desc'];
        } elseif ($sortType === 'price') {
            $sort = $sortPrice ? ['items_max_price' => 'desc'] : ['items_min_price'];
        }
        // 商品表名称
        $tableName = $this->getTable();        
        // 执行查询
        $list = $this->field(['*', '(sales_initial + sales_actual) as items_sales'            
        ])->with(['goods'])
            ->where('is_delete', '=', 0)
            ->where($filter)
            ->order($sort)
            ->paginate(15, false, [
                'query' => Request::instance()->request()
            ]);
        return $list;
    }
}
