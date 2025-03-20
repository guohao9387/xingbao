<?php

namespace app\validate;

use app\model\UserLoginLog as UserLoginLogModel;
use think\Validate;


class UserLoginLog extends Validate
{
    protected $rule = [
        'lid' => 'require',
        'uid' => 'require',
        'device_name' => 'require',
        'device_os' => 'require|checkDeviceOs',
        'device_id' => 'require',
        'sys_version' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'lid.require' => '信息不存在',
        'uid.require' => '用户信息不存在',
        'device_name.require' => '设备名称',
        'device_os.require' => '设备号',
        'device_id.require' => '设备ID',
        'sys_version.require' => '系统版本号',

    ];

    protected $scene = [
        'adminGet' => ['lid'],
        'apiCreate' => ['uid', 'device_name', 'device_os', 'device_id', 'sys_version'],
    ];

    protected function checkDeviceOs($value, $rule, $data = [])
    {
        $data_info = UserLoginLogModel::getDeviceOsData();
        if (empty($data_info[$data['device_os']])) {
            return "设备类型错误";
        }
        return true;
    }
}
