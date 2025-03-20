<?php

namespace app\validate;


use think\Validate;


class UserRealCheck extends Validate
{
    protected $rule = [
        'rid' => 'require',
        'status' => 'require|in:1,2,3,4',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'rid.require' => '信息不存在',
        'status.require' => '状态必须',
        'status.in' => '状态参数非法',
    ];

    protected $scene = [
        'adminGet' => ['rid'],
        'adminModify' => ['rid','status'],
        'adminDelete' => ['rid'],
    ];
}
