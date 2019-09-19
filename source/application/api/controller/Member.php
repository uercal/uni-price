<?php

namespace app\api\controller;

use app\api\model\Member as MemberModel;
use app\common\model\AdminData;
use app\common\model\GroupData;

/**
 * 用户管理
 * Class Member
 * @package app\api
 */
class Member extends Controller
{
    // 修改供应商商品价格
    public function changeAdata(){
        $model = new AdminData;
        $member = $this->getMember();
        if(!$member){
            return $this->renderError('用户不存在');
        }
        $res = $model->changeData($this->request->post(),$member['member_id']);
        if($res){
            return $this->renderSuccess();
        }else{
            return $this->renderError($model->error);
        }
    }



    // 获取分组列表信息
    public function getGroupList(){
        $model = new GroupData;
        $member = $this->getMember();
        if(!$member){
            return $this->renderError('用户不存在');
        }
        $group_list = $model->getGroupList($member['member_id']);
        return $this->renderSuccess($group_list);
    }


    // 保存分组信息
    public function saveGroup(){
        $model = new GroupData;
        $member = $this->getMember();
        if(!$member){
            return $this->renderError('用户不存在');
        }
        if($model->saveInfo($this->request->post(),$member['member_id'])){
            return $this->renderSuccess();
        }else{
            return $this->renderError($model->error);
        }
    }


    // 获取分组单个信息
    public function getGroupInfo(){
        $model = new GroupData;
        $group_id = input('group_id');
        $info = $model->getInfo($group_id);
        return $this->renderSuccess($info);
    }


    // 增加下级
    public function addChild(){
        $model = new MemberModel;
        $member = $this->getMember();
        if(!$member){
            return $this->renderError('用户不存在');
        }
        if($model->addChildMember($this->request->post(),$member)){
            return $this->renderSuccess();
        }else{
            return $this->renderError($model->error);
        }
    }


    public function getChild($member_id){
        $model = new MemberModel;
        $data = $model->getChildInfo($member_id);
        return $this->renderSuccess($data);
    }

    

    // 删除下级
    public function deleteChild($member_id){
        $parent = $this->getMember();
        if(!$parent){
            return $this->renderError('请重新登录');
        }
        $model = new MemberModel;
        if($model->deleteChild($member_id,$parent['member_id'])){
            return $this->renderSuccess();
        }else{
            return $this->renderError($model->error);
        }
    }


    // 修改密码
    public function resetPass(){
        $member = $this->getMember();
        if(!$member){
            return $this->renderError('请重新登录');
        }
        $model = new MemberModel;
        if($model->editPassword($this->request->post(),$member['member_id'])){
            return $this->renderSuccess();
        }else{
            return $this->renderError($model->error);
        }    
    }

}
