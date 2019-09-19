<?php

namespace app\api\model;

use app\common\model\AdminData;
use app\common\model\Goods as GoodsModel;
use app\common\model\GroupData;

/**
 * 商品模型
 * Class Goods
 * @package app\api\model
 */
class Goods extends GoodsModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'sales_initial',
        'sales_actual',
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time',
    ];

    public function getIndexList($member_id = 0)
    {
        if ($member_id != 0) {
            //
            $obj = Member::where(['member_id' => $member_id])->find();
            $type = $obj['type'];
            $pid = $obj['pid'];
            $list = $this->order('goods_sort asc')->select()->toArray();
            //
            switch ($type) {
                case 0:
                    # 供应商
                    $data = AdminData::where(['member_id' => $member_id])->value('price_data');
                    if (!empty($data)) {
                        $_list = json_decode($data, true);
                        $_list = array_column($_list,'price','goods_id');
                        foreach ($list as $key => &$value) {
                            // 
                            if(!isset($_list[$value['goods_id']])){
                                $value['price'] = 0;
                            }else{
                                $value['price'] = $_list[$value['goods_id']];
                            }
                            // 
                            
                        }
                    }
                    break;
                case 1:
                    # 内部经销商
                    $data = AdminData::where(['member_id' => $pid])->value('price_data');                    
                    $_list = json_decode($data, true);
                    $_list = array_column($_list,'price','goods_id');
                    foreach ($list as $key => &$value) {
                        // 
                        if(!isset($_list[$value['goods_id']])){
                            $value['price'] = 0;
                        }else{
                            $value['price'] = $_list[$value['goods_id']];
                        }
                        //                         
                    } 
                    $model = new GroupData;
                    $mapRaw = "concat(',',child_member,',') LIKE '%$member_id%'";
                    $__list = $model->where($mapRaw)->where(['create_member' => $pid])->value('data');
                    $__list = json_decode($_list, true);
                    $__list = array_column($_list,'price','goods_id');                                       
                    foreach ($_list as $key => &$value) {
                         // 
                         if(!isset($__list[$value['goods_id']])){
                            $value['price'] = 0;
                        }else{
                            $value['price'] = $value['price']*$__list[$value['goods_id']]['rate']/100;
                        }
                        //                         
                    }
                    $list = $_list;
                    break;
                case 2:
                    # 外部经销商
                    $data = AdminData::where(['member_id' => $pid])->value('price_data');                    
                    $_list = json_decode($data, true);
                    $_list = array_column($_list,'price','goods_id');
                    foreach ($list as $key => &$value) {
                        // 
                        if(!isset($_list[$value['goods_id']])){
                            $value['price'] = 0;
                        }else{
                            $value['price'] = $_list[$value['goods_id']];
                        }
                        //                         
                    } 
                    $model = new GroupData;
                    $mapRaw = "concat(',',child_member,',') LIKE '%$member_id%'";
                    $__list = $model->where($mapRaw)->where(['create_member' => $pid])->value('data');
                    $__list = json_decode($_list, true);
                    $__list = array_column($_list,'price','goods_id');                                       
                    foreach ($_list as $key => &$value) {
                         // 
                         if(!isset($__list[$value['goods_id']])){
                            $value['price'] = 0;
                        }else{
                            $value['price'] = $value['price']*$__list[$value['goods_id']]['rate']/100;
                        }
                        //                         
                    }
                    $list = $_list;
                    break;
            }
        } else {
            // 未登录
            $list = $this->order('goods_sort asc')->select()->toArray();
        }
        return $list;
    }

    // 获取供应商价格
    public function getProviderData($member_id)
    {        
        $price_data = AdminData::where(['member_id' => $member_id])->value('price_data');
        if (!empty($price_data)) {
            $data = json_decode($price_data, true);
            $data = array_column($data,'price','goods_id');            
            $list = $this->order('goods_sort asc')->select()->toArray();
            foreach ($list as $key => &$value) {
                // 
                if(!isset($data[$value['goods_id']])){
                    $value['price'] = 0;
                }else{
                    $value['price'] = $data[$value['goods_id']];
                }                                
            }            
        } else {
            $list = [];
        }        
        return $list;
    }
}
