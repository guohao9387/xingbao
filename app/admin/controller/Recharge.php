<?php

namespace app\admin\controller;

use app\admin\model\Recharge as ModelRecharge;
use support\Request;

class Recharge
{
    public function getRechargeList(Request $request)
    {
        $data = $request->only(['rid', 'order_sn', 'uid', 'user_number', 'order_sn', 'pay_type', 'pay_status',  'order', 'sort', 'page', 'limit']);
        $result = ModelRecharge::getRechargeList($data);
        return json($result);
    }


    public function getRechargePayType(Request $request)
    {
        $result = ModelRecharge::getRechargePayType();
        return json($result);
    }
}
