<?php

namespace app\admin\controller;

use app\admin\model\Withdraw as ModelWithdraw;
use support\Request;

class Withdraw
{
    public function getWithdrawList(Request $request)
    {
        $data = $request->only(['wid', 'order_sn', 'uid', 'user_number', 'family_id', 'family_deal_status', 'pay_type', 'alipay_real_name', 'alipay_account', 'deal_status',  'order', 'sort', 'page', 'limit']);
        $result = ModelWithdraw::getWithdrawList($data);
        return json($result);
    }

    public function getWithdrawInfo(Request $request)
    {
        $data = $request->only(['wid']);
        $result = ModelWithdraw::getWithdrawInfo($data);
        return json($result);
    }
    public function modifyWithdraw(Request $request)
    {
        $data = $request->only(['wid', 'deal_status', 'deal_note']);
        $keyName = 'admin:Withdraw:modifyWithdraw:' . $data['wid'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelWithdraw::modifyWithdraw($data);
        redisUnlock($keyName);
        return json($result);
    }

    public function getDealStatusData(Request $request)
    {
        $result = ModelWithdraw::getDealStatusData();
        return json($result);
    }

    public function getPayTypeData(Request $request)
    {
        $result = ModelWithdraw::getPayTypeData();
        return json($result);
    }
}
