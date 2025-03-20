<?php

namespace app\validate;


use think\Validate;


class Page extends Validate
{
    protected $rule = [
        'pid' => 'require',
        'title' => 'require',
        'content' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'pid.require' => '信息不存在',
        'title.require' => '标题必须',
        'content.require' => '内容必须',
    ];

    protected $scene = [
        'adminGet' => ['pid'],
        'adminModify' => ['pid', 'title', 'content'],
        'adminCreate' => ['title', 'content'],
        'adminDelete' => ['pid'],
        'apiGet' => ['pid'],

    ];
}
