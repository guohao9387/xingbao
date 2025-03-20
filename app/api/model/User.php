<?php

namespace app\api\model;

use app\model\Attire;
use app\model\ChuangLan;
use app\model\Examine;
use app\model\Rongyun;
use app\model\Room;
use app\model\RoomVisitor;
use app\model\UserVisitor as ModelUserVisitor;
use app\model\SystemConfig;
use app\model\User as ModelUser;
use app\model\UserAttire;
use app\model\UserBan;
use app\model\UserFollow;
use app\model\UserLevel;
use app\model\UserTransferLog;
use support\Redis;
use think\facade\Db;
use think\Model;


class User extends Model
{
    //用户登录
    public static function login($data)
    {
        $validate = new \app\validate\User;
        if (!$validate->scene('apiLogin')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $map = [];
        $map[] = ['user_name', '=', $data['user_name']];
        $userInfo = ModelUser::where($map)->findOrEmpty();
        if ($userInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '用户名不存在', 'data' => null];
        }

        $keyName = "api:loginErrorNum:" . $userInfo->user_name;
        $loginErrorNum = Redis::get($keyName);

        if ($loginErrorNum >= 10) {
            $ttl = Redis::ttl($keyName);
            return ['code' => 201, 'msg' =>  "账号锁定，请{$$ttl}秒后重试", 'data' => null];
        }
        if (md5($data['password']) != $userInfo->password) {
            Redis::incr($keyName, 1); //登录错误次数+1
            Redis::expire($keyName, 600); //600秒后释放锁定
            return ['code' => 201, 'msg' => '密码错误', 'data' => null];
        };
        Redis::del($keyName);
        return  User::doLogin($userInfo, $data);
    }

    //用户登录
    public static function loginBySmsCode($data)
    {
        $validate = new \app\validate\User;
        if (!$validate->scene('apiLoginBySmsCode')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }


        $map = [];
        $map[] = ['user_name', '=', $data['user_name']];
        $userInfo = ModelUser::where($map)->findOrEmpty();
        if ($userInfo->isEmpty()) {
            $password = generateRandom(32); //随机生成密码

            $result = User::doRegister($data['user_name'], $password, 1);
            if ($result['code'] != 200) {
                return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
            }
            $map = [];
            $map[] = ['user_name', '=', $data['user_name']];
            $userInfo = ModelUser::where($map)->findOrEmpty();
            if ($userInfo->isEmpty()) {
                return ['code' => 201, 'msg' => '登录失败', 'data' => null];
            }
        }
        return  User::doLogin($userInfo, $data);
    }
    //用户登录
    public static function loginByToken($data)
    {
        if (empty($data['token'])) {
            return ['code' => 201, 'msg' => 'token不能为空', 'data' => null];
        }

        if (empty($data['platform_type'])) {
            return json(['code' => 201, 'msg' => '设备类型不能为空', 'data' => null]);
        }
        $result = ChuangLan::getMobileByToken($data['platform_type'], $data['token']);
        if ($result['code'] != 200) {
            return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
        }
        $user_name = $result['data']['mobile'];
        $map = [];
        $map[] = ['user_name', '=', $user_name];
        $userInfo = ModelUser::where($map)->findOrEmpty();
        if ($userInfo->isEmpty()) {
            $password = generateRandom(32); //随机生成密码
            $result = User::doRegister($user_name, $password, 1);
            if ($result['code'] != 200) {
                return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
            }
            $map = [];
            $map[] = ['user_name', '=', $user_name];
            $userInfo = ModelUser::where($map)->findOrEmpty();
            if ($userInfo->isEmpty()) {
                return ['code' => 201, 'msg' => '登录失败', 'data' => null];
            }
        }
        return  User::doLogin($userInfo, $data);
    }

