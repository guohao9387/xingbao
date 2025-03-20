<?php

namespace app\admin\model;

use app\model\SystemConfig;
use app\model\User as ModelUser;
use app\model\UserFundLog;
use think\Model;

class User extends Model
{

    public static function getUserList($data)
    {

        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'uid';
        $data['sort'] = !empty($data['sort']) ? $data['sort'] : 'desc';
        $map = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, ['page', 'limit', 'order', 'sort'])) {
                if ($v != "") {
                    if (in_array($k, ['nick_name', 'user_name'])) {
                        $map[] = [$k, 'like', "%$v%"];
                    } else {
                        $map[] = [$k, '=', $v];
                    }
                }
            }
        }

        $model = ModelUser::where($map);
        $list = $model->page($data['page'], $data['limit'])->order($data['order'], $data['sort'])->select();
        $totalRowData = $model->field('count(1) as count,sum(money) as money')->find();
        foreach ($list as $k => &$v) {
            // $v->online_status_text = $v->online_status_text;
            $v->real_status_text = $v->real_status_text;
            // $v->robot_status_text = $v->robot_status_text;
            // $v->test_status_text = $v->robot_status_text;
            // $v->give_self_gift_status_text = $v->give_self_gift_status_text;
        }

        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $data['totalRow'] = $totalRowData;
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }

    


    public static function getUserInfo($data)
    {
        $validate = new \app\validate\User;
        if (!$validate->scene('adminGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $info = ModelUser::findOrEmpty($data['uid']);
        if ($info->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $info];
    }

    public static function modifyUser($data)
    {
        $validate = new \app\validate\User;
        if (!$validate->scene('adminModify')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelUser::update($data);

        if (!$result) {
            return ['code' => 201, 'msg' => '更新失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '更新成功', 'data' => null];
    }

    public static function modifyUserMoney($data)
    {
        if (empty($data['uid'])) {
            return ['code' => 201, 'msg' => '用户信息必须', 'data' => null];
        }
        // if (empty($data['fund_type'])) {
        //     return ['code' => 201, 'msg' => '资金类型必须', 'data' => null];
        // }
        //判断二级密码是否正确
        // if (empty($data['system_trade_password'])) {
        //     return ['code' => 201, 'msg' => '二级密码必须', 'data' => null];
        // }
        // $system_trade_password = SystemConfig::getValueByName('system_trade_password');
        // if ($data['system_trade_password'] != $system_trade_password) {
        //     return ['code' => 201, 'msg' => '二级密码错误', 'data' => null];
        // }

        $result = ModelUser::changeUserFund($data['uid'], 1, 1, $data['change_value'], $data['note']);


        return $result;
    }

  
}
