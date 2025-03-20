<?php

namespace app\validate;


use think\Validate;


class Game extends Validate
{
    protected $rule = [
        'gid' => 'require',
        'image' => 'require',
        'name' => 'require',

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'gid.require' => '信息不存在',
        'name.require' => '名称必须',
        'image.require' => '图片必须',
    ];

    protected $scene = [
        'adminGet' => ['gid'],
        'adminModify' => ['gid',  'image','name'],
        'adminCreate' => ['image','name'],
        'adminDelete' => ['gid'],
    ];
}
