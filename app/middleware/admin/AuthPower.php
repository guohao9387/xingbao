<?php

namespace app\middleware\admin;


use app\model\AdminMenu;
use app\model\AdminRole;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Redis;

class AuthPower implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {

        if ($this->isDotNeedCheckPower($request->path())) {
            return $handler($request);
        }
        if ($this->isNeedCheckPower($request->path())) {
            $authInfo =   $request->authInfo;
            if (!empty($authInfo) && $authInfo['role_id'] != 1) {
                $adminRoleMidData = Redis::get("admin:adminRole:available:requestUrl:" . $authInfo['role_id']);
                if (empty($adminRoleMidData)) {
                    $adminRoleInfo = AdminRole::where('rid', $authInfo['role_id'])->find();
                    $menuData = AdminMenu::where('type', 2)->where('mid', 'in', $adminRoleInfo['bind_mid_list'])->column('server_url');
                    $adminRoleMidData = json_encode($menuData);
                    Redis::set("admin:adminRole:available:requestUrl:" . $authInfo['role_id'], $adminRoleMidData, "EX", 365 * 24 * 3600);
                }
                if (!empty($adminRoleMidData)) {
                    if (!in_array($request->path(), json_decode($adminRoleMidData, true))) {
                        return new Response(403, ['Content-Type' => 'application/json'], json_encode(['code' => 403, 'msg' => '无权限', 'data' => null]));
                    }
                }
            }
        }

        return $handler($request);
    }
    private function isDotNeedCheckPower($requestUrl)
    {
        $data = [];
        $data[] = '/admin/AdminMenu/getMenuData';
        if (in_array($requestUrl, $data)) {
            return true;
        }
        return false;
    }

    private function isNeedCheckPower($requestUrl)
    {
        $data = [];
        $data[] = '/admin/Box/modifyBox';
        $data[] = '/admin/Box/resetBoxAwardList';
        $data[] = '/admin/Box/resetAllBoxAwardList';
        if (in_array($requestUrl, $data)) {
            return true;
        }
        return false;
    }
}
