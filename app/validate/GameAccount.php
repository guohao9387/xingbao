<?php

namespace app\validate;


use think\Validate;


class GameAccount extends Validate
{
    protected $rule = [
        'aid' => 'require',
        'gid' => 'require',
        'uid' => 'require',
        'game_account' => 'require',
        'game_pwd' => 'require',
        'weixin' => 'require',
        'qq' => 'number',
        'tel' => 'require',
        'sid' => 'require',
        'game' => 'require',
        'title' => 'require',
        'sell_price' => 'require',
        'image' => 'require',
        'status' => 'require|in:0,1,2,3,4,5',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'aid.require' => '信息不存在',
        'gid.require' => '必须有游戏类型',
        'game.require' => '必须有游戏类型',
        'title.require' => '必须有一个标题',
        'image.require' => '必须有一个封面图片',
        'sell_price.require' => '请输入正确的单价',
        'uid.require' => '必须有用户',
        'game_account.require' => '必须有游戏账号',
        'game_pwd.require' => '必须有游戏密码',
        'weixin.require' => '必须有微信',
        'qq.require' => '必须有qq',
        'tel.require' => '必须有电话号码',
        'sid.require' => '必须有游戏区服',
    ];

    protected $scene = [
        'adminGet' => ['aid'],
        'adminModify' => ['aid','status'],
        'adminCreate' => ['image'],
        'adminDelete' => ['aid'],
        'userCreate' => ['gid','game','title','image','sell_price','uid','game_account','game_pwd','weixin','qq','tel','sid'],
        'userModify' => ['title','image','sell_price','uid','game_account','game_pwd','weixin','qq','tel','aid'],

    ];
}
