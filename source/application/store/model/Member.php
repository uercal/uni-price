<?php

namespace app\store\model;

use app\common\model\Member as MemberModel;
use think\Request;
use think\Db;


/**
 * 订单管理
 * Class Order
 * @package app\store\model
 */
class Member extends MemberModel
{
    public function getList(){
        return $this->order('create_time desc')->paginate(10, false, [
            'query' => Request::instance()->request()
        ]);
    }


    public function add($data){
         // 开启事务
         Db::startTrans();
         try {
             $data['password'] = yoshop_hash($data['password']);
             $this->allowField(true)->save($data);
             Db::commit();
             return true;
         } catch (\Exception $e) {
             Db::rollback();
         }
         return false;
    }
}
