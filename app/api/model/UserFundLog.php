<?php

namespace app\api\model;

use app\model\UserFundLog as ModelUserFundLog;
use app\model\User;
use think\Model;

class UserFundLog extends Model
{

    public static function getUserFundLogList($authInfo, $data)
    {
        $map = [];
        $map[] = ['uid', '=', $authInfo['uid']];
        if (!empty($data['fund_type'])) {
            $map[] = ['fund_type', '=', $data['fund_type']];
        }
        if (!empty($data['change_type'])) {
            $map[] = ['change_type', 'in', $data['change_type']];
        }
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 12;
        $list =  ModelUserFundLog::field('fund_type,change_type,change_value,create_time')->where($map)->page($data['page'], $data['limit'])->order('lid', 'desc')->select();
        foreach ($list as $k => &$v) {
            $v->fund_type_text = $v->fund_type_text;
            $v->change_type_text = $v->change_type_text;
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => ['list' => $list]];
    }
    public static function getUserFundLogChangeType()
    {
        $data = ModelUserFundLog::getChangeTypeData();
        $returnData = [];
        foreach ($data as $k => $v) {
            $returnData[] = ['name' => $v, 'value' => $k];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $returnData];
    }
    public static function getUserFundLogFundType()
    {
        $data = ModelUserFundLog::getFundTypeData();
        $returnData = [];
        foreach ($data as $k => $v) {
            $returnData[] = ['name' => $v, 'value' => $k];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $returnData];
    }
}
