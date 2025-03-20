<?php

namespace app\api\model;


use app\model\Page as ModelPage;
use think\Model;

class Page extends Model
{
    public static function getPageInfo($authInfo, $data)
    {
        $validate = new \app\validate\Page;
        if (!$validate->scene('apiGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $info = ModelPage::field('pid,title,content')->findOrEmpty($data['pid']);
        if ($info->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $info];
    }
}
