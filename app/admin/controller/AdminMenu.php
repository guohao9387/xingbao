<?php

namespace app\admin\controller;

use app\admin\model\AdminMenu as ModelAdminMenu;
use support\Request;

class AdminMenu
{
    public function getMenuData(Request $request)
    {
        $data = [];
        $data['authInfo'] = $request->authInfo;
        $result = ModelAdminMenu::getMenuData($data);

        return json($result['data']);
    }
    public function getAllMenuData(Request $request)
    {
        $result = ModelAdminMenu::getAllMenuData();
        return json($result);
    }
    public function getAuthMenuData(Request $request)
    {
        $data = $request->only(['rid']);
        $result = ModelAdminMenu::getAuthMenuData($data);
        return json($result);
    }
    public function getAdminMenuList(Request $request)
    {
        $data = $request->only(['title', 'show_status']);
        $result = ModelAdminMenu::getAdminMenuList($data);
        $data = [];
        $data['code'] = 0;
        $data['msg'] = '获取成功';
        $data['data'] = $result['data'];
        return json($data);
    }
    public function getAdminMenu(Request $request)
    {
        $data = $request->only(['mid']);
        $result = ModelAdminMenu::getAdminMenu($data);
        return json($result);
    }
    public function createAdminMenu(Request $request)
    {
        $data = $request->only(['pid', 'title', 'type', 'icon', 'page_href', 'server_url', 'open_type', 'login_status', 'sort']);
        $result = ModelAdminMenu::createAdminMenu($data);
        return json($result);
    }
    public function modifyAdminMenu(Request $request)
    {
        $data = $request->only(['mid', 'title', 'type', 'icon', 'page_href', 'server_url', 'open_type', 'login_status', 'sort']);
        $result = ModelAdminMenu::modifyAdminMenu($data);
        return json($result);
    }
    public function deleteAdminMenu(Request $request)
    {
        $data = $request->only(['mid']);
        $result = ModelAdminMenu::deleteAdminMenu($data);
        return json($result);
    }
}
