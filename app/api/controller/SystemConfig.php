<?php

namespace app\api\controller;

use app\api\model\SystemConfig as ModelSystemConfig;
use support\Request;

class SystemConfig
{

    //获得用户提现列表
    public function getSystemConfigData(Request $request)
    {
        $result = ModelSystemConfig::getSystemConfigData($request->authInfo);
        return encryptedJson($result);
    }
}
