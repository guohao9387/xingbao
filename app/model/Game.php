<?php

namespace app\model;

use support\Redis;
use think\Model;
use think\model\concern\SoftDelete;

class Game extends Model
{
    use SoftDelete;
    protected $pk = 'gid';
    protected $name = 'Game';
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
    public static function onAfterUpdate($data)
    {
        Game::getGameData(true);
    }
   
    public static function getGameData($forceUpdate = false)
    {

        $keyName = 'game:data';
        $gameData = Redis::get($keyName);
        if (empty($gameData) || $forceUpdate) {
            $data = Game::column('*', 'gid');
            $gameData = json_encode($data);
            Redis::set($keyName, $gameData, "EX",  365 * 24 * 3600);
        }
        $data = json_decode($gameData, true);
        return $data;
    }
}
