<?php

namespace app\api\controller;

use app\api\model\User as ModelUser;
use app\model\Sms;
use support\Request;

class User
{
    public function login(Request $request)
    {
        $data = $request->only(['user_name', 'password', 'device_id']);
        if (empty($data['user_name'])) {
            return encryptedJson(['code' => 201, 'msg' => '请输入手机号码', 'data' => null]);
        }
        $keyName = 'api:User:login:' . $data['user_name'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $data['request_true_ip'] = $request->getRealIp(true);
        $result = ModelUser::login($data);
        redisUnlock($keyName);
        return encryptedJson($result);
    }

    public function loginBySmsCode(Request $request)
    {
        $data = $request->only(['user_name', 'sms_code', 'device_id']);
        if (empty($data['user_name'])) {
            return encryptedJson(['code' => 201, 'msg' => '请输入手机号码', 'data' => null]);
        }
        if (empty($data['sms_code'])) {
            return encryptedJson(['code' => 201, 'msg' => '短信验证码不能为空', 'data' => null]);
        }
        $result = Sms::validateCode($data['user_name'], $data['sms_code']);
        if ($result['code'] != 200) {
            return encryptedJson($result);
        }
        $keyName = 'api:User:loginBySmsCode:' . $data['user_name'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $data['request_true_ip'] = $request->getRealIp(true);
        $result = ModelUser::loginBySmsCode($data);
        redisUnlock($keyName);
        return encryptedJson($result);
    }
    public function loginByToken(Request $request)
    {
        $data = $request->only(['token', 'device_id', 'platform_type']);
        if (empty($data['token'])) {
            return encryptedJson(['code' => 201, 'msg' => '验证参数不能为空', 'data' => null]);
        }
        $keyName = 'api:User:loginByToken:' . $data['token'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $data['request_true_ip'] = $request->getRealIp(true);
        $result = ModelUser::loginByToken($data);
        redisUnlock($keyName);
        return encryptedJson($result);
    }
    public function register(Request $request)
    {
        $data = $request->only(['user_name', 'password', 'sms_code', 'invite_code']);
        if (empty($data['user_name'])) {
            return encryptedJson(['code' => 201, 'msg' => '请输入手机号码', 'data' => null]);
        }
        if (empty($data['sms_code'])) {
            return encryptedJson(['code' => 201, 'msg' => '短信验证码不能为空', 'data' => null]);
        }
        $result = Sms::validateCode($data['user_name'], $data['sms_code']);
        if ($result['code'] != 200) {
            return encryptedJson($result);
        }
        $keyName = 'api:User:register:' . $data['user_name'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelUser::register($data);
        redisUnlock($keyName);
        return encryptedJson($result);
    }
    //退出登录
    public function logout(Request $request)
    {
        $result = ModelUser::logout($request->authInfo);
        return encryptedJson($result);
    }
    //注销账号
    public function deleteUser(Request $request)
    {
        $authInfo = $request->authInfo;
        $data = $request->only(['sms_code']);
        if (empty($data['sms_code'])) {
            return encryptedJson(['code' => 201, 'msg' => '验证码不能为空', 'data' => null]);
        }
        $result = Sms::validateCode($authInfo['user_name'], $data['sms_code']);
        if ($result['code'] != 200) {
            return encryptedJson($result);
        }
        $result = ModelUser::deleteUser($authInfo, $data);
        return encryptedJson($result);
    }


    public function forgotPassword(Request $request)
    {
        $data = $request->only(['user_name', 'password', 'sms_code']);
        $result = Sms::validateCode($data['user_name'], $data['sms_code']);
        if ($result['code'] != 200) {
            return encryptedJson($result);
        }
        $result = ModelUser::forgotPassword($data);
        return encryptedJson($result);
    }
    //修改用户登录密码
    public function modifyPassword(Request $request)
    {
        $authInfo = $request->authInfo;
        $data = $request->only(['password', 'sms_code']);
        if (empty($data['sms_code'])) {
            return encryptedJson(['code' => 201, 'msg' => '验证码不能为空', 'data' => null]);
        }
        $result = Sms::validateCode($authInfo['user_name'], $data['sms_code']);
        if ($result['code'] != 200) {
            return encryptedJson($result);
        }
        $result = ModelUser::modifyPassword($authInfo, $data);
        return encryptedJson($result);
    }
    //修改用户交易密码
    public function modifyTradePassword(Request $request)
    {
        $authInfo = $request->authInfo;
        $data = $request->only(['trade_password', 'sms_code']);
        if (empty($data['sms_code'])) {
            return encryptedJson(['code' => 201, 'msg' => '验证码不能为空', 'data' => null]);
        }
        $result = Sms::validateCode($authInfo['user_name'], $data['sms_code']);
        if ($result['code'] != 200) {
            return encryptedJson($result);
        }
        $result = ModelUser::modifyTradePassword($authInfo, $data);
        return encryptedJson($result);
    }


    //修改用户绑定支付宝账号
    public function modifyAlipayAccount(Request $request)
    {
        $authInfo = $request->authInfo;
        $data = $request->only(['alipay_real_name', 'alipay_account', 'sms_code']);
        if (empty($data['sms_code'])) {
            return encryptedJson(['code' => 201, 'msg' => '验证码不能为空', 'data' => null]);
        }
        $result = Sms::validateCode($authInfo['user_name'], $data['sms_code']);
        if ($result['code'] != 200) {
            return encryptedJson($result);
        }
        $result = ModelUser::modifyAlipayAccount($authInfo, $data);
        return encryptedJson($result);
    }


    //修改用户详情
    public function modifyUserInfo(Request $request)
    {
        $data = $request->only(['nick_name', 'image', 'sex', 'birthday', 'autograph', 'gift_show_status', 'rank_show_status']);
        $result = ModelUser::modifyUserInfo($request->authInfo, $data);
        return encryptedJson($result);
    }
    //获取个人信息
    public function getUserInfo(Request $request)
    {
        $result = ModelUser::getUserInfo($request->authInfo);
        return encryptedJson($result);
    }



    //获取邀请信息
    public function getUserInviteCodeInfo(Request $request)
    {
        $result = ModelUser::getUserInviteCodeInfo($request->authInfo);
        return encryptedJson($result);
    }
    //用户实名认证
    public function userCertification(Request $request)
    {
        $data = $request->only(['real_name', 'card_id', 'card_image_z', 'card_image_f']);
        $result = ModelUser::userCertification($request->authInfo, $data);
        return encryptedJson($result);
    }
    //查看其他用户基本信息
    public function getOtherUserBaseInfo(Request $request)
    {
        $data = $request->only(['uid']);
        $result = ModelUser::getOtherUserBaseInfo($request->authInfo, $data);
        return encryptedJson($result);
    }

    //查看其他用户信息
    public function getOtherUserInfo(Request $request)
    {
        $data = $request->only(['uid']);
        $result = ModelUser::getOtherUserInfo($request->authInfo, $data);
        return encryptedJson($result);
    }

    //钻石兑换金币
    public function exchangeMoneyToIntegral(Request $request)
    {
        $data = $request->only(['money', 'trade_password']);
        $keyName = 'api:User:exchangeMoneyToIntegral:' . $request->authInfo['uid'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelUser::exchangeMoneyToIntegral($request->authInfo, $data);
        redisUnlock($keyName);
        return encryptedJson($result);
    }
    //金币兑换礼物碎片
    public function exchangeIntegralToGiftPiece(Request $request)
    {
        $data = $request->only(['integral', 'trade_password']);
        $keyName = 'api:User:exchangeIntegralToGiftPiece:' . $request->authInfo['uid'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelUser::exchangeIntegralToGiftPiece($request->authInfo, $data);
        redisUnlock($keyName);
        return encryptedJson($result);
    }
    //赠送金币
    public function giveIntegral(Request $request)
    {
        $data = $request->only(['value', 'uid', 'trade_password']);
        $keyName = 'api:User:giveIntegral:' . $request->authInfo['uid'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelUser::giveIntegral($request->authInfo, $data);
        redisUnlock($keyName);
        return encryptedJson($result);
    }
    //获取用户IM发送限制状态
    public function getUserImSendLimitStatus(Request $request)
    {
        $keyName = 'api:User:getUserImSendLimitStatus:' . $request->authInfo['uid'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelUser::getUserImSendLimitStatus($request->authInfo);
        redisUnlock($keyName);
        return encryptedJson($result);
    }
}
