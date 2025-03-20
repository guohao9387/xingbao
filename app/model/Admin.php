<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Admin extends Model
{

    use SoftDelete;
    protected $pk = 'aid';
    protected $name = 'admin';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;


    protected function setPasswordAttr($value)
    {
        return md5($value);
    }
}
