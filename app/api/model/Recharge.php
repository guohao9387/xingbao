<?php

namespace app\api\model;

use app\model\Attire;
use app\model\Recharge as ModelRecharge;
use app\model\RechargeConfig;
use app\model\SystemConfig;
use app\model\UserAttire;
use app\model\UserAttireOrder;
use app\model\RechargeBlack;
use think\facade\Db;
use think\Model;

class Recharge extends Model
{

    public static function createRechargeOrder($authInfo, $data)
    {
        $data['uid'] = $authInfo['uid'];
        $validate = new \app\validate\Recharge;
        if (!$validate->scene('apiCreate')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $rechargeConfigInfo = RechargeConfig::where('cid', $data['cid'])->findOrEmpty();
        if ($rechargeConfigInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '充值信息不存在', 'data' => null];
        }
        if ($authInfo['test_status'] == 2) {
            return ['code' => 201, 'msg' => '停用充值功能', 'data' => null];
        }
        // if($authInfo['uid']!=1001091){
        //     return ['code' => 201, 'msg' => '暂未开放', 'data' => null];
        // }
        //打开默认
        $recharge_need_real = SystemConfig::getValueByName('recharge_need_real');
        if ($recharge_need_real == 2) {
            if ($authInfo['real_status'] == 1) {
                return ['code' => 203, 'msg' => '请实名认证后进行操作', 'data' => null];
            } else {
                if (empty($authInfo['card_id'])) {
                    return ['code' => 203, 'msg' => '请补充证件信息', 'data' => null];
                }
                //身份证号处理
                $cardBlackList = RechargeBlack::getRechargeBlackData();
                //            return ['code' => 201, 'msg' => '---', 'data' => [$authInfo['card_id'],$balackList]];
                if (in_array($authInfo['card_id'], $cardBlackList)) {
                    return ['code' => 203, 'msg' => '充值功能受限,请联系官方客服', 'data' => null];
                }
            }
        }
        $data['cid'] = $rechargeConfigInfo['cid'];
        $data['money'] = $rechargeConfigInfo['money'];
        $data['integral'] = $rechargeConfigInfo['integral'];
        $data['pay_status'] = 1;
        $result = ModelRecharge::create($data);
        $returnData = [];
        $returnData['order_sn'] = $result['order_sn'];
        //等待支付对接
        return ['code' => 200, 'msg' => '获取成功', 'data' => $returnData];
    }

    public static function getRechargeList($authInfo, $data)
    {
        $map = [];
        $map[] = ['uid', '=', $authInfo['uid']];
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 12;
        $list = ModelRecharge::field('order_sn,pay_type,money,integral,pay_status,notify_time,create_time')->where($map)->page($data['page'], $data['limit'])->order('rid', 'desc')->select();
        foreach ($list as $k => &$v) {
            $v->pay_type_text = $v->pay_type_text;
            $v->pay_status_text = $v->pay_status_text;
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => ['list' => $list]];
    }

    public static function getRechargeConfigList()
    {
        $rechargeConfigList = RechargeConfig::cache(60)->field('cid,money,integral')->order('money asc')->select();
        return ['code' => 200, 'msg' => '获取成功', 'data' => $rechargeConfigList];
    }

    public static function getRechargeConfigFirstList($authInfo)
    {
        $map = [];
        $map[] = ['first_status', '=', 2];
        $rechargeConfigList = RechargeConfig::where($map)->field('cid,money,integral,first_status,attr_list')->order('money asc')->select();

        $attrAidData = [];
        foreach ($rechargeConfigList as $k => $v) {
            $attrAidData = array_merge($attrAidData, $v['attr_list']);
        }
        $attireData = Attire::where('aid', 'in', $attrAidData)->column('name,type,image,play_image,play_type,available_day', 'aid');
        //获取领取状态
        $map = [];
        $map[] = ['uid', '=', $authInfo['uid']];
        $map[] = ['first_grant_status', 'in', [2, 3]];
        $rechargeInfoData = ModelRecharge::where($map)->column('rid,first_grant_status', 'cid');

        foreach ($rechargeConfigList as $k => $v) {
            $attr_list_data = [];
            foreach ($v['attr_list'] as $m => $n) {
                if (!empty($n)) {
                    if (!empty($attireData[$n])) {
                        $attr_list_data[$m]['aid'] = $attireData[$n]['aid'];
                        $attr_list_data[$m]['name'] = $attireData[$n]['name'];
                        $attr_list_data[$m]['image'] = storageNetworkAddress($attireData[$n]['image']);
                        $attr_list_data[$m]['play_image'] = storageNetworkAddress($attireData[$n]['play_image']);
                        $attr_list_data[$m]['play_type'] = $attireData[$n]['play_type'];
                        $attr_list_data[$m]['type'] = $attireData[$n]['type'];
                        $attr_list_data[$m]['available_day'] = $attireData[$n]['available_day'];
                    }
                }
            }
            $rechargeConfigList[$k]['attr_list_data'] = $attr_list_data;
            //领取状态
            $rechargeConfigList[$k]['first_grant_status'] = 1;
            $rechargeConfigList[$k]['rid'] = 0;
            if (!empty($rechargeInfoData[$v['cid']])) {
                $rechargeConfigList[$k]['first_grant_status'] = $rechargeInfoData[$v['cid']]['first_grant_status'];
                $rechargeConfigList[$k]['rid'] = $rechargeInfoData[$v['cid']]['rid'];
            }
            unset($rechargeConfigList[$k]['attr_list']);
        }
        $can_grant_status = 1; //无可领取的奖品状态
        foreach ($rechargeConfigList as $k => $v) {
            if ($v['first_grant_status'] != 3) {
                $can_grant_status = 2; //有可领取的奖品状态
            }
        }

        return ['code' => 200, 'msg' => '获取成功', 'data' => ['list' => $rechargeConfigList, 'can_grant_status' => $can_grant_status]];
    }

