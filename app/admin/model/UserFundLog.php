<?php

namespace app\admin\model;


use app\model\User;
use app\model\UserFundLog as ModelUserFundLog;
use think\Model;

class UserFundLog extends Model
{

    public static function getUserFundLogList($data)
    {
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'lid';
        $data['sort'] = !empty($data['sort']) ? $data['sort'] : 'desc';
        $map = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, ['page', 'limit', 'order', 'sort'])) {
                if ($v != "") {
                    if (in_array($k, ['change_type'])) {
                        $map[] = [$k, 'in', $v];
                    }elseif ($k == "user_number") {
                        $uidData = User::where('number', 'like', "%{$data['user_number']}%")->column('uid');
                        $map[] = ['uid', 'in', $uidData];
                    } else {
                        $map[] = [$k, '=', $v];
                    }
                }
            }
        }

        $model =  ModelUserFundLog::where($map);
        $list =  $model->page($data['page'], $data['limit'])->order($data['order'], $data['sort'])->select();
        $totalRowData = $model->field('count(1) as count,sum(change_value) as change_value')->find();
        $userData = User::getUsersData(array_column($list->toArray(), 'uid'));
        foreach ($list as $k => &$v) {
            $v->nick_name = !empty($userData[$v->uid]) ? $userData[$v->uid]['nick_name'] : '';
            $v->user_name = !empty($userData[$v->uid]) ? $userData[$v->uid]['user_name'] : '';
            $v->change_type_text = $v->change_type_text;
            $v->fund_type_text = $v->fund_type_text;
        }
        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $data['totalRow'] = $totalRowData;
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
    public static function getUserFundLogChangeType()
    {
        $data = ModelUserFundLog::getChangeTypeData();
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
    public static function getUserFundLogFundType()
    {
        $data = ModelUserFundLog::getFundTypeData();
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
}
