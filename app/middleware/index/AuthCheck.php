<?php

namespace app\middleware\index;

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
        // dump($request->path());
        if (!$this->isDotNeedLogin($request->path())) {
            // dump(1);
            return $handler($request);
        }else{
            $uid=$request->session()->get('uid');
            if(!$uid){
                if($request->method() == 'GET'){
                // dump(2);
                    return redirect('/index/Login/login');
                }
                if($request->method() == 'POST'){
                // dump(3);
                    return new Response(401, ['Content-Type' => 'application/json'], json_encode(['code' => 401, 'msg' => '请先登录', 'data' => null]));
                }
            }
            // dump(4);
    
            return $handler($request);
        }
    }
    private function isDotNeedLogin($requestUrl)
    {
        $requestUrl = strtolower($requestUrl);
        $data = [];
        $data[] = '/index/login/login'; //用户登录
        $data[] = '/index/index/index'; //首页
        $data[] = '/index/index/err_msg'; //首页
        $data[] = '/index/index/sys_article'; //首页
        $data[] = '/index/index/sys_message'; //首页
        $data[] = '/index/index/sys_message_list'; //首页
        $data[] = '/index/index/sys_page_list'; //首页
        $data[] = '/index/index/search'; //首页
        $data[] = '/index/user/login'; //用户登录
        $data[] = '/index/user/register'; //用户注册
        $data[] = '/index/user/logout'; //退出登录
        $data[] = '/index/buy/buy'; //我要买
        $data[] = '/index/sell/sell'; //我要买
        $data[] = '/index/buy/game_account_info'; //我要买
        $data[] = '/index/buy/game_account_list'; //我要买
        $data[] = '/index/buy/game_account_collection'; //我要买
        $data[] = '/index/buy/buy_now'; //我要买
        // $data[] = '/index/user/forgotpassword'; //用户找回密码
        $data[] = '/index/sms/sendcodebyphone'; //发送短信验证码
        $data[] = '/index/captcha/captcha'; //获取图片验证码
        $data[] = '/index/user/loginbysmscode'; //根据验证码登录

        $data[] = '/index/pay/alipaynotify'; //阿里云回调通知
        $data[] = '/index/pay/wxpaynotify'; //微信支付回调通知


        if (in_array($requestUrl, $data)) {
            return false;
        }
        return true;
    }
}
