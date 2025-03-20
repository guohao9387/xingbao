<?php

namespace app\admin\controller;

use app\admin\model\Banner as ModelBanner;
use support\Request;

class Banner
{
    public function getBannerList(Request $request)
    {
        $data = $request->only(['bid',  'order', 'sort', 'page', 'limit']);
        $result = ModelBanner::getBannerList($data);
        return json($result);
    }
    public function getBanner(Request $request)
    {
        $data = $request->only(['bid']);
        $result = ModelBanner::getBanner($data);
        return json($result);
    }
    public function createBanner(Request $request)
    {
        $data = $request->only(['pid', 'image', 'sort']);
        $result = ModelBanner::createBanner($data);
        return json($result);
    }
    public function modifyBanner(Request $request)
    {
        $data = $request->only(['bid', 'pid', 'image', 'sort']);
        $result = ModelBanner::modifyBanner($data);
        return json($result);
    }
    public function deleteBanner(Request $request)
    {
        $data = $request->only(['bid']);
        $result = ModelBanner::deleteBanner($data);
        return json($result);
    }
}
