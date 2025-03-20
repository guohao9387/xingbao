<?php

namespace app\api\controller;

use app\api\model\Banner as ModelBanner;
use support\Request;

class Banner
{
    //获得轮播图列表
    public function getBannerList(Request $request)
    {
        $result = ModelBanner::getBannerList($request->authInfo);
        return encryptedJson($result);
    }
}
