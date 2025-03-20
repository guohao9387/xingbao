<?php

namespace app\api\controller;

use app\api\model\UserFundLog as ModelUserFundLog;
use support\Request;

class UserFundLog
{
    //获得我的资金日志
    public function getUserFundLogList(Request $request)
    {
        $data = $request->only(['fund_type', 'change_type', 'page', 'limit']);
        $result = ModelUserFundLog::getUserFundLogList($request->authInfo, $data);
        return encryptedJson($result);
    }
    public function getUserFundLogChangeType(Request $request)
    {
        $result = ModelUserFundLog::getUserFundLogChangeType();
        return encryptedJson($result);
    }
    public function getUserFundLogFundType(Request $request)
    {
        $result = ModelUserFundLog::getUserFundLogFundType();
        return encryptedJson($result);
    }
}
