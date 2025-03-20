<?php

namespace app\api\model;


use app\model\Suggest as ModelSuggest;
use think\Model;

class Suggest extends Model
{

    public static function createSuggest($authInfo, $data)
    {
        $data['uid'] = $authInfo['uid'];
        $validate = new \app\validate\Suggest;
        if (!$validate->scene('apiCreate')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelSuggest::create($data);
        //等待支付对接
        return ['code' => 200, 'msg' => '提交成功', 'data' => null];
    }

    public static function getSuggestTypeData()
    {
        $data = ModelSuggest::getTypeData();
        //等待支付对接
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
}
