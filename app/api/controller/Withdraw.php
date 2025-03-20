<?php

namespace app\api\controller;

use app\api\model\Withdraw as ModelWithdraw;
use support\Request;

class Withdraw
{
    //提现
    public function createWithdraw(Request $request)
    {
        $data = $request->only(['money', 'alipay_real_name', 'alipay_account', 'trade_password']);
        $keyName = 'api:Withdraw:createWithdraw:' . $request->authInfo['uid'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelWithdraw::createWithdraw($request->authInfo, $data);
        redisUnlock($keyName);
        return encryptedJson($result);
    }
    //获得用户提现列表
    public function getWithdrawList(Request $request)
    {
        $data = $request->only(['page', 'limit']);
        $result = ModelWithdraw::getWithdrawList($request->authInfo, $data);
        return encryptedJson($result);
    }
}
