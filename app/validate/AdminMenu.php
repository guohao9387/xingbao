<?php

namespace app\validate;

use think\Validate;

class AdminMenu extends Validate
{

    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */

    protected $rule = [
        'mid' => 'require',
        'pid' => 'require',
        'title' => 'require',
        'type' => 'require|in:0,1,2',
        'page_href' => 'requireIf:type,1',
        'server_url' => 'requireIf:type,2',
        'open_type' => 'require',
        'show_status' => 'require|in:1,2',
        'sort' => 'require',

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'mid.require' => '信息不存在',
        'pid.require' => '上级菜单必须',
        'title.require' => '名称必须',
        'type.require' => '菜单类型必须',
        'page_href.requireIf' => '页面地址必须',
        'server_url.requireIf' => '功能权限标识必须',
        'open_type.require' => '打开类型必须',
        'show_status.require' => '显示状态',
        'show_status.in' => '登录状态非法',
        'sort.require' => '排序必须',

    ];

    protected $scene = [
        'adminCreate' => ['pid', 'title', 'type', 'page_href', 'server_url', 'open_type', 'login_status', 'sort'],
        'adminModify' => ['mid', 'title', 'type', 'page_href', 'server_url', 'open_type', 'login_status', 'sort'],
        'adminGet' => ['mid'],
        'adminDelete' => ['mid'],
    ];
}
