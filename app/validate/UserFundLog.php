<?php

namespace app\validate;

use app\model\UserFundLog as UserFundLogModel;
use think\Validate;


class UserFundLog extends Validate
{
    protected $rule = [
        'uid' => 'require',
        'fund_type' => 'require',
        'change_type' => 'require|checkChangeType',
        'change_value' => 'require',
        'note' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'uid.require' => '资金变动用户必须',
        'change_type.require' => '资金变动类型必须',
        'change_value.require' => '资金变动数量必须',
        'note.require' => '资金变动日志必须',

    ];

    protected $scene = [
        'create' => ['uid', 'change_type', 'change_value', 'note', 'fund_type'],
    ];

    protected function checkChangeType($value, $rule, $data = [])
    {
        $data_info = UserFundLogModel::getChangeTypeData();
        if (empty($data_info[$data['change_type']])) {
            return "资金变动类型不存在";
        }
        return true;
    }
}
