<?php

namespace app\api\controller;

use app\api\model\Recharge as ModelRecharge;
use support\Request;

class Recharge
{
    //创建充值订单
    public function createRechargeOrder(Request $request)
    {
        $data = $request->alone(['cid']);
        $keyName = 'api:Recharge:createRechargeOrder:' . $request->authInfo['uid'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelRecharge::createRechargeOrder($request->authInfo, $data);
        redisUnlock($keyName);
        return encryptedJson($result);
    }

    //获得我的充值记录列表
    public function getRechargeList(Request $request)
    {
        $data = $request->alone(['page', 'limit']);
        $result = ModelRecharge::getRechargeList($request->authInfo, $data);
        return encryptedJson($result);
    }
    //获得充值金额表
    public function getRechargeConfigList(Request $request)
    {
        $result = ModelRecharge::getRechargeConfigList();
        return encryptedJson($result);
    }

    //获得首次充值奖励列表
    public function getRechargeConfigFirstList(Request $request)
    {
        $result = ModelRecharge::getRechargeConfigFirstList($request->authInfo);
        return encryptedJson($result);
    }
    //领取首充奖品
    public function getRechargeConfigFirstReward(Request $request)
    {
        $data = $request->alone(['rid']);
        $result = ModelRecharge::getRechargeConfigFirstReward($request->authInfo, $data);
        return encryptedJson($result);
    }
}
