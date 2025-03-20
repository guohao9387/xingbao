<?php

namespace app\admin\controller;

use app\admin\model\AdminRole as ModelAdminRole;
use support\Request;

class AdminRole
{
    public function getAdminRoleList(Request $request)
    {
        $data = $request->only(['role_name', 'page', 'limit']);
        $result = ModelAdminRole::getAdminRoleList($data);
        return json($result);
    }

    public function getAdminRoleInfo(Request $request)
    {
        $data = $request->only(['rid']);
        $result = ModelAdminRole::getAdminRoleInfo($data);
        return json($result);
    }
    public function createAdminRole(Request $request)
    {
        $data = $request->only(['role_name', 'bind_mid_list']);
        $result = ModelAdminRole::createAdminRole($data);
        return json($result);
    }
    public function modifyAdminRole(Request $request)
    {
        $data = $request->only(['rid', 'role_name',  'bind_mid_list']);
        $result = ModelAdminRole::modifyAdminRole($data);
        return json($result);
    }
    public function deleteAdminRole(Request $request)
    {
        $data = $request->only(['rid']);
        $result = ModelAdminRole::deleteAdminRole($data);
        return json($result);
    }
}
