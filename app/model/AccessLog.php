<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class AccessLog extends Model
{
    use SoftDelete;
    protected $pk = 'lid';
    protected $name = 'AccessLog';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
}
