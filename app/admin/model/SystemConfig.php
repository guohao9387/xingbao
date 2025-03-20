<?php

namespace app\admin\model;

use app\model\SystemConfig as ModelSystemConfig;
use think\Model;
use support\Redis;

class SystemConfig extends Model
{


    public static function getSystemConfigList($data)
    {
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'sort';
        $data['sort'] = !empty($data['sort']) ? $data['sort'] : 'desc';
        $map = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, ['page', 'limit', 'order', 'sort'])) {
                if ($v != "") {
                    if (in_array($k, ['key_title', 'key_name', 'key_value'])) {
                        $map[] = [$k, 'like', "%$v%"];
                    } else {
                        $map[] = [$k, '=', $v];
                    }
                }
            }
        }
        $model =  ModelSystemConfig::where($map);
        $list =  $model->page($data['page'], $data['limit'])->order([$data['order']=>$data['sort'],'cid'=>'desc'])->select();
        foreach ($list as $k => &$v) {
            $v['type_text'] = $v->type_text;
        }
        $totalRowData = $model->field('count(1) as count')->find();
        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $data['totalRow'] = $totalRowData;
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }

    public static function getSystemConfigInfo($data)
    {
        $validate = new \app\validate\SystemConfig;
        if (!$validate->scene('adminGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $info = ModelSystemConfig::findOrEmpty($data['cid']);
        if ($info->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $info];
    }
    public static function modifySystemConfig($data)
    {
        $validate = new \app\validate\SystemConfig;
        if (!$validate->scene('adminModify')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelSystemConfig::update($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '更新失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '更新成功', 'data' => null];
    }
}
