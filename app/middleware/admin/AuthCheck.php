<?php

namespace app\middleware\admin;

use app\model\Admin;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Redis;

class AuthCheck implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if (!$this->isDotNeedLogin($request->path())) {
            return $handler($request);
        }
        $loginToken = $request->header('login-token');
        if (empty($loginToken)) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode(['code' => 401, 'msg' => '登录失效', 'data' => null]));
        }
        $authInfo = Redis::get("admin:authInfo:" . $loginToken);
        //重连
        $adminInfo = Admin::where('login_token', $loginToken)->findOrEmpty();
        if ($adminInfo->isEmpty()) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode(['code' => 401, 'msg' => '登录失效', 'data' => null]));
        }
        $authInfo = json_encode($adminInfo);
        Redis::set("admin:authInfo:" . $adminInfo->login_token, json_encode($adminInfo), "EX", 365 * 24 * 3600); //一年有效期
        $authInfo = json_decode($authInfo, true);
        $request->authInfo = $authInfo;
        $request->authId = $authInfo['aid'];
        $request->authIdentity = 2;

        return $handler($request);
    }
    private function isDotNeedLogin($requestUrl)
    {
        $data = [];
        $data[] = '/admin/Admin/login'; //管理员登录
        $data[] = '/admin/Admin/captcha'; //获取图形验证码
        $data[] = '/admin/Rongyun/syncUserOnlineStatus'; //订阅用户在线状态https://mengdong.xyyyapp.com
        $data[] = '/admin/Rongyun/syncRoomStatus'; //聊天室房间状态同步
        $data[] = '/admin/Rongyun/syncMessageData'; //融云消息同步
        //$data[] = '/admin/Cli/syncAllUserUserOnlineStatus'; //用户在线状态全量手动修正检查同步
        $data[] = '/admin/Cli/syncRoomVisitorNum'; //房间访客数量等状态手动修正同步
        $data[] = '/admin/Cli/rollbackRedPacket'; //定期回退未发放红包
        $data[] = '/admin/Zego/syncRoomLogin'; //即构登录房间同步
        $data[] = '/admin/Zego/syncRoomLogout'; //即构退出房间同步
        //$data[] = '/admin/Cli/updateUserRongyunToken'; //同步注册融云

        $data[] = '/admin/Admin/sendCode'; //后台登录发送短信
        $data[] = '/admin/Admin/loginBySmsCode'; //后台手机号登录接口


        if (in_array($requestUrl, $data)) {
            return false;
        }
        return true;
    }
}
