<?php

namespace app\store\model;

use app\common\model\Items as ItemsModel;
use think\Db;

/**
 * 曲目模型
 * Class Goods
 * @package app\store\model
 */
class Items extends ItemsModel
{
    /**
     * 添加曲目
     * @param array $data
     * @return bool
     */
    public function add(array $data)
    {                
        // 开启事务
        Db::startTrans();
        try {
            // 添加曲目
            $this->allowField(true)->save($data);                        
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
        }
        return false;
    }
    
    /**
     * 编辑曲目
     * @param $data
     * @return bool
     */
    public function edit($data)
    {       
        // 开启事务
        Db::startTrans();
        try {
            // 保存曲目
            $this->allowField(true)->save($data);            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 删除曲目
     * @return bool
     */
    public function remove()
    {
        // 开启事务处理
        Db::startTrans();
        try {        
            // 删除当前曲目
            $this->delete();
            // 事务提交
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

}
