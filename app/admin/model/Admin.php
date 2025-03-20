<?php

namespace app\admin\model;

use app\model\Admin as ModelAdmin;
use app\model\AdminRole;
use app\model\Sms as ModelSms;
use think\Model;
use support\Redis;

class Admin extends Model
{
    public static function adminLogin($data)
    {
        $validate = new \app\validate\Admin;
        if (!$validate->scene('adminLogin')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $map = [];
        $map[] = ['user_name', '=', $data['user_name']];
        $adminInfo = ModelAdmin::where($map)->findOrEmpty();
        if ($adminInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '用户名不存在', 'data' => null];
        } else {
            if ($adminInfo->login_status != 1) {
                return ['code' => 201, 'msg' => '登录受限', 'data' => null];
            }
            if (md5($data['password']) != $adminInfo->password) {
                $adminInfo->login_error_num = $adminInfo->login_error_num + 1;
                if ($adminInfo->login_error_num > 9) {
                    $adminInfo->login_status = 2;
                }
                $adminInfo->save();
                if ($adminInfo->login_error_num > 9) {
                    return ['code' => 201, 'msg' => '账号已锁定', 'data' => null];
                } else {
                    $can_login_num = 9 - $adminInfo->login_error_num + 1;
                    return ['code' => 201, 'msg' => '密码错误，' . $can_login_num . '次错误后锁定账户', 'data' => null];
                }
            }
            if (empty($adminInfo->login_token)) { //每次更新token
                $adminInfo->login_token = generateRandom(32);
            }
            $adminInfo->login_error_num = 0;
            $adminInfo->save();
            Redis::set("admin:authInfo:" . $adminInfo->login_token, json_encode($adminInfo), "EX", 365 * 24 * 3600);

            $data = [];
            $data['login_token'] = $adminInfo->login_token;
            return ['code' => 200, 'msg' => '登录成功', 'data' => $data];
        }
    }

    public static function adminLoginBySms($data)
    {
        //短信验证部分
        if (floor($data['user_name']) != $data['user_name'] || strlen($data['user_name']) != 11) {
            return ['code' => 201, 'msg' => '手机号格式不正确', 'data' => null];
        }
        if (empty($data['code']) || floor($data['code']) != $data['code']) {
            return ['code' => 201, 'msg' => '验证码非法', 'data' => null];
        }
        $result = ModelSms::validateCode($data['user_name'], $data['code']);
        if ($result['code'] != 200) {
            return ['code' => 201, 'msg' => '短信验证码验证失败', 'data' => null];
        }
        $map = [];
        $map[] = ['user_name', '=', $data['user_name']];
        $adminInfo = ModelAdmin::where($map)->findOrEmpty();
        if ($adminInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '用户名不存在', 'data' => null];
        } else {
            if ($adminInfo->login_status != 1) {
                return ['code' => 201, 'msg' => '登录受限', 'data' => null];
            }
            if (md5($data['password']) != $adminInfo->password) {
                $adminInfo->login_error_num = $adminInfo->login_error_num + 1;
                if ($adminInfo->login_error_num > 9) {
                    $adminInfo->login_status = 2;
                }
                $adminInfo->save();
                if ($adminInfo->login_error_num > 9) {
                    return ['code' => 201, 'msg' => '账号已锁定', 'data' => null];
                } else {
                    $can_login_num = 9 - $adminInfo->login_error_num + 1;
                    return ['code' => 201, 'msg' => '密码错误，' . $can_login_num . '次错误后锁定账户', 'data' => null];
                }
            }
            if (empty($adminInfo->login_token) || $adminInfo->aid != 3) { //每次更新token
                $adminInfo->login_token = generateRandom(32);
            }
            $adminInfo->login_error_num = 0;
            $adminInfo->save();
            Redis::set("admin:authInfo:" . $adminInfo->login_token, json_encode($adminInfo), "EX", 365 * 24 * 3600);
            $data = [];
            $data['login_token'] = $adminInfo->login_token;
            return ['code' => 200, 'msg' => '登录成功', 'data' => $data];
        }
    }

    public static function getAdminList($data)
    {
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'aid';
        $data['sort'] = !empty($data['sort']) ? $data['sort'] : 'desc';
        $map = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, ['page', 'limit', 'order', 'sort'])) {
                if ($v != "") {
                    if (in_array($k, ['user_name', 'nick_name'])) {
                        $map[] = [$k, 'like', "%$v%"];
                    } else {
                        $map[] = [$k, '=', $v];
                    }
                }
            }
        }

        $model = ModelAdmin::where($map);
        $list = $model->page($data['page'], $data['limit'])->order($data['order'], $data['sort'])->select();
        $admin_role_data = AdminRole::column('role_name', 'rid');
        foreach ($list as $k => &$v) {
            $v['role_name'] = !empty($admin_role_data[$v['role_id']]) ? $admin_role_data[$v['role_id']] : "未知";
        }

        $totalRowData = $model->field('count(1) as count')->find();
        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $data['totalRow'] = $totalRowData;
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }


    public static function getAdminInfo($data)
    {
        $validate = new \app\validate\Admin;
        if (!$validate->scene('adminGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $info = ModelAdmin::findOrEmpty($data['aid']);
        if ($info->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $info];
    }


    public static function createAdmin($data)
    {
        $validate = new \app\validate\Admin;
        if (!$validate->scene('adminCreate')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelAdmin::create($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '创建失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '创建成功', 'data' => null];
    }

    public static function modifyAdmin($data)
    {
        $validate = new \app\validate\Admin;
        if (!$validate->scene('adminModify')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        if ($data['aid'] == 1) {
            if ($data['role_id'] != 1) {
                return ['code' => 201, 'msg' => '该管理员无法设置其他角色', 'data' => null];
            }
        }
        $result = ModelAdmin::update($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '更新失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '更新成功', 'data' => null];
    }

    public static function deleteAdmin($data)
    {
        $validate = new \app\validate\Admin;
        if (!$validate->scene('adminDelete')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        if ($data['aid'] == 1) {
            return ['code' => 201, 'msg' => '该管理员无法删除', 'data' => null];
        }
        $result = ModelAdmin::destroy($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '删除失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '删除成功', 'data' => null];
    }

    public static function modifyAdminPassword($data)
    {
        $validate = new \app\validate\Admin;
        if (!$validate->scene('adminModifyPassword')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelAdmin::update($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '修改密码失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '修改密码成功', 'data' => null];
    }
}
