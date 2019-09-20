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
                    $admin_id = Member::where(['username'=>'admin'])->value('member_id');
                    $data = AdminData::where(['member_id' => $admin_id])->value('price_data');                    
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
                    $__list = json_decode($__list, true);
                    $__list = array_column($__list,null,'goods_id');                                       
                    foreach ($list as $key => &$value) {  
                        if(!isset($__list[$value['goods_id']])){
                            
                        }else{
                            $value['price'] = bcdiv($value['price']*$__list[$value['goods_id']]['rate'],100,2);
                        }                                                                      
                    }                    
                    break;
                case 2:
                    # 外部经销商
                    // 获取供应商数据
                    $admin_id = Member::where(['username'=>'admin'])->value('member_id');
                    $data = AdminData::where(['member_id' => $admin_id])->value('price_data');                    
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
                    // 获取内部经销 数据
                    $model = new GroupData;
                    $mapRaw = "concat(',',child_member,',') LIKE '%$pid%'";
                    $__list = $model->where($mapRaw)->where(['create_member' => $admin_id])->value('data');
                    $__list = json_decode($__list, true);
                    $__list = array_column($__list,null,'goods_id');                                       
                    foreach ($list as $key => &$value) {  
                        if(!isset($__list[$value['goods_id']])){
                            
                        }else{
                            $value['price'] = bcdiv($value['price']*$__list[$value['goods_id']]['rate'],100,2);
                        }                                                                      
                    }
                    // 获取自己（外部）数据
                    $_mapRaw = "concat(',',child_member,',') LIKE '%$member_id%'";
                    $___list = $model->where($_mapRaw)->where(['create_member' => $pid])->value('data');
                    $___list = json_decode($___list, true);
                    $___list = array_column($___list,null,'goods_id');                                       
                    foreach ($list as $key => &$value) {  
                        if(!isset($___list[$value['goods_id']])){
                            
                        }else{
                            $value['price'] = bcdiv($value['price']*$___list[$value['goods_id']]['rate'],100,2);
                        }                                                                      
                    }
                    
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
