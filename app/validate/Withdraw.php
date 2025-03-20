<?php

namespace app\validate;


use think\Validate;


class Withdraw extends Validate
{
    protected $rule = [
        'wid' => 'require',
        'money' => 'require|gt:0',
        'pay_type' => 'require|in:1,2',
        'alipay_real_name' => 'requireIf:pay_type,1',
        'alipay_account' => 'requireIf:pay_type,1',
        'wx_open_id' => 'requireIf:pay_type,2',
        'deal_status' => 'require|in:1,2,3',
        'deal_note' => 'requireIf:deal_status,3',

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'wid.require' => '信息不存在',
        'money.require' => '提现金额必须',
        'money.lt' => '提现金额必须大于0',
        'alipay_real_name.requireIf' => '支付宝真实姓名必须',
        'alipay_account.requireIf' => '支付宝账号必须',
        'wx_open_id.requireIf' => '微信提现参数不能为空',
        'deal_status.require' => '提现状态必须',
        'deal_status.in' => '提现状态参数异常',
        'deal_note.requireIf' => '提现拒绝时，备注不能为空'
    ];

    protected $scene = [
        'adminGet' => ['wid'],
        'adminModify' => ['wid', 'deal_status', 'deal_note'],
        'apiCreate' => ['money', 'alipay_account', 'alipay_real_name'],
    ];
}
