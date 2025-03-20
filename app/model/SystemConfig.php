<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;
use support\Redis;

class SystemConfig extends Model
{
    use SoftDelete;
    protected $pk = 'cid';
    protected $name = 'SystemConfig';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public static function onAfterUpdate($data)
    {
        //修改后更新缓存
        $keyName = 'systemConfig';
        $data = SystemConfig::column('key_value', 'key_name');
        $sysConfigData = json_encode($data);
        Redis::set($keyName, $sysConfigData, "EX",  365 * 24 * 3600);
    }

    public function getTypeTextAttr($value, $data)
    {
        $status = [0 => '未知', 1 => '基础配置', 2 => '高级配置'];
        return $status[$data['type']];
    }

    public static function getSystemConfigData()
    {
        $keyName = 'systemConfig';
        $sysConfigData = Redis::get($keyName);
        if (empty($sysConfigData)) {
            $data = SystemConfig::column('key_value', 'key_name');
            $sysConfigData = json_encode($data);
            Redis::set($keyName, $sysConfigData, "EX",  365 * 24 * 3600);
        }
        $data = json_decode($sysConfigData, true);
        return $data;
    }
    public static function getValueByName($name)
    {
        $keyName = 'systemConfig';
        $sysConfigData = Redis::get($keyName);
     
        if (empty($sysConfigData) || 1) {
            $data = SystemConfig::column('key_value', 'key_name');
            $sysConfigData = json_encode($data);
            Redis::set($keyName, $sysConfigData, "EX",  365 * 24 * 3600);
        }
        $data = json_decode($sysConfigData, true);
        if (empty($name)) {
            return '';
        }
        if (isset($data[$name])) {
            return $data[$name];
        } else {
            return '';
        }
    }
}
