<?php

namespace app\middleware\api;

use app\model\User;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Redis;

class AuthCheck implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {

        //return new Response(403, ['Content-Type' => 'application/json'], json_encode(['code' => 403, 'msg' => '系统维护中', 'data' => null]));
        if (!$this->isDotNeedLogin($request->path())) {
            return $handler($request);
        }
        $loginToken = $request->header('login-token');
        if (empty($loginToken)) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode(['code' => 401, 'msg' => '登录失效', 'data' => null]));
        }

        $authInfo = Redis::get("user:authInfo:" . $loginToken);
        if (empty($authInfo) || 1) {
            //重连
            $userInfo = User::where('login_token', $loginToken)->findOrEmpty();
            if ($userInfo->isEmpty()) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode(['code' => 401, 'msg' => '登录失效', 'data' => null]));
            }

            $authInfo = json_encode($userInfo);
            Redis::set("user:authInfo:" . $userInfo->login_token, json_encode($userInfo), "EX", 365 * 24 * 3600); //一年有效期
        }

        // $authInfo = User::where('uid', 1000774)->findOrEmpty();
        //return new Response(403, ['Content-Type' => 'application/json'], json_encode(['code' => 403, 'msg' => '系统维护中', 'data' => null]));
        $authInfo = json_decode($authInfo, true);


        $request->authInfo = $authInfo;
        $request->authId = $authInfo['uid'];
        $request->authIdentity = 2;
        return $handler($request);
    }
    private function isDotNeedLogin($requestUrl)
    {
        $data = [];
        $data[] = '/api/User/login'; //用户登录
        $data[] = '/api/User/register'; //用户注册
        $data[] = '/api/User/forgotPassword'; //用户找回密码
        $data[] = '/api/Sms/sendCodeByPhone'; //发送短信验证码
        $data[] = '/api/Captcha/captcha'; //获取图片验证码
        $data[] = '/api/User/loginBySmsCode'; //根据验证码登录
        $data[] = '/api/Pay/aliPayNotify'; //阿里云回调通知
        $data[] = '/api/Pay/wxPayNotify'; //微信支付回调通知
        $data[] = '/api/Page/getPageInfo'; //文章详情


        if (in_array($requestUrl, $data)) {
            return false;
        }
        return true;
    }
}
