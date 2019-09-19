<?php

namespace app\common\model;

use think\Cache;
use think\Db;
/**
 * 供应商数据模型
 * Class Category
 * @package app\common\model
 */
class AdminData extends BaseModel
{
    protected $name = 'admin_data';
    protected $updateTime = false;

    public $error;

    // 
    public function changeData($post,$member_id){                
        $data = $post['goods_id'];
        $_data = [];
        foreach ($data as $key => $value) {
            $d = [
                'goods_id'=>$key,
                'price'=>$value
            ];
            $_data[] = $d;
        }        
        Db::startTrans();
        try {   
            $obj = self::get(['member_id'=>$member_id]);
            if($obj){
                $obj->save([
                    'price_data'=>json_encode($_data)
                ]);
            }else{
                $this->allowField(true)->save([
                    'member_id'=>$member_id,
                    'price_data'=>json_encode($_data)
                ]);
            }            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
        }
        return false;
    }
}
