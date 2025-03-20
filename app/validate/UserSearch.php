<?php

namespace app\validate;

use app\model\UserSearch as UserSearchModel;
use think\Validate;


class UserSearch extends Validate
{
    protected $rule = [
        'sid' => 'require',
        'uid' => 'require',
        'type' => 'require|checkType',
        'keywords' => 'require',

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'sid.require' => '信息不存在',
        'uid.require' => '用户信息必须',
        'type.require' => '搜索类型必须',
        'keywords.require' => '搜索关键字必须',
    ];

    protected $scene = [
        'apiCreate' => ['uid', 'type', 'keywords'],
    ];

    protected function checkType($value, $rule, $data = [])
    {
        $data_info = UserSearchModel::getTypeData();
        if (empty($data_info[$data['type']])) {
            return "变动类型不存在";
        }
        return true;
    }
}
