<?php

namespace app\admin\model;

use app\model\UserRealCheck as ModelUserRealCheck;
use think\Model;

class UserRealCheck extends Model
{

    public static function getUserRealCheckList($data)
    {
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'rid';
        $data['sort'] = !empty($data['sort']) ? $data['sort'] : 'desc';
        $map = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, ['page', 'limit', 'order', 'sort'])) {
                if ($v != "") {
                    if (in_array($k, ['name'])) {
                        $map[] = [$k, 'like', "%$v%"];
                    } else {
                        $map[] = [$k, '=', $v];
                    }
                }
            }
        }

        $model =  ModelUserRealCheck::where($map);
        $list =  $model->page($data['page'], $data['limit'])->order($data['order'], $data['sort'])->select();
        foreach ($list as $k => $v) {
            $list[$k]['status_text'] = $v->status_text;
        }
        $totalRowData = $model->field('count(1) as count')->find();
        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $data['totalRow'] = $totalRowData;
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }

    public static function modifyUserRealCheck($data)
    {
        $validate = new \app\validate\UserRealCheck;
        if (!$validate->scene('adminModify')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        // dump($data);
        $result = ModelUserRealCheck::update($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '更新失败', 'data' => null];
        }
        $info = ModelUserRealCheck::findOrEmpty($data['rid']);
        return ['code' => 200, 'msg' => '更新成功', 'data' => $info];
    }

    public static function deleteUserRealCheck($data)
    {
        $validate = new \app\validate\UserRealCheck;
        if (!$validate->scene('adminDelete')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelUserRealCheck::destroy($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '删除失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '删除成功', 'data' => null];
    }
    public static function getStatusData()
    {
        $data = ModelUserRealCheck::getStatusData();
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
    public static function getUserRealCheck($data)
    {
        $map=[];
        $map['uid']=session()->get('uid');
       
        $info = ModelUserRealCheck::where($map)->order('id desc')->find($data['rid']);
        return ['code' => 200,'msg' => '获取成功', 'data' => $info];
    }
}
