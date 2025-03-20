<?php

namespace app\admin\model;

use app\model\SystemMessage as ModelSystemMessage;
use app\model\User;
use think\Model;

class SystemMessage extends Model
{

    public static function getSystemMessageList($data)
    {
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'smid';
        $data['sort'] = !empty($data['sort']) ? $data['sort'] : 'desc';
        $map = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, ['page', 'limit', 'order', 'sort'])) {
                if ($v != "") {
                    if (in_array($k, ['title'])) {
                        $map[] = [$k, 'like', "%$v%"];
                    } else {
                        $map[] = [$k, '=', $v];
                    }
                }
            }
        }

        $model =  ModelSystemMessage::where($map);
        $list =  $model->page($data['page'], $data['limit'])->order($data['order'], $data['sort'])->select();
        foreach ($list as $k => &$v) {
            $v->push_type_text = $v->push_type_text;
        }
        $totalRowData = $model->field('count(1) as count')->find();
        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $data['totalRow'] = $totalRowData;
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
    public static function getSystemMessage($data)
    {
        $validate = new \app\validate\SystemMessage;
        if (!$validate->scene('adminGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $info = ModelSystemMessage::where('smid', $data['smid'])->findOrEmpty();
        if ($info->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $info];
    }

    public static function createSystemMessage($data)
    {
        $validate = new \app\validate\SystemMessage;
        if (!$validate->scene('adminCreate')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelSystemMessage::create($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '创建失败', 'data' => null];
        }

        return ['code' => 200, 'msg' => '创建成功', 'data' => null];
    }

    public static function modifySystemMessage($data)
    {
        $validate = new \app\validate\SystemMessage;
        if (!$validate->scene('adminModify')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $info = ModelSystemMessage::where('smid', $data['smid'])->findOrEmpty();
        if ($info->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }

        $result = ModelSystemMessage::update($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '更新失败', 'data' => null];
        }
    

        return ['code' => 200, 'msg' => '更新成功', 'data' => null];
    }

    public static function deleteSystemMessage($data)
    {
        $validate = new \app\validate\SystemMessage;
        if (!$validate->scene('adminDelete')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelSystemMessage::destroy($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '删除失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '删除成功', 'data' => null];
    }
}
