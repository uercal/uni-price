<?php

namespace app\store\controller;

use app\store\model\Category;
use app\store\model\Goods as GoodsModel;
use app\store\model\Items as ItemsModel;

/**
 * 曲目管理控制器
 * Class Goods
 * @package app\store\controller
 */
class Items extends Controller
{
    /**
     * 曲目列表(出售中)
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ItemsModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加曲目
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            // 专辑分类
            $catgory = Goods::getCacheTree();            
            return $this->fetch('add', compact('catgory'));
        }
        $model = new ItemsModel;
        if ($model->add($this->postData('items'))) {
            return $this->renderSuccess('添加成功', url('items/index'));
        }
        $error = $model->getError() ?: '添加失败';
        return $this->renderError($error);
    }

    /**
     * 删除曲目
     * @param $items_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($items_id)
    {
        $model = ItemsModel::get($items_id);
        if (!$model->remove()) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 曲目编辑
     * @param $items_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($items_id)
    {
        // 曲目详情
        $model = ItemsModel::detail($items_id);
        if (!$this->request->isAjax()) {
            // 曲目分类
            $catgory = Goods::getCacheTree();            
            return $this->fetch('edit', compact('model', 'catgory'));
        }
        // 更新记录
        if ($model->edit($this->postData('items'))) {
            return $this->renderSuccess('更新成功', url('items/index'));
        }
        $error = $model->getError() ?: '更新失败';
        return $this->renderError($error);
    }

}
