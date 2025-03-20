<?php

namespace app\admin\controller;

use app\admin\model\Admin as ModelAdmin;
use App\admin\model\Sms;
use app\model\SystemConfig;

use Gregwar\Captcha\CaptchaBuilder;
use support\Request;

class Admin
{
    public function login(Request $request)
    {
        $data = $request->only(['user_name', 'password', 'captcha']);
        $appCaptchaCheckModel = SystemConfig::getValueByName('app_captcha_check_model');
        if ($appCaptchaCheckModel == 1) {
            if (empty($data['captcha']) || strtolower($data['captcha']) !== $request->session()->get('captcha')) {
                return json(['code' => 400, 'msg' => '输入的验证码不正确']);
            }
        }

        $result = ModelAdmin::adminLogin($data);
        return json($result);
    }

    public function loginBySmsCode(Request $request)
    {
        $data = $request->only(['user_name', 'code', 'password']);
        $keyName = 'admin:admin:loginBySmsCode:' . $data['user_name'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelAdmin::adminLoginBySms($data);
        redisUnlock($keyName);
        return json($result);
    }

    public function sendCode(Request $request)
    {
        $data = $request->only(['user_name', 'captcha']);
        $keyName = 'admin:Admin:sendCode:' . $data['user_name'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = Sms::sendCode($data, $request);
        redisUnlock($keyName);
        return json($result);
    }

    public function captcha(Request $request)
    {
        // 初始化验证码类
        $builder = new CaptchaBuilder;
        // 生成验证码
        $builder->build();
        // 将验证码的值存储到session中
        $request->session()->set('captcha', strtolower($builder->getPhrase()));
        // 获得验证码图片二进制数据
        $img_content = $builder->get();
        // 输出验证码二进制数据
        return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

    public function getAdminList(Request $request)
    {
        $data = $request->only(['user_name', 'nick_name', 'order', 'sort', 'page', 'limit']);
        $result = ModelAdmin::getAdminList($data);
        return json($result);
    }

    public function getAdminInfo(Request $request)
    {
        $data = $request->only(['aid']);
        $result = ModelAdmin::getAdminInfo($data);
        return json($result);
    }

    public function createAdmin(Request $request)
    {
        $data = $request->only(['user_name', 'password', 'nick_name', 'role_id', 'login_status']);
        $result = ModelAdmin::createAdmin($data);
        return json($result);
    }

    public function modifyAdmin(Request $request)
    {
        $data = $request->only(['aid', 'user_name', 'nick_name', 'role_id', 'login_status']);
        $result = ModelAdmin::modifyAdmin($data);
        return json($result);
    }

    public function deleteAdmin(Request $request)
    {
        $data = $request->only(['aid']);
        $result = ModelAdmin::deleteAdmin($data);
        return json($result);
    }

    public function modifyAdminPassword(Request $request)
    {
        $data = $request->only(['aid', 'password']);
        $result = ModelAdmin::modifyAdminPassword($data);
        return json($result);
    }
}
