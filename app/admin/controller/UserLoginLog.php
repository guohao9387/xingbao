<?php

namespace app\admin\controller;

use app\admin\model\UserLoginLog as ModelUserLoginLog;
use support\Request;


class UserLoginLog
{
    public function getUserLoginLogList(Request $request)
    {
        $data = $request->only(['lid', 'uid', 'user_name', 'device_name', 'device_os', 'device_id', 'sys_version', 'client_ip', 'group', 'order', 'sort', 'page', 'limit']);
        $result = ModelUserLoginLog::getUserLoginLogList($data);
        return json($result);
    }
    public function getUserLoginLogRelationList(Request $request)
    {
        $data = $request->only(['uid',    'device_id', 'client_ip', 'group', 'order', 'sort', 'page', 'limit']);
        $result = ModelUserLoginLog::getUserLoginLogRelationList($data);
        return json($result);
    }


    public function getUserLoginLogInfo(Request $request)
    {
        $data = $request->only(['lid']);
        $result = ModelUserLoginLog::getUserLoginLogInfo($data);
        return json($result);
    }
    public function getDeviceOsType(Request $request)
    {
        $result = ModelUserLoginLog::getDeviceOsType();
        return json($result);
    }
}
