<?php

namespace app\validate;

use app\model\Admin as ModelAdmin;
use app\model\AdminRole;
use think\Validate;


class Admin extends Validate
{
    // 定义规则
    protected $rule =   [
        'aid' => 'require',
        'user_name' => 'require|checkUserName',
        'password' => 'require|regex:[a-zA-Z0-9]{6,16}',
        'nick_name' => 'require',
        'role_id' => 'require|checkRoleId',
        'login_status' => 'require|in:1,2',
        'login_token' => 'require',
    ];

    // 定义信息
    protected $message  =   [
        'aid.require' => '信息不存在',
        'user_name.require' => '用户名必须',
        'password.require' => '密码必须',
        'password.regex' => '密码必须6-16位字母或者数字组成',
        'nick_name.require' => '昵称必须',
        'role_id.require' => '身份必须',
        'login_status.require' => '登录状态必须',
        'login_status.in' => '登录状态非法',
        'login_token.require' => '信息不存在',
    ];

    protected $scene = [
        'adminCreate' => ['user_name', 'password', 'nick_name', 'role_id', 'login_status'],
        'adminModify' => ['aid', 'user_name', 'nick_name', 'role_id', 'login_status'],
        'adminModifyPassword' => ['aid', 'password'],
        'adminGet' => ['aid'],
        'adminDelete' => ['aid'],
        'adminLogout' => ['login_token'],
    ];


    // 登录验证场景定义
    public function sceneAdminLogin()
    {
        return $this->only(['user_name', 'password'])
            ->remove('user_name', 'checkUserName')
            ->remove('password', 'regex');
    }



    protected function checkRoleId($value, $rule, $data = [])
    {
        $info = AdminRole::getByRid($data['role_id']);
        if (empty($info)) {
            return "身份信息不存在";
        }
        return true;
    }

    protected function checkUserName($value, $rule, $data = [])
    {
        $info = ModelAdmin::getByUserName($data['user_name']);
        if (!empty($info)) {
            if (!empty($data['aid'])) {
                if ($info['aid'] != $data['aid']) {
                    return "用户名已被占用";
                }
            } else {
                return "用户名已被占用";
            }
        }
        return true;
    }
}
