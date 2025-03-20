<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class AdminRole extends Model
{
    use SoftDelete;
    protected $pk = 'rid';
    protected $name = 'AdminRole';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;


    protected function setBindMidListAttr($value)
    {
        return implode(',', $value);
    }
}
