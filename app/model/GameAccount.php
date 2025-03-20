<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class GameAccount extends Model
{
    use SoftDelete;
    protected $pk = 'aid';
    protected $name = 'GameAccount';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public function getImageAttr($value)
    {
        return storageNetworkAddress($value);
    }

    public function setImageAttr($value)
    {
        return storageLocalAddress($value);
    }
    public static function getStatusData()

    {
        $data = [1 => '未审核',  2 => '已上架',  3 => '审核失败',  4 => '用户下架',  5=> '后台下架',  6=> '已售出'];
        return $data;
    }
    public  function getStatusTextAttr($value, $data)
    {
        $status = $this::getStatusData();
        return $status[$data['status']];
    }
}