    private static function doLogin($userInfo, $data)
    {
        //判断是否限制登录
        if (empty($data['device_id'])) {
            return ['code' => 201, 'msg' => '登录设备信息不能为空', 'data' => null];
        }
        if ($userInfo['login_status'] == 2) {
            // return ['code' => 201, 'msg' => '账号已限制登录，请联系平台客服', 'data' => null];
        }
        if ($userInfo['account_status'] == 2) {
            return ['code' => 201, 'msg' => '账号不存在', 'data' => null];
        }
        $userBanResult = UserBan::getUserBanStatus($userInfo['uid'], $data['device_id'], $data['request_true_ip']);
        if ($userBanResult['code'] != 200) {
            return ['code' => 201, 'msg' => $userBanResult['msg'], 'data' => null];
        }
        if (empty($userInfo->login_token)) {
            $randString = generateRandom(32);
            $userInfo->login_token = md5($userInfo->uid . $randString);
        }
        //判断融云是否已经注册
        if (empty($userInfo['ry_uid'])) {
            $result = Rongyun::userRegister($userInfo->uid, $userInfo->nick_name, $userInfo->image);
            if ($result['code'] == 200) {
                $userInfo->ry_uid =  $result['userId'];
                $userInfo->ry_token =  $result['token'];
            }
        }
        $userInfo->allowField(['login_token', 'ry_uid', 'ry_token'])->save();
        Redis::set("user:authInfo:" . $userInfo->login_token, json_encode($userInfo), "EX", 365 * 24 * 3600); //一年有效期
        $data = [];

        $data['login_token'] = $userInfo->login_token;
        return ['code' => 200, 'msg' => '登录成功', 'data' => $data];
    }

