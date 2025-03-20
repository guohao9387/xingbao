<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class AdminMenu extends Model
{
    use SoftDelete;
    protected $pk = 'mid';
    protected $name = 'AdminMenu';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;


    public function getTypeAttr($value)
    {
        $status = [0 => '目录', 1 => '菜单', 2 => '功能'];
        return $status[$value];
    }
    public function getShowStatusAttr($value)
    {
        $status = [1 => '显示', 2 => '隐藏'];
        return $status[$value];
    }
}
