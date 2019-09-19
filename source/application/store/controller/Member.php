<?php

namespace app\store\controller;

use app\store\model\Member as MemberModel;

/**
 * 人员管理
 * Class Member
 * @package app\store\controller
 */
class Member extends Controller
{
    public function index(){
        $model = new MemberModel;
        $list = $model->getList();        
        return $this->fetch('index',compact('list'));
    }


    /**
     * 添加经销商
     */
    public function add(){
        if (!$this->request->isAjax()) {            
            return $this->fetch('add');
        }
        $model = new MemberModel;
        if ($model->add($this->postData('member'))) {
            return $this->renderSuccess('添加成功', url('member/index'));
        }
        $error = $model->getError() ?: '添加失败';
        return $this->renderError($error);
    }

    public function resetPass($id,$password){
        $model = new MemberModel;
        $model->where(['member_id'=>$id])->update(['password'=>yoshop_hash($password)]);
        return $this->renderSuccess('修改成功', url('member/index'));        
    }
}
