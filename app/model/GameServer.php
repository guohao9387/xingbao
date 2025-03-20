<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class GameServer extends Model
{
    use SoftDelete;
    protected $pk = 'sid';
    protected $name = 'GameServer';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    
}
