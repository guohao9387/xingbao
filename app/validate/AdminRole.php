<?php

namespace app\validate;

use think\Validate;


class AdminRole extends Validate
{
    protected $rule = [
        'rid' => 'require',
        'role_name' => 'require',
        'bind_mid_list' => 'require|checkBindMidList',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'rid.require' => '信息不存在',
        'role_name.require' => '角色名称必须',
        'bind_mid_list.require' => '权限必须',
    ];

    protected $scene = [
        'adminCreate' => ['role_name', 'bind_mid_list'],
        'adminModify' => ['rid', 'role_name', 'bind_mid_list'],
        'adminGet' => ['rid'],
        'adminDelete' => ['rid'],
    ];

    protected function checkBindMidList($value, $rule, $data = [])
    {
        // $map = [];
        // $map[] = ['mid', 'in', $data['bind_mid_list']];
        // $count = AdminMenuModel::Where($map)->count();
        // if (count($data['bind_mid_list']) != $count) {
        //     return "权限参数非法";
        // }
        return true;
    }
}
