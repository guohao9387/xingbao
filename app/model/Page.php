<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Page extends Model
{
    use SoftDelete;
    protected $pk = 'pid';
    protected $name = 'Page';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    protected function setContentAttr($value)
    {
        return empty($value) ? '' : htmlspecialchars_decode($value);
    }
}
