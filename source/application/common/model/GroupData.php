<?php

namespace app\common\model;

use app\common\model\Member;
use app\common\model\Goods;
use think\Cache;
use think\Db;
/**
 * 分组价格模型
 * Class Category
 * @package app\common\model
 */
class GroupData extends BaseModel
{
    protected $name = 'group_data';
    protected $updateTime = false;
    protected $append = ['child_member_list'];
    public $error;


    // 模型关联
    public function getChildMemberListAttr($value,$data){
        if(!empty($data['child_member'])){
            $member_ids = explode(',',$data['child_member']);
            $model = new Member;
            $list = $model->whereIn('member_id',$member_ids)->where(['pid'=>$data['create_member']])->select()->toArray();
            return $list;
        }else{
            return [];
        }
    }









    // 
    public function getGroupList($member_id){
        return $this->where(['create_member'=>$member_id])->select()->toArray();
    }



    // 
    public function getInfo($group_id){
        $info = $this->where(['group_id'=>$group_id])->find()->toArray();
        $info['data'] = json_decode($info['data'],true);
        return $info;               
    }


    // 保存分组价格
    public function saveInfo($post,$member_id){
        // 
        $goods = $post['goods_id'];        
        $data = [];
        foreach ($goods as $key => $value) {
            $arr = explode(',',$value);
            $data[] = [
                'goods_id'=>$key,
                'price'=>$arr[0],
                'rate'=>$arr[1]
            ];
        }        
        // 
        Db::startTrans();
        try {   
            if(isset($post['group_id'])){
                $this->where(['group_id'=>$post['group_id']])->update([
                    'group_name'=>$post['group_name'],
                    'data'=>json_encode($data)                    
                ]);
            }else{
                $this->allowField(true)->save([
                    'group_name'=>$post['group_name'],
                    'data'=>json_encode($data),
                    'create_member'=>$member_id
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
