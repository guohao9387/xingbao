<?php

namespace app\validate;

use app\model\User as ModelUser;
use think\Validate;
use app\model\UserFundLog;

class User extends Validate
{
    protected $rule = [
        'uid' => 'require',
        // 'pid' => 'require|checkPid',
        'user_name' => 'require|mobile|checkUserName',
        'password' => 'require|regex:[a-zA-Z0-9]{6,16}',
        // 'trade_password' => 'require|regex:[a-zA-Z0-9]{6,16}',
        // 'number' => 'require|length:1,8|checkNumber',
        'nick_name' => 'require|length:1,20|chsAlphaNum|checkNickName',
        'image' => 'require',
        // 'sex' => 'require|in:0,1,2',
        // 'birthday' => 'require|checkBirthday',
        // 'autograph' => 'require|length:1,50',
        'real_status' => 'require|in:1,2',
        'real_name' => 'require|chs',
        'card_id' => 'require|idCard',
        // 'card_image_z' => 'require',
        // 'card_image_f' => 'require',
        // 'alipay_real_name' => 'require|chs',
        // 'alipay_account' => 'require',
        // 'gift_show_status' => 'in:1,2',
        // 'test_status' => 'in:1,2|checkTestStatus'

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'uid.require' => '信息不存在',
        'user_name.require' => '用户名必须',
        'user_name.mobile' => '用户名必须为手机号',
        // 'number.require' => '编号必须',
        // 'number.length' => '编号必须1~8位',
        'password.require' => '密码必须',
        'password.regex' => '密码必须6-16位字母或者数字组成',
        'trade_password.require' => '交易密码必须',
        'trade_password.regex' => '交易密码必须6-16位字母或者数字组成',
        'nick_name.require' => '昵称必须',
        'nick_name.length' => '昵称长度限制1~20个字符',
        'nick_name.chsAlphaNum' => '昵称只能是汉字、字母、数字',
        'image.require' => '头像必须',
        // 'sex.require' => '性别必须',
        // 'sex.in' => '性别参数非法',
        // 'birthday.require' => '生日必须',
        // 'autograph.require' => '个性签名必须',
        // 'autograph.length' => '个性签名长度限制1~50个字符',
        'real_status.in' => '实名状态参数非法',
        'real_name.require' => '真实姓名必须',
        'real_name.chs' => '真实姓名必须为中文字符',
        'card_id.require' => '身份证号必须',
        'card_id.idCard' => '身份证号格式错误',
        // 'card_image_z.require' => '身份证正面必须',
        // 'card_image_f.require' => '身份证反面必须',
        'alipay_real_name.require' => '支付宝真实姓名必须',
        'alipay_real_name.chs' => '支付宝真实姓名必须为中文字符',
        'alipay_account.require' => '支付宝账号必须',
        // 'gift_show_status.in' => '礼物墙显示状态参数非法',

    ];

    protected $scene = [
        'adminGet' => ['uid'],
        'adminModify' => ['uid',   'user_name',],
        'apiRegister' => ['user_name', 'password'],
        'apiForgotPassword' => ['user_name', 'password'],
        'apiModifyUserNickName' => ['uid', 'nick_name'],
        'apiModifyPassword' => ['uid', 'password'],
        'apiModifyTradePassword' => ['uid', 'trade_password'],
        'apiModifyUserInfo' => ['uid', 'nick_name', 'image', ],
        'apiUserCertification' => ['uid', 'real_name', 'card_id'],
        'apiModifyAlipayAccount' => ['uid', 'alipay_real_name', 'alipay_account'],
        'apiGet' => ['uid'],
    ];


    // 登录验证场景定义   
    public function sceneApiLogin()
    {
        return $this->only(['user_name', 'password'])
            ->remove('user_name', 'checkUserName')
            ->remove('password', 'regex');
    }

    // 登录验证场景定义
    public function sceneApiLoginBySmsCode()
    {
        return $this->only(['user_name'])
            ->remove('user_name', 'checkUserName');
    }

    // 登录验证场景定义
    public function sceneApiForgotPassword()
    {
        return $this->only(['user_name', 'password'])
            ->remove('user_name', 'checkUserName');
    }


    protected function checkPid($value, $rule, $data = [])
    {
        if (!empty($data['pid'])) {
            $info = ModelUser::getByUid($data['pid']);
            if (empty($info)) {
                return "上级用户不存在";
            }
        }
        return true;
    }

    protected function checkUserName($value, $rule, $data = [])
    {
        $info = ModelUser::getByUserName($data['user_name']);
        if (!empty($info)) {
            if (empty($data['uid'])) {
                return "用户名已存在";
            } else {
                if ($data['uid'] != $info['uid']) {
                    return "用户名已存在";
                }
            }
        }

        return true;
    }
    protected function checkNickName($value, $rule, $data = [])
    {
        $info = ModelUser::getByNickName($data['nick_name']);
        if (!empty($info)) {
            if (empty($data['uid'])) {
                return "昵称已存在";
            } else {
                if ($data['uid'] != $info['uid']) {
                    return "昵称已存在";
                }
            }
        }

        return true;
    }

    protected function checkNumber($value, $rule, $data = [])
    {
        $info = ModelUser::getByNumber($data['number']);
        if (!empty($info)) {
            if (empty($data['uid'])) {
                return "编号已存在";
            } else {
                if ($data['uid'] != $info['uid']) {
                    return "编号已存在";
                }
            }
        }
        return true;
    }


    protected function checkBirthday($value, $rule, $data = [])
    {
        if (strtotime(date('Y-m-d H:i:s', $data['birthday'])) != $data['birthday']) {
            return "请输入合法生日日期";
        }
        return true;
    }

}
