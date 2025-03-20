<?php

namespace app\admin\controller;

use app\admin\model\SystemConfig as ModelSystemConfig;
use support\Request;

class SystemConfig
{
    public function getSystemConfigList(Request $request)
    {
        $data = $request->only(['type', 'key_title', 'key_name', 'key_value', 'page', 'limit']);
        $result = ModelSystemConfig::getSystemConfigList($data);
        return json($result);
    }
    public function getSystemConfigInfo(Request $request)
    {
        $data = $request->only(['cid']);
        $result = ModelSystemConfig::getSystemConfigInfo($data);
        return json($result);
    }
    public function modifySystemConfig(Request $request)
    {
        $data = $request->only(['cid', 'key_value', 'key_desc', 'sort']);
        $result = ModelSystemConfig::modifySystemConfig($data);
        return json($result);
    }
}
