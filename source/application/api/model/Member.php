<?php

namespace app\api\model;

use app\common\exception\BaseException;
//use app\api\model\Wxapp;
use app\common\library\wechat\WxUser;
use app\common\model\GroupData;
use app\common\model\Member as MemberModel;
use think\Cache;
use think\Db;

/**
 * renyuan模型类
 * Class User
 * @package app\api\model
 */
class Member extends MemberModel
{
    private $token;

    public $error;

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
    ];

    /**
     * 获取用户信息
     * @param $token
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function getMember($token)
    {
        return self::get(['member_id' => Cache::get($token)['member_id']])->toArray();
    }

    /**
     * 用户登录
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login($post)
    {
        $obj = $this->where([
            'username' => $post['account'],
        ])->find();

        if ($obj) {
            if ($obj['password'] == yoshop_hash($post['password'])) {
                // 生成token
                $this->token = $this->token($obj['member_id']);
                // 记录缓存, 7天
                Cache::set($this->token, $obj, 86400 * 7);
                return $obj['member_id'];
            } else {
                return -1;
            }
        } else {
            return 0;
        }
    }

    /**
     * 获取token
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 微信登录
     * @param $code
     * @return array|mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function wxlogin($code)
    {
        // 获取当前小程序信息
        $wxapp = Wxapp::detail();
        // 微信登录 (获取session_key)
        $WxUser = new WxUser($wxapp['app_id'], $wxapp['app_secret']);
        if (!$session = $WxUser->sessionKey($code)) {
            throw new BaseException(['msg' => 'session_key 获取失败']);
        }

        return $session;
    }

    /**
     * 生成用户认证的token
     * @param $openid
     * @return string
     */
    private function token($openid)
    {
        return md5($openid . self::$wxapp_id . 'token_salt');
    }

    /**
     * 自动注册用户
     * @param $open_id
     * @param $userInfo
     * @return mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function register($open_id, $userInfo)
    {
        if (!$user = self::get(['open_id' => $open_id])) {
            $user = $this;
            $userInfo['open_id'] = $open_id;
            $userInfo['wxapp_id'] = self::$wxapp_id;
        }
        $userInfo['nickName'] = preg_replace('/[\xf0-\xf7].{3}/', '', $userInfo['nickName']);
        if (!$user->allowField(true)->save($userInfo)) {
            throw new BaseException(['msg' => '用户注册失败']);
        }
        return $user['user_id'];
    }

    public function addChildMember($post, $member)
    {

        if (!isset($post['member_id'])) {
            // 新增
            $m_obj = $this->where(['username' => $post['username']])->find();
            if ($m_obj) {
                $this->error = '该用户名已存在';
                return false;
            }
        } else {
            // 修改
            $m_obj = $this->where(['member_id' => $post['member_id']])->find();
        }
        //
        Db::startTrans();
        try {
            if (!isset($post['member_id'])) {
                // 新增
                $this->allowField(true)->save([
                    'manager' => $post['manager'],
                    'username' => $post['username'],
                    'password' => yoshop_hash($post['password']),
                    'type' => $member['type'] == 0 ? 1 : 2,
                    'pid' => $member['member_id'],
                    'status' => 1,
                ]);
                // group_data  child_member
                $group_model = new GroupData;
                $group_obj = $group_model->where(['group_id' => $post['group_id']])->find();
                //
                if (!empty($group_obj['child_member'])) {
                    $child_member = explode(',', $group_obj['child_member']);
                    $child_member[] = $this->member_id;
                    $child_member = implode(',', $child_member);
                } else {
                    $child_member = $this->member_id;
                }
                $group_obj->save([
                    'child_member' => $child_member,
                ]);
            } else {
                // 修改
                if ($post['password'] == '#origin#') {
                    unset($post['password']);
                }else{
                    $post['password'] = yoshop_hash($post['password']);
                }
                $m_obj->allowField(true)->save($post);
                // 修改分组情况                
                $group_model = new GroupData;
                $group_obj = $group_model->where(['group_id' => $post['group_id']])->find();
                $member_id = $post['member_id'];
                $mapRaw = "concat(',',child_member,',') LIKE '%$member_id%'";
                $group_old_obj = $group_model->where($mapRaw)->find();
                if($group_obj['group_id']!=$group_old_obj['group_id']){
                    $old_child_member = $group_old_obj['child_member'];
                    $old_child_member = explode(',',$old_child_member);
                    foreach ($old_child_member as $key => $value) {
                        if($value==$post['member_id']){
                            unset($old_child_member[$key]);
                        }
                    }
                    $old_child_member = implode(',',$old_child_member);
                    $group_old_obj->save(['child_member'=>$old_child_member]);
                    // 
                    if (!empty($group_obj['child_member'])) {
                        $child_member = explode(',', $group_obj['child_member']);
                        $child_member[] = $post['member_id'];
                        $child_member = implode(',', $child_member);
                    } else {
                        $child_member = $post['member_id'];
                    }
                    $group_obj->save([
                        'child_member' => $child_member,
                    ]);
                }
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
        }
        return false;

    }

    public function getChildInfo($member_id)
    {
        // memberInfo
        $detail = $this->where(['member_id' => $member_id])->find();
        // group_id
        $model = new GroupData;
        $mapRaw = "concat(',',child_member,',') LIKE '%$member_id%'";
        $group_id = $model->where($mapRaw)->value('group_id');
        return compact('detail', 'group_id');
    }



    // 
    public function deleteChild($member_id,$parent_id){        
        //
        Db::startTrans();
        try {
            $obj = $this->where(['member_id'=>$member_id,'pid'=>$parent_id])->find();
            if(!$obj){
                $this->error = '上下级不匹配';
                return false;
            }
            // member
            $this->where(['member_id'=>$member_id])->delete();
            // group_data
            $group_model = new GroupData;
            $mapRaw = "concat(',',child_member,',') LIKE '%$member_id%'";
            $group_obj = $group_model->where($mapRaw)->find();
            $child_member = explode(',',$group_obj['child_member']);
            foreach ($child_member as $key => $value) {
                if($value==$member_id){
                    unset($child_member[$key]);
                }
            }
            $child_member = implode(',',$child_member);
            $group_obj->save(['child_member'=>$child_member]);
            // 
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
        }
        return false;
    }



    // 
    public function editPassword($post,$member_id){
        //
        Db::startTrans();
        try {
            $pass = $this->where(['member_id'=>$member_id])->value('password');            
            if($pass!==yoshop_hash($post['password'])){
                $this->error ='旧密码错误';
                return false;
            }
            $this->where(['member_id'=>$member_id])->update([
                'password'=>yoshop_hash($post['new_password'])
            ]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
        }
        return false;
    }

}
