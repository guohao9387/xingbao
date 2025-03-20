<?php

namespace app\validate;

use app\model\Room;
use app\model\Suggest as ModelSuggest;
use app\model\User;
use think\Validate;


class Suggest extends Validate
{
    protected $rule = [
        'sid' => 'require',
        'type' => 'require|checkType',
        'image' => 'checkImage',
        'identity_type' => 'require|in:1,2',
        'id' => 'require|checkId',
        'content' => 'require|length:5,500',
        'deal_status' => 'require|checkDealStatus',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'sid.require' => '信息不存在',
        'type.require' => '投诉建议类型必须',
        'identity_type.require' => '举报对象类型必须',
        'id.require' => '举报对象必须',
        'content.require' => '投诉建议内容不能为空',
        'content.require' => '投诉建议内容不能为空',
    ];

    protected $scene = [
        'adminGet' => ['sid'],
        'adminModify' => ['sid', 'deal_status'],
        'apiCreate' => ['type', 'identity_type', 'id',  'image', 'content'],
    ];

    protected function checkDealStatus($value, $rule, $data = [])
    {
        $data_info = ModelSuggest::getDealStatusData();
        if (empty($data_info[$data['deal_status']])) {
            return "处理类型错误";
        }
        return true;
    }

    protected function checkType($value, $rule, $data = [])
    {
        $data_info = ModelSuggest::getTypeData();
        if (empty($data_info[$data['type']])) {
            return "投诉建议类型错误";
        }
        return true;
    }
    protected function checkImage($value, $rule, $data = [])
    {
        if (!empty([$data['image']])) {
            $imageArray = explode(',', $value);
            if (count($imageArray) > 5) {
                return "最多支持上传5张图";
            }
        }
        return true;
    }

    protected function checkId($value, $rule, $data = [])
    {
        if ($data['identity_type'] == 1) {
            $info = Room::getByRid($data['id']);
            if (empty($info)) {
                return "举报对象不存在";
            }
        } elseif ($data['identity_type'] == 2) {
            $info = User::getByUid($data['id']);
            if (empty($info)) {
                return "举报对象不存在";
            }
        }
        return true;
    }
}
