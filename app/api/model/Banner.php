<?php

namespace app\api\model;


use app\model\Banner as ModelBanner;
use think\Model;

class Banner extends Model
{
    public static function getBannerList()
    {
        $list =  ModelBanner::field('image,pid')->order('sort', 'desc')->select();
        return ['code' => 200, 'msg' => '获取成功', 'data' => ['list' => $list]];
    }
}
