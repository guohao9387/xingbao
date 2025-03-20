<?php

namespace app\validate;


use think\Validate;


class GameServer extends Validate
{
    protected $rule = [
        'sid' => 'require',
        'gid' => 'require',
        'name' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'sid.require' => '信息不存在',
        'gid.require' => '必须有游戏类型',
        'name.require' => '服务器名称必须',
    ];

    protected $scene = [
        'adminGet' => ['sid'],
        'adminModify' => ['sid',  'name', 'gid'],
        'adminCreate' => ['name', 'gid'],
        'adminDelete' => ['sid'],
    ];
}
