<?php

namespace app\validate;

use app\model\AccountOrder as ModelAccountOrder;
use think\Validate;


class AccountOrder extends Validate
{
    protected $rule = [
        'oid' => 'require',
        'gid' => 'require',
        'aid' => 'require',
        'sell_uid' => 'require',
        'buy_uid' => 'require',
        'account_amount' => 'require',
        'server_amount' => 'require',
        'amount' => 'require',
        'pay_status' => 'require|checkPayStatus',
        'order_status' => 'require|checkOrderStatus',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'oid.require' => '信息不存在',
        'pay_status.require' => '支付状态不能为空',
        'order_status.require' => '订单状态不能为空',
        'gid.require' => '商品种类不能为空',
        'aid.require' => '商品id不能为空',
        'sell_uid.require' => '卖家id不能为空',
        'buy_uid.require' => '买家id不能为空',
        'account_amount.require' => '账户金额不能为空',
        'server_amount.require' => '服务器金额不能为空',
        'amount.require' => '订单金额不能为空',
        'order_no.require' => '订单号不能为空',
        'order_amount.require' => '订单金额不能为空',
        'pay_amount.require' => '支付金额不能为空',
    ];


    protected $scene = [
        'adminGet' => ['oid'],
        'adminModify' => ['oid','pay_status','order_status'],
        'adminDakuan' => ['oid','order_status'],
        'adminDelete' => ['oid'],
        'userCreate' => ['aid','gid','pay_status','order_status','order_no','order_amount','pay_amount'],
        'userGet' => ['oid'],
    ];

    protected function checkPayStatus($value, $rule, $data = [])
    {
        $status = ModelAccountOrder::getPayStatusData();
        if (!array_key_exists($value, $status)) {
            return '支付状态错误';
        }
        return true;
    }

    protected function checkOrderStatus($value, $rule, $data = [])
    {
        $status = ModelAccountOrder::getOrderStatusData();
        if (!array_key_exists($value, $status)) {
            return '订单状态错误';
        }
        return true;
    }
}
