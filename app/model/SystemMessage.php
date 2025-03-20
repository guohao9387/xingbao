<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class SystemMessage extends Model
{
    use SoftDelete;
    protected $pk = 'smid';
    protected $name = 'SystemMessage';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    protected function setContentAttr($value)
    {
        return empty($value) ? '' : htmlspecialchars_decode($value);
    }

    public static function getPushTypeData()
    {
        $data = [1 => '不推送', 2 => '推送'];
        return $data;
    }
    public  function getPushTypeTextAttr($value, $data)
    {
        $status = $this::getPushTypeData();
        return $status[$data['push_type']];
    }
}
