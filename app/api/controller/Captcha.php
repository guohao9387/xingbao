<?php

namespace app\api\controller;

use support\Request;
use Gregwar\Captcha\CaptchaBuilder;
use support\Redis;

class Captcha
{
    /**
     * 输出验证码图像
     */
    public function captcha(Request $request)
    {
        // 初始化验证码类
        $builder = new CaptchaBuilder;
        // 生成验证码
        $builder->build();
        // 将验证码的值存储到session中
        $captchaKey =  generateRandom(10);
        $keyName = "captcha:" . $captchaKey;
        Redis::set($keyName, strtolower($builder->getPhrase()), "EX", 60);
        // 获得验证码图片二进制数据
        $img_content = $builder->get();
        // 输出验证码二进制数据
        $data = [];
        $data['captcha_key'] = $captchaKey;
        $data['captcha_base64'] = 'data:image/jpg;base64,' . base64_encode($img_content);
        return encryptedJson(['code' => 200, 'msg' => '获取成功', 'data' => $data]);
    }
}
