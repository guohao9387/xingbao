<?php

namespace app\validate;


use think\Validate;


class Banner extends Validate
{
    protected $rule = [
        'bid' => 'require',
        'image' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'bid.require' => '信息不存在',
        'image.require' => '图片必须',
    ];

    protected $scene = [
        'adminGet' => ['bid'],
        'adminModify' => ['bid',  'image'],
        'adminCreate' => ['image'],
        'adminDelete' => ['bid'],
    ];
}
