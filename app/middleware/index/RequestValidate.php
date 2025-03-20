<?php

namespace app\middleware\index;


use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Redis;


class RequestValidate implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $requestrAndResponseConfigData =  config("customConfig.requestrAndResponseConfig");
        //判断是否开启验证
        if (!$requestrAndResponseConfigData['request']['enable']) {
            return $handler($request); //如果未开启直接执行后续操作
        }
        $requestData = $request->post(); //获取所有POST请求数据
        if ($requestrAndResponseConfigData['request']['encryptStatus']) {
            $requestPostData = $request->only(['encrypted_data']); //获取请求数据
            if (empty($requestPostData['encrypted_data'])) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode(['code' => 201, 'msg' => 'encrypted_data参数缺失', 'data' => null]));
            }
            //数据进行解密
            //加密数据格式： 接口数组所需参数使用json编码、openssl_encrypt 进行加密 、base64_encode编码
            //接密数据格式： 接口数组所需参数使用 base64_decode解码、openssl_decrypt 进行解密、base64_decode编码
            $jsonData = decryptWithOpenssl($requestPostData['encrypted_data'], $requestrAndResponseConfigData['secretKey'],  $requestrAndResponseConfigData['iv']);
            //传递参数给下一步
            $requestData = json_decode($jsonData, true);
        }

        //验证请求参数是否合规
        $result = $this->validateSignature($requestData);
        if ($result['code'] != 200) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode(['code' => 201, 'msg' => $result['msg'], 'data' => null]));
        }
        $request->requestData  = $requestData;
        return $handler($request);
    }

    //数据验证
    public function validateSignature(array $data)
    {
        //数据验证参数说明
        /*
         1.API接口基本参数 比如 uid=100  API接口本身接口参数
         2.validate_timestamp   请求当前秒时间戳 10位
         3.validate_nonceStr    随机6位字符串
         4.validate_signature   1、2、3 在放入同一个数组，根据键值进行正序排列、构造为url进行形式 例如：uid=1&validate_nonceStr=2fe4c0&validate_timestamp=17133591735580f9cf686f3b7b518a47b765e1860e
         5.上一步接口拼接 secretKey：MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDc9  获得  uid=1&validate_nonceStr=2fe4c0&validate_timestamp=17133591735580f9cf686f3b7b518a47b765e1860eMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDc9
         6.上一步接口进行md5加密获得  c30f4b781755fdca02d5ba5e8ee2c11c
         */
        $requestrAndResponseConfigData =  config("customConfig.requestrAndResponseConfig");
        if (empty($data['validate_timestamp']) || empty($data['validate_nonceStr']) || empty($data['validate_signature'])) {
            return ['code' => 201, 'msg' => '参数非法', 'data' => null];
        }
        //判断请求时间是否已过期
        if ($requestrAndResponseConfigData['request']['timeout'] > 0 && time() - $requestrAndResponseConfigData['request']['timeout'] > $data['validate_timestamp']) {
            return ['code' => 201, 'msg' => '请求超时', 'data' => null];
        }
        //是否重放攻击
        if ($requestrAndResponseConfigData['request']['replay']) {
            $replayRedisKeyName = "middleware:replay:" . $data['validate_timestamp'] . $data['validate_nonceStr'];
            $replayStatus = Redis::setNx($replayRedisKeyName, 1);
            if (!$replayStatus) {
                return ['code' => 201, 'msg' => '请求参数已失效', 'data' => null];
            }
            Redis::expireAt($replayRedisKeyName, time() + 10); //定时删除
        }

        //验证签名是否合法
        $validate_signature = $data['validate_signature'];
        unset($data['validate_signature']);
        ksort($data); //升序排序
        $queryString = http_build_query($data);
        $queryStringSignature = md5($queryString . $requestrAndResponseConfigData['secretKey']);
        if ($queryStringSignature != $validate_signature) {
            return ['code' => 201, 'msg' => '签名验证失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '验证成功', 'data' => null];
    }
}
