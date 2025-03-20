<?php

namespace App\admin\model;

use app\model\Sms as ModelSms;
use think\Model;

class Sms extends Model
{
    //发送验证码
    public static function sendCode($data, $request)
    {
        $appCaptchaCheckModel = get_system_config('app_captcha_check_model');
        if ($appCaptchaCheckModel == 1) {
            if (empty($data['captcha']) || strtolower($data['captcha']) !== $request->session()->get('captcha')) {
                return json(['code' => 400, 'msg' => '输入的验证码不正确']);
            }
            if (floor($data['user_name']) != $data['user_name'] || strlen($data['user_name']) != 11) {
                return ['code' => 201, 'msg' => '手机号格式不正确', 'data' => null];
            }
            if (empty($data['captcha']) || strtolower($data['captcha']) !== $request->session()->get('captcha')) {
                return ['code' => 400, 'msg' => '图形验证验证码不正确'];
            }
        }
        $result = ModelSms::sendCode($data);
        return $result;
    }
}
