<?php

namespace app\api\model;

use app\model\Room;
use app\model\RoomMember;
use app\model\SystemConfig;
use app\model\User;
use app\model\Withdraw as ModelWithdraw;
use think\Model;
use think\facade\Db;

class Withdraw extends Model
{

    //提现
    public static function createWithdraw($authInfo, $data)
    {
        $data['uid'] = $authInfo['uid'];
        $validate = new \app\validate\Withdraw;
        if (!$validate->scene('apiCreate')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        if (empty($data['trade_password'])) {
            return ['code' => 201, 'msg' => '请输入交易密码', 'data' => null];
        }
        $userInfo = User::where("uid", $authInfo['uid'])->find();
        if (empty($userInfo['trade_password'])) {
            return ['code' => 201, 'msg' => '请先设置交易密码后进行操作', 'data' => null];
        }
        if ($userInfo['real_status'] != 2) {
            return ['code' => 2002, 'msg' => '请实名认证后操作', 'data' => null];
        }
        if (empty($userInfo['alipay_real_name']) || empty($userInfo['alipay_account'])) {
            return ['code' => 201, 'msg' => '请先绑定收款支付宝账号信息后进行操作', 'data' => null];
        }
        if (empty($userInfo['trade_password'])) {
            return ['code' => 201, 'msg' => '请先设置交易密码后进行操作', 'data' => null];
        }
        if ($userInfo['trade_password'] != md5($data['trade_password'])) {
            return ['code' => 201, 'msg' => '支付密码不正确', 'data' => null];
        }
        $min_withdraw_amount = SystemConfig::getValueByName('min_withdraw_amount');
        $user_withdraw_server_rate = SystemConfig::getValueByName('user_withdraw_server_rate');
        $exchange_money_rate = SystemConfig::getValueByName('exchange_money_rate');
        $max_withdraw_day_count =  SystemConfig::getValueByName('max_withdraw_day_count');
        $open_withdraw =  SystemConfig::getValueByName('open_withdraw');
        if ($data['money'] < $min_withdraw_amount) {
            $min_withdraw_amount_rmb = $min_withdraw_amount * $exchange_money_rate;
            return ['code' => 201, 'msg' => '单笔提现金额最小为' . $min_withdraw_amount_rmb . '元', 'data' => null];
        }
        //每天只能提现一次
        $map = [];
        $map[] = ['uid', '=', $authInfo['uid']];
        $map[] = ['create_time', '>=', strtotime(date('Y-m-d'))];
        $toDayWithdrawCount = ModelWithdraw::where($map)->count();
        if ($toDayWithdrawCount >= $max_withdraw_day_count) {
            return ['code' => 201, 'msg' => '每天只能提现' . $max_withdraw_day_count . '次', 'data' => null];
        }
        if ($open_withdraw > 1) {
            return ['code' => 201, 'msg' => '提现暂停中', 'data' => null];
        }



        $createData = [];
        $createData['uid'] = $authInfo['uid'];
        $createData['money'] =  round($data['money'] * $exchange_money_rate, 2);
        $createData['server_money'] = round($data['money'] * $exchange_money_rate * $user_withdraw_server_rate, 2);
        $createData['general_money'] = round($createData['money'] - $createData['server_money'], 2);
        $createData['pay_type'] = 1;
        $createData['alipay_real_name'] = $userInfo['alipay_real_name'];
        $createData['alipay_account'] = $userInfo['alipay_account'];
        $createData['deal_status'] = 1;

        Db::startTrans();
        try {
            //扣除宝石
            $result = User::changeUserFund($authInfo['uid'], 1, 2, -$data['money'], "用户提现");
            if ($result['code'] != 200) {
                Db::rollback();
                return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
            }
            $result = ModelWithdraw::create($createData);
            if (!$result) {
                Db::rollback();
                return ['code' => 201, 'msg' => '提现失败', 'data' => null];
            }
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['code' => 201, 'msg' => '提现失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '申请成功', 'data' => null];
    }

    public static function getWithdrawList($authInfo, $data)
    {
        $map = [];
        $map[] = ['uid', '=', $authInfo['uid']];
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 12;
        $list =  ModelWithdraw::field('order_sn,money,server_money,general_money,pay_type,alipay_real_name,alipay_account,deal_status,family_deal_status,create_time')->where($map)->page($data['page'], $data['limit'])->order('wid', 'desc')->select();
        foreach ($list as $k => &$v) {
            $v->pay_type_text = $v->pay_type_text;
            $v->deal_status_text = $v->deal_status_text;

        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => ['list' => $list]];
    }
}
