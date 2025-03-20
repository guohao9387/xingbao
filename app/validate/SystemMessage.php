<?php

namespace app\validate;


use think\Validate;


class SystemMessage extends Validate
{
    protected $rule = [
        'smid' => 'require',
        'title' => 'require',
        'content' => 'require',
        // 'push_type' => 'require|in:1,2',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'smid.require' => '信息不存在',
        'title.require' => '标题必须',
        'content.require' => '内容必须',
        // 'push_type.require' => '推送状态',
        // 'push_type.in' => '推送状态参数非法',
    ];

    protected $scene = [
        'adminGet' => ['smid'],
        'adminModify' => ['smid', 'title', 'content'],
        'adminCreate' => ['title', 'content'],
        'adminDelete' => ['smid'],
        'apiGet' => ['smid'],

    ];
}
