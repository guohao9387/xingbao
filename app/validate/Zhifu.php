<?php

namespace app\validate;


use think\Validate;


class Zhifu extends Validate
{
    protected $rule = [
        'pid' => 'require',
        'order_sn' => 'require',
        'uid' => 'require',
        'aid' => 'require',
        'pay_type' => 'require|in:1,2,3',
        'amount' => 'require',
        'pay_status' => 'require|in:1,2',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'pid.require' => '信息不存在',
        'order_sn.require' => '订单编号必须',
        'uid.require' => '用户必须',
        'aid.require' => '商品编号必须',
        'pay_type.require' => '支付类型必须',
        'amount.require' => '订单金额必须',
        'pay_status.require' => '支付状态必须',
    ];

    protected $scene = [
        'adminGet' => ['pid'],
        'adminDelete' => ['pid'],
        'userCreate' => ['order_sn', 'uid', 'aid', 'pay_type', 'amount', 'pay_status'],
        'userGet' => ['uid'],
    ];
}
