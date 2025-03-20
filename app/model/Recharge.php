<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Recharge extends Model
{
    use SoftDelete;
    protected $pk = 'rid';
    protected $name = 'Recharge';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;


    protected static function setOrderSn()
    {
        $order_sn = 'CZ' . date('YmdHis') . mt_rand(1000, 9999);
        //检测是否靓号
        $info = Recharge::where(['order_sn' => $order_sn])->find();
        if (!empty($info)) {
            Recharge::setOrderSn();
        } else {
            return $order_sn;
        }
    }

    public static function onBeforeInsert($data)
    {
        $data->order_sn = Recharge::setOrderSn();
    }

    public static function getPayStatusData()
    {
        $data = [1 => '待支付', 2 => '已支付'];
        return $data;
    }
    public function getPayStatusTextAttr($value, $data)
    {
        $status = $this::getPayStatusData();
        return $status[$data['pay_status']];
    }

    public static function getPayTypeData()
    {
        $data = [0 => '未知', 1 => '支付宝APP', 2 => '微信APP', 3 => '连连支付', 4 => 'adaPay'];
        return $data;
    }
    public function getPayTypeTextAttr($value, $data)
    {
        $status = $this::getPayTypeData();
        return $status[$data['pay_type']];
    }

    public static function getLianlianTYpeAttr()
    {
        return [
            'WECHAT_JSAPI',
            'WECHAT_NATIVE',
            'WECHAT_APPLET',
            'ALIPAY_NATIVE',
            'ALIPAY_APPLET',
            'WECHAT_APP',
            'WECHAT_LLAPPLET',
            'WECHAT_H5',
            'DC_NATIVE',
            'DC_APP',
            'POS_NATIVE',
            'AGGREGATE_CODE',
            'STATIC_CODE',
            'CLOUDPAY_APP',
            'CLOUDPAY_WAP',
            'COUPON_PAY',
            'CLOUDPAY_APPL'
        ];
    }
}
