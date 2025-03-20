<?php

namespace app\admin\controller;

use app\admin\model\RechargeConfig as ModelRechargeConfig;
use support\Request;

class RechargeConfig
{
    public function getRechargeConfigList(Request $request)
    {
        $data = $request->only(['cid',   'order', 'sort', 'page', 'limit']);
        $result = ModelRechargeConfig::getRechargeConfigList($data);
        return json($result);
    }
    public function getRechargeConfig(Request $request)
    {
        $data = $request->only(['cid']);
        $result = ModelRechargeConfig::getRechargeConfig($data);
        return json($result);
    }
    public function createRechargeConfig(Request $request)
    {
        $data = $request->only(['money', 'integral', 'first_status', 'attr_list']);
        $result = ModelRechargeConfig::createRechargeConfig($data);
        return json($result);
    }
    public function modifyRechargeConfig(Request $request)
    {
        $data = $request->only(['cid', 'money', 'integral', 'first_status', 'attr_list']);
        $result = ModelRechargeConfig::modifyRechargeConfig($data);
        return json($result);
    }
    public function deleteRechargeConfig(Request $request)
    {
        $data = $request->only(['cid']);
        $result = ModelRechargeConfig::deleteRechargeConfig($data);
        return json($result);
    }
}
