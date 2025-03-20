<?php

namespace app\validate;


use app\model\UserMessage as ModelUserMessage;
use think\Validate;


class UserMessage extends Validate
{
    protected $rule = [
        'mid' => 'require',
        'title.require' => '标题必须',
        'content.require' => '内容必须',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'mid.require' => '信息不存在',
        'title.require' => '标题必须',
        'content.require' => '内容必须',
    ];

    protected $scene = [
        'adminGet' => ['mid'],
        'adminModify' => ['mid', 'title', 'content'],
        'adminCreate' => ['title', 'content'],
        'adminDelete' => ['mid'],
        'apiGet' => ['mid'],
    ];

}
