<?php

namespace app\admin\model;


use app\model\User;
use app\model\UserLoginLog as ModelUserLoginLog;
use think\facade\Db;
use think\Model;

class UserLoginLog extends Model
{

    public static function getUserLoginLogList($data)
    {
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'lid';
        $data['sort'] = !empty($data['sort']) ? $data['sort'] : 'desc';
        $map = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, ['group', 'page', 'limit', 'order', 'sort'])) {
                if ($v != "") {
                    if (in_array($k, ['device_name', 'device_id', 'sys_version', 'client_ip'])) {
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

        $model =  ModelUserLoginLog::where($map);
        $mode =  $model->page($data['page'], $data['limit'])->order($data['order'], $data['sort']);
        if (!empty($data['group'])) {
            //$mode = $mode->group($data['group']);
        }
        $list = $mode->select();
        $totalRowData = $model->field('count(1) as count')->find();
        $user_data = User::getUsersData(array_column($list->toArray(), 'uid'));
        foreach ($list as $k => &$v) {
            $v->nick_name = !empty($user_data[$v->uid]) ? $user_data[$v->uid]['nick_name'] : '';
            $v->user_name = !empty($user_data[$v->uid]) ? $user_data[$v->uid]['user_name'] : '';
            $v->device_os_text = $v->device_os_text;
        }
        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $data['totalRow'] = $totalRowData;
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }


    public static function getUserLoginLogRelationList($data)
    {
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'lid';
        $data['sort'] = !empty($data['sort']) ? $data['sort'] : 'desc';

        foreach ($data as $k => $v) {
            if (!in_array($k, ['page', 'limit', 'order', 'sort'])) {
            }
        }


        if (!empty($data['uid'])) {
            $map1 = [];
            $map1[] = ['device_id', 'in', Db::Raw(ModelUserLoginLog::where('uid', $data['uid'])->fetchSql(true)->column('device_id'))];
            $map2 = [];
            $map2[] = ['client_ip', 'in', Db::Raw(ModelUserLoginLog::where('uid', $data['uid'])->fetchSql(true)->column('client_ip'))];
        } elseif (!empty($data['user_number'])) {
            $uid = User::where('number', $data['user_number'])->value('uid');
            if (empty($uid)) {
                return ['code' => 201, 'msg' => '用户编号信息不存在', 'data' => ['list' => null]];
            }
            $map1 = [];
            $map1[] = ['device_id', 'in', Db::Raw(ModelUserLoginLog::where('uid', $uid)->fetchSql(true)->column('device_id'))];
            $map2 = [];
            $map2[] = ['client_ip', 'in', Db::Raw(ModelUserLoginLog::where('uid', $uid)->fetchSql(true)->column('client_ip'))];
        } elseif (!empty($data['device_id'])) {
            $map1 = [];
            $map1[] = ['uid', 'in', Db::Raw(ModelUserLoginLog::where('device_id', $data['device_id'])->fetchSql(true)->column('uid'))];
            $map2 = [];
            $map2[] = ['client_ip', 'in', Db::Raw(ModelUserLoginLog::where('device_id', $data['device_id'])->fetchSql(true)->column('client_ip'))];
        } elseif (!empty($data['client_ip'])) {
            $map1 = [];
            $map1[] = ['uid', 'in', Db::Raw(ModelUserLoginLog::where('client_ip', $data['client_ip'])->fetchSql(true)->column('uid'))];
            $map2 = [];
            $map2[] = ['device_id', 'in', Db::Raw(ModelUserLoginLog::where('client_ip', $data['client_ip'])->fetchSql(true)->column('device_id'))];
        } else {
            return ['code' => 201, 'msg' => '请填写查询参数', 'data' => ['list' => null]];
        }

        $model =  ModelUserLoginLog::whereOr([$map1, $map2]);
        $list =  $model->page($data['page'], $data['limit'])->order($data['order'], $data['sort'])->select();
        $totalRowData = $model->field('count(1) as count')->find();
        $user_data = User::getUsersData(array_column($list->toArray(), 'uid'));
        foreach ($list as $k => &$v) {
            $v->nick_name = !empty($user_data[$v->uid]) ? $user_data[$v->uid]['nick_name'] : '';
            $v->number = !empty($user_data[$v->uid]) ? $user_data[$v->uid]['number'] : '';
            $v->device_os_text = $v->device_os_text;
        }
        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $data['totalRow'] = $totalRowData;
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }

    public static function getUserLoginLogInfo($data)
    {
        $validate = new \app\validate\UserLoginLog;
        if (!$validate->scene('adminGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $info = ModelUserLoginLog::findOrEmpty($data['lid']);
        if ($info->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $info];
    }


    public static function getDeviceOsType()
    {
        $data = ModelUserLoginLog::getDeviceOsData();
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
}
