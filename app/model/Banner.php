<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Banner extends Model
{
    use SoftDelete;
    protected $pk = 'bid';
    protected $name = 'Banner';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;


    public function getImageAttr($value)
    {
        return storageNetworkAddress($value);
    }

    public function setImageAttr($value)
    {
        return storageLocalAddress($value);
    }

}
