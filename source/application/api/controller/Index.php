<?php

namespace app\api\controller;

use app\api\model\Goods as GoodsModel;
use app\api\model\Member as MemberModel;
use app\api\model\WxappPage;

/**
 * 首页控制器
 * Class Index
 * @package app\api\controller
 */
class Index extends Controller
{

    public function page()
    {
        // 页面元素
        $wxappPage = WxappPage::detail();
        $items = $wxappPage['page_data']['array']['items'];
        // 新品推荐
        $model = new GoodsModel;
        $newest = $model->getNewList();
        // 猜您喜欢
        $best = $model->getBestList();
        return $this->renderSuccess(compact('items', 'newest', 'best'));
    }

    public function index()
    {
        $member = $this->getMember();        
        if(!$member){
            return $this->renderError('用户不存在');
        }
        $model = new GoodsModel;
        $goods = $model->getIndexList($member['member_id']);
        $has_login = $member ? 1 : 0;
        $member = $member? $member : null;
        return $this->renderSuccess(compact('goods','has_login','member'));
    }

    public function login()
    {
        $model = new MemberModel;
        $member_id = $model->login($this->request->post());
        if ($member_id == -1) {
            // password invalid
            return $this->renderError('密码错误');
        } else if ($member_id == 0) {
            // users not found
            return $this->renderError('用户名不存在');
        } else {
            $token = $model->getToken();
            return $this->renderSuccess(compact('member_id', 'token'));
        }
    }



    // 供应商价格管理
    public function getAdminPrice(){
        $member = $this->getMember();        
        if(!$member){
            return $this->renderError('用户不存在');
        }else{
            if($member['type']!=0){
                return $this->renderError('用户非供应商');
            }else{
                $model = new GoodsModel;
                $data = $model->getProviderData($member['member_id']);
                return $this->renderSuccess(compact('data'));
            }
        }
    }

}
