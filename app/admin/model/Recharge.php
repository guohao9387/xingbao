<?php

namespace app\admin\model;

use app\model\Recharge as ModelRecharge;
use app\model\User;
use think\Model;

class Recharge extends Model
{

    public static function getRechargeList($data)
    {
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'rid';
        $data['sort'] = !empty($data['sort']) ? $data['sort'] : 'desc';
        $map = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, ['page', 'limit', 'order', 'sort'])) {
                if ($v != "") {
                    if (in_array($k, ['order_sn'])) {
                        $map[] = [$k, 'like', "%$v%"];
                    } elseif ($k == "user_number") {
                        $uidData = User::where('number', 'like', "%{$data['user_number']}%")->column('uid');
                        $map[] = ['uid', 'in', $uidData];
                    } else {
                        $map[] = [$k, '=', $v];
                    }
                }
            }
        }

        $model =  ModelRecharge::where($map);
        $list =  $model->page($data['page'], $data['limit'])->order($data['order'], $data['sort'])->select();
        $user_data = User::getUsersData(array_column($list->toArray(), 'uid'));
        foreach ($list as $k => &$v) {
            $v->nick_name = !empty($user_data[$v->uid]) ? $user_data[$v->uid]['nick_name'] : '';
            $v->number = !empty($user_data[$v->uid]) ? $user_data[$v->uid]['number'] : '';
            $v->pay_status_text = $v->pay_status_text;
            $v->pay_type_text = $v->pay_type_text;
        }
        $totalRowData = $model->field('count(1) as count')->find();
        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $map[] = ['rid', '>', 0];
        $data['totalRow'] = ModelRecharge::where($map)->field('sum(money) as money,sum(integral) as integral')->find();
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }



    public static function getRechargePayType()
    {
        $data = ModelRecharge::getPayTypeData();
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
}
