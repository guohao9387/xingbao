<?php

namespace app\admin\model;

use app\model\Banner as ModelBanner;
use think\Model;

class Banner extends Model
{

    public static function getBannerList($data)
    {
        $data['page'] = !empty($data['page']) ? $data['page'] : 1;
        $data['limit'] = !empty($data['limit']) ? $data['limit'] : 15;
        $data['order'] = !empty($data['order']) ? $data['order'] : 'bid';
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

        $model =  ModelBanner::where($map);
        $list =  $model->page($data['page'], $data['limit'])->order($data['order'], $data['sort'])->select();
        $totalRowData = $model->field('count(1) as count')->find();
        $data = [];
        $data['list'] = $list;
        $data['count'] = $totalRowData['count'];
        unset($totalRowData['count']);
        $data['totalRow'] = $totalRowData;
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
    public static function getBanner($data)
    {
        $validate = new \app\validate\Banner;
        if (!$validate->scene('adminGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $info = ModelBanner::findOrEmpty($data['bid']);
        if ($info->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $info];
    }

    public static function createBanner($data)
    {
        $validate = new \app\validate\Banner;
        if (!$validate->scene('adminCreate')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelBanner::create($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '创建失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '创建成功', 'data' => null];
    }

    public static function modifyBanner($data)
    {
        $validate = new \app\validate\Banner;
        if (!$validate->scene('adminModify')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelBanner::update($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '更新失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '更新成功', 'data' => null];
    }

    public static function deleteBanner($data)
    {
        $validate = new \app\validate\Banner;
        if (!$validate->scene('adminDelete')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelBanner::destroy($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '删除失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '删除成功', 'data' => null];
    }
}
