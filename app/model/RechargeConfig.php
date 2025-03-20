<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class RechargeConfig extends Model
{
    use SoftDelete;
    protected $pk = 'cid';
    protected $name = 'RechargeConfig';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    protected function setAttrListAttr($value)
    {
        if (!empty($value) && is_array($value)) {
            return implode(",", $value);
        } else {
            return empty((int)$value) ? '' : (int)$value;
        }
    }

    public function getAttrListAttr($value)
    {
        return explode(',', $value);
    }
}
