<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;



class UserLoginLog extends Model
{
    use SoftDelete;
    protected $pk = 'lid';
    protected $name = 'UserLoginLog';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public static function getDeviceOsData()
    {
        $data = [0 => '未知', 1 => '安卓', 2 => '苹果'];
        return $data;
    }
    public  function getDeviceOsTextAttr($value, $data)
    {
        $status = $this::getDeviceOsData();
        return empty($status[$data['device_os']]) ? '' : $status[$data['device_os']];
    }
}
