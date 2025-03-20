<?php

namespace app\controller;

use app\model\Banner;
use support\Request;

class Index
{
    public function index(Request $request)
    {

        // $bannerData =    Banner::field('image,pid')->order('sort', 'desc')->select();

        // return view('index/index', ['banner_data' => $bannerData]);
    }
}
