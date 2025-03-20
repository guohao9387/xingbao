<?php

namespace app\admin\controller;

use app\admin\model\Console as ModelConsole;
use support\Request;

class Console
{
    //获得顶部统计数据
    public function getTotalData()
    {
        $result = ModelConsole::getTotalData();
        return json($result);
    }
    //获得每日新增用户数据

    public function getChatData()
    {
        $result = ModelConsole::getChatData();
        return json($result);
    }
}
