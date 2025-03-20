<?php

namespace app\api\controller;

use app\api\model\Page as ModelPage;
use support\Request;

class Page
{
    //获得轮播图列表
    public function getPageInfo(Request $request)
    {
        $data = $request->alone(['pid']);
        $result = ModelPage::getPageInfo($request->authInfo, $data);
        return encryptedJson($result);
    }
}
