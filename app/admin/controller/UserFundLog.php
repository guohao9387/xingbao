<?php

namespace app\admin\controller;

use app\admin\model\UserFundLog as ModelUserFundLog;
use support\Request;

class UserFundLog
{
    public function getUserFundLogList(Request $request)
    {
        $data = $request->only(['lid', 'uid','user_number','room_number', 'change_type', 'fund_type',  'order', 'sort', 'page', 'limit']);
        $result = ModelUserFundLog::getUserFundLogList($data);
        return json($result);
    }
    public function getUserFundLogChangeType(Request $request)
    {
        $result = ModelUserFundLog::getUserFundLogChangeType();
        return json($result);
    }
    public function getUserFundLogFundType(Request $request)
    {
        $result = ModelUserFundLog::getUserFundLogFundType();
        return json($result);
    }
}
