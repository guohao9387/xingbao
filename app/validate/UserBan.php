<?php

namespace app\validate;

use app\model\UserBan as ModelUserBan;
use think\Validate;


class UserBan extends Validate
{
    protected $rule = [
        'bid' => 'require',
        'type' => 'require|checkType',
        'data' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'bid.require' => '信息不存在',
        'type.require' => '封禁类型必须',
        'data.require' => '封禁标识必须',
    ];
    protected $scene = [
        'adminCreate' => ['type', 'data'],
        'adminDelete' => ['bid'],
    ];


    protected function checkType($value, $rule, $data = [])
    {
        $result = ModelUserBan::getTypeData();
        if (empty($result[$data['type']])) {
            return "封禁类型不存在";
        }
        return true;
    }
}
