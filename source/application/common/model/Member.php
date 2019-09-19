<?php

namespace app\common\model;

/**
 * 成员模型
 * Class Member
 * @package app\common\model
 */
class Member extends BaseModel
{
    protected $name = 'member';
    protected $updateTime = false;
    protected $append = ['status_text', 'type_text'];

    // 关联
    public function parent()
    {
        return $this->hasOne('Member', 'member_id', 'pid');
    }

    // text
    public function getStatusTextAttr($value, $data)
    {
        return $data['status'] == 0 ? '禁用' : '启用';
    }

    public function getTypeTextAttr($value, $data)
    {        
        switch ($data['type']) {
            case 0:                
                $name = '供应商';
                break;
            case 1:                
                $name = '内部供应商';
                break;
            case 2:                
                $name = '外部供应商';
                break;
        }
        return $name;
    }

    // methods

}
