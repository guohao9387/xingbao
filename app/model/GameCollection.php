<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class GameCollection extends Model
{
    use SoftDelete;
    protected $pk = 'id';
    protected $name = 'GameCollection';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
}
