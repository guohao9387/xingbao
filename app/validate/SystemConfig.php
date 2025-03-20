<?php

namespace app\validate;

use think\Validate;


class SystemConfig extends Validate
{

    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'cid' => 'require',
        'sort' => 'require',
    ];
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'cid.require' => '信息不存在',
        'sort.require' => '排序必须',
    ];
    protected $scene = [
        'adminModify' => ['cid', 'sort'],
        'adminGet' => ['cid'],
    ];
}