    public static function getRechargeConfigFirstReward($authInfo, $data)
    {
        if (empty($data['rid'])) {
            return ['code' => 201, 'msg' => '参数错误', 'data' => null];
        }
        $map = [];
        $map[] = ['uid', '=', $authInfo['uid']];
        $map[] = ['rid', '=', $data['rid']];
        $rechargeInfo = ModelRecharge::where($map)->findOrEmpty();
        if ($rechargeInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }
        if ($rechargeInfo->first_grant_status == 1) {
            return ['code' => 201, 'msg' => '无法领取', 'data' => null];
        }
        if ($rechargeInfo->first_grant_status == 3) {
            return ['code' => 201, 'msg' => '奖品已领取', 'data' => null];
        }
        //发放装扮
        $map = [];
        $map[] = ['cid', '=', $rechargeInfo['cid']];
        $rechargeConfigInfo = RechargeConfig::where($map)->findOrEmpty();
        if ($rechargeConfigInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '活动已结束', 'data' => null];
        }

        Db::startTrans();
        try {
            $map = [];
            $map[] = ['rid', '=', $rechargeInfo['rid']];
            $map[] = ['first_grant_status', '=', 2];
            $updateData = [];
            $updateData['first_grant_status'] = 3;
            $result = ModelRecharge::where($map)->update($updateData);
            if (!$result) {
                Db::rollback();
                return ['code' => 201, 'msg' => '无法重复领取', 'data' => null];
            }
            $nowTime = time();
            foreach ($rechargeConfigInfo['attr_list'] as $k => $v) {
                $map = [];
                $map[] = ['aid', '=', $v];
                $attireInfo = Attire::where($map)->findOrEmpty();
                if (!$attireInfo->isEmpty()) {
                    // 启动事务

                    //查找用户装扮信息
                    $map = [];
                    $map[] = ['aid', '=', $v];
                    $map[] = ['uid', '=', $authInfo['uid']];
                    $userAttireInfo = UserAttire::where($map)->findOrEmpty();
                    if ($userAttireInfo->isEmpty()) {
                        $insertData = [];
                        $insertData['uid'] = $authInfo['uid'];
                        $insertData['type'] = $attireInfo['type'];
                        $insertData['id'] = $attireInfo['aid'];
                        $insertData['use_status'] = 1;
                        $insertData['over_time'] = $nowTime + $attireInfo['available_day'] * 86400;
                        UserAttire::create($insertData);
                    } else {
                        if ($userAttireInfo['over_time'] > $nowTime) {
                            UserAttire::where('aid', $userAttireInfo['aid'])->inc('over_time', $attireInfo['available_day'] * 86400)->update();
                        } else {
                            UserAttire::where('aid', $userAttireInfo['aid'])->update(['over_time' => $nowTime + $attireInfo['available_day'] * 86400]);
                        }
                    }
                    //创建订单记录
                    $insertData = [];
                    $insertData['uid'] = 0; //系统赠送
                    $insertData['receive_uid'] = $authInfo['uid'];
                    $insertData['type'] = $attireInfo['type'];
                    $insertData['aid'] = $attireInfo['aid'];
                    $insertData['attr_name'] = $attireInfo['name'];
                    $insertData['attr_image'] = $attireInfo['image'];
                    $insertData['price'] = $attireInfo['price'];
                    $insertData['available_day'] = $attireInfo['available_day'];
                    UserAttireOrder::create($insertData);
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['code' => 201, 'msg' => '领取失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '领取成功', 'data' => null];
    }
}