    //用户注册
    public static function  register($data)
    {
        $validate = new \app\validate\User;
        if (!$validate->scene('apiRegister')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $parentUserInfo = []; //上级用户信息
        if (!empty($data['invite_code'])) {
            $parentUserInfo = ModelUser::where('invite_code', $data['invite_code'])->find();
            if (empty($parentUserInfo)) {
                return ['code' => 201, 'msg' => '邀请码不存在', 'data' => null];
            }
        }
        $result = User::doRegister($data['user_name'], $data['password'], 1);
        if ($result['code'] != 200) {
            return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
        }
        $data = [];
        $data['login_token'] = $result['data']['login_token'];
        return ['code' => 201, 'msg' => '注册成功', 'data' => $data];
    }
    public static function doRegister($user_name, $password, $robot_status)
    {
        $insert_data = [];
        $insert_data['user_name'] = $user_name;
        $insert_data['password'] = $password;
        if (!empty($parentUserInfo)) {
            $insert_data['pid'] = $parentUserInfo->uid;
        }
        $login_token = md5(time() . generateRandom(32));
        $insert_data['login_token'] = $login_token;
        $insert_data['transfer_status'] = 1; //默认有转账权限
        $insert_data['robot_status'] = $robot_status; //是否机器人
        $userInfo = ModelUser::create($insert_data);
        if (!$userInfo) {
            return ['code' => 201, 'msg' => '注册失败', 'data' => null];
        }
        //后续注册
        if ($robot_status == 1) {
            $register_send_coin_num = SystemConfig::getValueByName('register_send_coin_num');
            if (!empty($register_send_coin_num)) {
                ModelUser::changeUserFund($userInfo->uid, 2, 15, $register_send_coin_num, '新用户注册赠送');
            }
        }
        //注册融云

        $result = Rongyun::userRegister($userInfo->uid, $userInfo->nick_name, $userInfo->image);
        if ($result['code'] == 200) {
            $updateData = [];
            $updateData['uid'] = $userInfo->uid;
            $updateData['ry_uid'] = $result['userId'];
            $updateData['ry_token'] = $result['token'];
            ModelUser::update($updateData);

            //推送全服消息
            if ($robot_status == 1) {
                $userData = ModelUser::getUsersData([$userInfo->uid]);
                $pushData = $userData[$userInfo->uid];
                Rongyun::asyncSendMessage("rongyunSendMessageQueue", 'sendToAllRoom', $updateData['ry_uid'], [], 'RC:TxtMsg', 2015, $pushData, 0); //异步发送消息
            }
        }

        return ['code' => 200, 'msg' => '注册失败', 'data' => ['login_token' => $login_token]];
    }

    //退出登录
    public static function logout($authInfo)
    {
        //return ['code' => 200, 'msg' => '退出成功', 'data' => null];
        Redis::del("user:authInfo:" . $authInfo['login_token']); //删除缓存
        $updateData = [];
        $updateData['uid'] = $authInfo['uid'];
        $updateData['login_token'] = md5(time() . generateRandom(32));
        ModelUser::update($updateData);
        return ['code' => 200, 'msg' => '退出成功', 'data' => null];
    }
    //注销账号
    public static function deleteUser($authInfo)
    {
        Redis::del("user:authInfo:" . $authInfo['login_token']); //删除缓存
        $updateData = [];
        $updateData['uid'] = $authInfo['uid'];
        $updateData['nick_name'] = '账号已注销';
        $updateData['user_name'] = $authInfo['user_name'] . "_" . generateRandom(6);
        $updateData['image'] = ModelUser::$deleteUserDefaultImage;
        $updateData['login_token'] = md5(time() . generateRandom(32));
        $updateData['account_status'] = 2; //注销状态
        $result = ModelUser::update($updateData);
        if (!$result) {
            return ['code' => 200, 'msg' => '注销失败，请重试', 'data' => null];
        }
        return ['code' => 200, 'msg' => '注销成功', 'data' => null];
    }

    //用户找回密码
    public static function forgotPassword($data)
    {
        $validate = new \app\validate\User;
        if (!$validate->scene('apiForgotPassword')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $map = [];
        $map[] = ['user_name', '=', $data['user_name']];
        $userInfo = ModelUser::where($map)->findOrEmpty();
        if ($userInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '用户名不存在', 'data' => null];
        }
        $updateData = [];
        $updateData['uid'] = $userInfo->uid;
        $updateData['password'] = $data['password'];
        ModelUser::update($updateData);
        return ['code' => 200, 'msg' => '修改成功', 'data' => null];
    }
    //修改用户信息
    public static function modifyUserInfo($authInfo, $data)
    {

        $data['uid'] = $authInfo['uid'];
        $validate = new \app\validate\User;
        if (!$validate->scene('apiModifyUserInfo')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        ModelUser::update($data);
        //更新融云
        Rongyun::userUpdate($authInfo['ry_uid'], $data['nick_name'], $data['image']);

        $examineContent = Examine::getType1DataStruct($data['nick_name'], $data['image'], $data['autograph']);
        $originalContent = Examine::getType1DataStruct($authInfo['nick_name'], $authInfo['image'], $authInfo['autograph']);
        Examine::createExamine($authInfo['uid'], 1, $authInfo['uid'], 2, $examineContent, $originalContent,); //加入审核
        return ['code' => 200, 'msg' => '修改成功', 'data' => null];
    }

    //实名认证
    public static function userCertification($authInfo, $data)
    {


        $data['uid'] = $authInfo['uid'];
        $validate = new \app\validate\User;
        if (!$validate->scene('apiUserCertification')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }

        if ($authInfo['real_status'] == 2) {
            return ['code' => 201, 'msg' => '您已实名认证', 'data' => null];
        }
        //判断身份证号是否唯一
        $map = [];
        $map[] = ['card_id', '=', $data['card_id']];
        $map[] = ['account_status', '=', 1];
        $userInfo = ModelUser::where($map)->findOrEmpty();

        if (!$userInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '该身份证信息已认证其他账号，如有疑问请联系客服人员', 'data' => null];
        }
        //对接第三方实名认证接口
        //根据身份证号判断是否18岁，18岁以下禁止实名认证
        $birthYear = substr($data['card_id'], 6, 4); // 获取出生年份
        $currentYear = date("Y"); // 获取当前年份
        if ($currentYear - $birthYear < 18) {
            return ['code' => 201, 'msg' => '您未满18周岁，禁止实名认证', 'data' => null];
        }



        //对接第三方实名认证接口
        /*https: //market.aliyun.com/products/57000002/cmapi00037894.html?spm=5176.2020520132.101.2.1b317218sYE3qG#sku=yuncode3189400004*/
        $host = "https://zidv2.market.alicloudapi.com";
        $path = "/idcheck/Post";
        $method = "POST";
        $appcode = "a48fa2384afa47ac918a3089c8b60686";
        $appcode = "8569aff79f31459090bde924e2f79d2e";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type" . ":" . "application/x-www-form-urlencoded; charset=UTF-8");
        $bodys = "cardNo=" . $data['card_id'] . "&realName=" . $data['real_name'];
        $url = $host . $path;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        $content = curl_exec($curl);
        $contentData = json_decode($content, true);
        if (empty($contentData['result'])) {
            return ['code' => 201, 'msg' => '身份证认证失败3', 'data' => null];
        }
        if ($contentData['result']['isok'] != true) {
            return ['code' => 201, 'msg' => '姓名与身份证号不一致', 'data' => null];
        }

        /*
         待对接
         */
        $data['real_status'] = 2; //实名状态更改
        ModelUser::update($data);
        return ['code' => 200, 'msg' => '修改成功', 'data' => null];
    }
    //修改用户登录密码
    public static function modifyPassword($authInfo, $data)
    {
        $data['uid'] = $authInfo['uid'];
        $validate = new \app\validate\User;
        if (!$validate->scene('apiModifyPassword')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        ModelUser::update($data);
        return ['code' => 200, 'msg' => '修改成功', 'data' => null];
    }

    //修改用户交易密码
    public static function modifyTradePassword($authInfo, $data)
    {
        $data['uid'] = $authInfo['uid'];
        $validate = new \app\validate\User;
        if (!$validate->scene('apiModifyTradePassword')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        ModelUser::update($data);
        return ['code' => 200, 'msg' => '修改成功', 'data' => null];
    }
    //修改用户交易密码
    public static function modifyAlipayAccount($authInfo, $data)
    {
        $data['uid'] = $authInfo['uid'];
        $validate = new \app\validate\User;
        if (!$validate->scene('apiModifyAlipayAccount')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        ModelUser::update($data);
        return ['code' => 200, 'msg' => '修改成功', 'data' => null];
    }

    //获取用户基本信息
    public static function getUserInfo($authInfo)
    {
        $userCharmLevelData = UserLevel::getUserCharmLevelData();
        $userContributionLevelData = UserLevel::getUserContributionLevelData();
        $data = [];
        $data['uid'] = $authInfo['uid'];
        $data['user_name'] = $authInfo['user_name'];
        $data['nick_name'] = $authInfo['nick_name'];
        $data['number'] = $authInfo['number'];
        $data['is_special_number'] = $authInfo['is_special_number'];
        $data['image'] = $authInfo['image'];
        $data['sex'] = $authInfo['sex'];
        $data['birthday'] = $authInfo['birthday'];
        $data['money'] = $authInfo['money'];
        $data['integral'] = $authInfo['integral'];
        $data['gift_piece'] = $authInfo['gift_piece'];
        $data['charm_value'] = $authInfo['charm_value'];
        $data['charm_level'] = $authInfo['charm_level'];
        $data['charm_level_icon'] = $userCharmLevelData[$data['charm_level']]['image'];
        $data['contribution_value'] = $authInfo['contribution_value'];
        $data['contribution_level'] = $authInfo['contribution_level'];
        $data['contribution_level_icon'] = $userContributionLevelData[$data['contribution_level']]['image'];
        $data['real_status'] = $authInfo['real_status'];
        $data['follow_num'] = $authInfo['follow_num'];
        $data['fans_num'] = $authInfo['fans_num'];
        $data['room_collect_num'] = $authInfo['room_collect_num'];
        $data['autograph'] = $authInfo['autograph'];
        $data['alipay_real_name'] = $authInfo['alipay_real_name'];
        $data['alipay_account'] = $authInfo['alipay_account'];
        $data['ry_uid'] = $authInfo['ry_uid'];
        $data['ry_token'] = $authInfo['ry_token'];
        $data['room_id'] = $authInfo['room_id'];
        $data['transfer_status'] = $authInfo['transfer_status'];
        //获取用户访客记录总数
        $data['visitor_num'] = ModelUserVisitor::where('see_uid', $authInfo['uid'])->count();
        $data['gift_show_status'] = $authInfo['gift_show_status'];
        $data['rank_show_status'] = $authInfo['rank_show_status'];
        $data['give_self_gift_status'] = $authInfo['give_self_gift_status'];
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }


    //获取其他用户基本信息
    public static function getOtherUserBaseInfo($authInfo, $data)
    {
        $validate = new \app\validate\User;
        if (!$validate->scene('apiGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $userInfo = ModelUser::field("uid,nick_name,image,number,is_special_number,sex,birthday,charm_level,contribution_level,follow_num,fans_num,real_status,autograph,room_id,gift_show_status")->where("uid", $data['uid'])->findOrEmpty();
        if ($userInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '用户信息不存在', 'data' => null];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $userInfo];
    }

    //获取其他用户基本信息
    public static function getOtherUserInfo($authInfo, $data)
    {
        $validate = new \app\validate\User;
        if (!$validate->scene('apiGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $userInfo = ModelUser::field("uid,nick_name,image,number,is_special_number,sex,birthday,charm_level,contribution_level,follow_num,fans_num,real_status,autograph,room_id,gift_show_status")->where("uid", $data['uid'])->findOrEmpty();
        if ($userInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '用户信息不存在', 'data' => null];
        }
        $userCharmLevelData = UserLevel::getUserCharmLevelData();
        $userContributionLevelData = UserLevel::getUserContributionLevelData();
        $userInfo['charm_level_icon'] = $userCharmLevelData[$userInfo['charm_level']]['image'];
        $userInfo['contribution_level_icon'] = $userContributionLevelData[$userInfo['contribution_level']]['image'];


        $map = [];
        $map[] = ['uid', '=', $data['uid']];
        // $map[] = ['type', '=', 4];
        // $map[] = ['use_status', '=', 2];

        $userAttireInfo = UserAttire::where($map)->findOrEmpty();
        $attireData = Attire::getAttireData();
        $headAttrieData = null;
        if (!$userAttireInfo->isEmpty()) {
            if (!empty($attireData[$userAttireInfo['id']])) {
                $headAttrieData['image'] = $attireData[$userAttireInfo['id']]['image']; //麦位框装扮
                $headAttrieData['play_image'] = $attireData[$userAttireInfo['id']]['play_image']; //麦位框装扮
                $headAttrieData['play_type'] = $attireData[$userAttireInfo['id']]['play_type']; //麦位框装扮
            }
        }
        $userInfo['head_attrie_data'] = $headAttrieData; //麦位框装扮

        //插入访客记录
        if ($data['uid'] != $authInfo['uid']) {
            $insertData = [];
            $insertData['uid'] = $authInfo['uid'];
            $insertData['see_uid'] = $userInfo['uid'];
            ModelUserVisitor::create($insertData);
        }
        $returnData = [];
        $returnData['user_info'] = $userInfo;

        //获取用户关注状态

        $map = [];
        $map[] = ['uid', '=', $authInfo['uid']];
        $map[] = ['follow_uid', '=',  $data['uid']];
        $userFollowInfo = UserFollow::where($map)->findOrEmpty();
        $returnData['follow_status'] = 1; //未关注
        if (!$userFollowInfo->isEmpty()) {
            $returnData['follow_status'] = 2; //已关注
        }
        $returnData['room_info'] = null;
        //获取用户当前所在房间
        $map = [];
        $map[] = ['uid', '=', $userInfo['uid']];
        $map[] = ['leave_time', '=',  0];
        $roomVisitorInfo = RoomVisitor::where($map)->findOrEmpty();
        if (!$roomVisitorInfo->isEmpty()) {
            $map = [];
            $map[] = ['rid', '=', $roomVisitorInfo['rid']];
            $returnData['room_info']  = Room::field('rid,cid,tid,room_number,is_special_number,room_name,image,visitor_num,password_status,top_status,is_family,room_status,open_status')->where($map)->find();
        }
        $returnData['owner_room_info']  = null;
        if (!empty($userInfo['room_id'])) {
            $returnData['owner_room_info']  = Room::field('rid,cid,tid,room_number,is_special_number,room_name,image,visitor_num,password_status,top_status,is_family,room_status,open_status')->where('rid', $userInfo['room_id'])->find();
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $returnData];
    }


    //兑换金币
    public static function exchangeMoneyToIntegral($authInfo, $data)
    {
        if (empty($data['money'])) {
            return ['code' => 201, 'msg' => '请输入兑换钻石数量', 'data' => null];
        }
        if (floor($data['money'] != $data['money'])) {
            return ['code' => 201, 'msg' => '兑换钻石数量必须为整数', 'data' => null];
        }
        if (floor($data['money'] <= 0)) {
            return ['code' => 201, 'msg' => '兑换钻石数量必须大于0', 'data' => null];
        }
        if (empty($data['trade_password'])) {
            //return ['code' => 201, 'msg' => '请输入交易密码', 'data' => null];
        }
        if (empty($authInfo['trade_password'])) {
            // return ['code' => 201, 'msg' => '请先设置交易密码后进行操作', 'data' => null];
        }
        if ($authInfo['trade_password'] != md5($data['trade_password'])) {
            //return ['code' => 201, 'msg' => '支付密码不正确', 'data' => null];
        }
        Db::startTrans();
        try {
            //扣除宝石
            $result = ModelUser::changeUserFund($authInfo['uid'], 1, 8, -$data['money'], "兑换扣除钻石");
            if ($result['code'] != 200) {
                Db::rollback();
                return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
            }
            //增加金币
            $result = ModelUser::changeUserFund($authInfo['uid'], 2, 9, $data['money'], "兑换增加金币");
            if ($result['code'] != 200) {
                Db::rollback();
                return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
            }
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['code' => 201, 'msg' => '兑换失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '兑换成功', 'data' => null];
    }

    //兑换礼物碎片
    public static function exchangeIntegralToGiftPiece($authInfo, $data)
    {
        if (empty($data['integral'])) {
            return ['code' => 201, 'msg' => '请输入兑换钻石数量', 'data' => null];
        }
        if (floor($data['integral'] != $data['integral'])) {
            return ['code' => 201, 'msg' => '兑换金币数量必须为整数', 'data' => null];
        }
        if (floor($data['integral'] <= 0)) {
            return ['code' => 201, 'msg' => '兑换金币数量必须大于0', 'data' => null];
        }
        if ($data['integral'] % 10 != 0) {
            return ['code' => 201, 'msg' => '金币数量必须是10的整数倍', 'data' => null];
        }
        if (empty($data['trade_password'])) {
            //return ['code' => 201, 'msg' => '请输入交易密码', 'data' => null];
        }
        if (empty($authInfo['trade_password'])) {
            // return ['code' => 201, 'msg' => '请先设置交易密码后进行操作', 'data' => null];
        }
        if ($authInfo['trade_password'] != md5($data['trade_password'])) {
            //return ['code' => 201, 'msg' => '支付密码不正确', 'data' => null];
        }
        Db::startTrans();
        try {
            //扣除金币
            $result = ModelUser::changeUserFund($authInfo['uid'], 2, 21, -$data['integral'], "兑换扣除金币");
            if ($result['code'] != 200) {
                Db::rollback();
                return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
            }
            //增加礼物碎片
            $result = ModelUser::changeUserFund($authInfo['uid'], 3, 22, $data['integral'] / 10, "兑换增加礼物碎片");
            if ($result['code'] != 200) {
                Db::rollback();
                return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
            }
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['code' => 201, 'msg' => '兑换失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '兑换成功', 'data' => null];
    }


    //兑换金币
    public static function giveIntegral($authInfo, $data)
    {
        if ($authInfo['test_status'] == 2) {
            return ['code' => 201, 'msg' => '停用赠送功能', 'data' => null];
        }
        if (empty($data['value'])) {
            return ['code' => 201, 'msg' => '请输入赠送金币数量', 'data' => null];
        }
        if (floor($data['value'] != $data['value'])) {
            return ['code' => 201, 'msg' => '赠送金币数量必须为整数', 'data' => null];
        }
        if (floor($data['value'] <= 0)) {
            return ['code' => 201, 'msg' => '赠送金币数量必须大于0', 'data' => null];
        }
        if (empty($data['trade_password'])) {
            //return ['code' => 201, 'msg' => '请输入交易密码', 'data' => null];
        }
        if ($authInfo['transfer_status'] != 2) {
            return ['code' => 201, 'msg' => '暂无该操作权限', 'data' => null];
        }
        if (empty($authInfo['trade_password'])) {
            //return ['code' => 201, 'msg' => '请先设置交易密码后进行操作', 'data' => null];
        }
        if ($authInfo['trade_password'] != md5($data['trade_password'])) {
            //return ['code' => 201, 'msg' => '支付密码不正确', 'data' => null];
        }
        if (empty($data['uid'])) {
            return ['code' => 201, 'msg' => '受赠人信息不存在', 'data' => null];
        }
        $reciveUserInfo = ModelUser::where('uid', $data['uid'])->findOrEmpty();
        if ($reciveUserInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '受赠人信息不存在', 'data' => null];
        }

        Db::startTrans();
        try {
            //扣除宝石
            $result = ModelUser::changeUserFund($authInfo['uid'], 2, 10, -$data['value'], "金币赠送");
            if ($result['code'] != 200) {
                Db::rollback();
                return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
            }
            //增加金币
            $result = ModelUser::changeUserFund($data['uid'], 2, 11, $data['value'], "收到金币赠送");
            if ($result['code'] != 200) {
                Db::rollback();
                return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
            }
            //增加转增记录
            $createData = [];
            $createData['uid'] = $authInfo['uid'];
            $createData['receive_uid'] = $reciveUserInfo['uid'];
            $createData['value'] = $data['value'];
            UserTransferLog::create($createData);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['code' => 201, 'msg' => '赠送失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '赠送成功', 'data' => null];
    }




    //获取用户邀请码信息
    public static function getUserInviteCodeInfo($authInfo)
    {
        $data = [];
        $web_site_url = SystemConfig::getValueByName('web_site_url');
        $data['invite_url'] = $web_site_url . '?invite_code=' . $authInfo['invite_code'];
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }


    public static function getUserImSendLimitStatus($authInfo)
    {

        $send_message_min_contribution_value = SystemConfig::getValueByName('send_message_min_contribution_value');
        $returnData = [];
        if ($authInfo['contribution_value'] < $send_message_min_contribution_value) {
            $returnData['limit_status'] = 1;
            $returnData['msg'] = '贡献值达到' . $send_message_min_contribution_value . '开启此功能';
        } else {
            $returnData['limit_status'] = 2;
            $returnData['msg'] = '已获取权限';
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $returnData];
    }
}
