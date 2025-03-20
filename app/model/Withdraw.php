<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Withdraw extends Model
{
    use SoftDelete;
    protected $pk = 'wid';
    protected $name = 'Withdraw';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $insert = [];
    protected $update = [];


    protected static function setOrderSn()
    {
        $order_sn = 'TX' . date('YmdHis') . mt_rand(1000, 9999);
        //检测是否靓号
        $info = Withdraw::where(['order_sn' => $order_sn])->find();
        if (!empty($info)) {
            Withdraw::setOrderSn();
        } else {
            return $order_sn;
        }
    }

    public static function onBeforeInsert($data)
    {
        $data->order_sn = Withdraw::setOrderSn();
    }

  
    public static function getPayStatusData()
    {
        $data = [0=>'未操作',1 => '成功', 2 => '失败', 4 => '挂单', 9 => '×', 15 => '取消', -1 => '已无效'];
        return $data;
    }
    public function getPayStatusTextAttr($value, $data)
    {
        $status = $this::getPayStatusData();
        return $status[$data['pay_status']];
    }
    

    public static function getDealStatusData()
    {
        $data = [1 => '处理中', 2 => '已通过', 3 => '已拒绝'];
        return $data;
    }
    public function getDealStatusTextAttr($value, $data)
    {
        $status = $this::getDealStatusData();
        return $status[$data['deal_status']];
    }

    public static function getPayTypeData()
    {
        $data = [1 => '微信', 2 => '支付宝'];
        return $data;
    }
    public function getPayTypeTextAttr($value, $data)
    {
        $status = $this::getPayTypeData();
        return $status[$data['pay_type']];
    }
  
    public function getDealTimeTextAttr($value, $data)
    {
        return !empty($data['deal_time']) ? date('Y-m-d H:i:s', $data['deal_time']) : "";
    }
}
