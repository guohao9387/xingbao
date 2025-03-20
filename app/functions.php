<?php

/**
 * Here is your custom functions.
 */


use app\model\SystemConfig;
use app\model\Upload;
use support\Redis;
use support\Response;

if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed $vars 要输出的变量
     * @return void
     */
    function dump(...$vars)
    {
        ob_start();
        var_dump(...$vars);

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_SUBSTITUTE);
            }
            $output = '<pre>' . $output . '</pre>';
        }

        echo $output;
    }
}



//获取系统配置信息
function get_system_config($key_name = "")
{
    return SystemConfig::getValueByName($key_name);
}

//本地地址转换为网络地址
function storageNetworkAddress($path)
{
    return Upload::storageNetworkAddress($path);
}
//网络地址转换为本地地址
function storageLocalAddress($path)
{
    return Upload::storageLocalAddress($path);
}

//生成随机数
function generateRandom($num = 0)
{
    $code = strtolower('ABCDEFGHIJKLMNOPQRSTUVWXYZ23456789');
    $str = '';
    for ($i = 0; $i < $num; $i++) {
        $str .= $code[mt_rand(0, strlen($code) - 1)];
    }
    return $str;
}
//Curl请求
function myCurl($url, $post_data = array(), $header = array(), $cookie = "")
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出
    $curl_header = array();
    if (!empty($header)) {
        foreach ($header as $k => $v) {
            $curl_header[] = "$k:$v";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_header);
    }

    if (!empty($post_data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    }
    if (!empty($cookie)) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}



function getBetweenTime($timeType = 0)
{

    switch ($timeType) {
        case 1:
            // 今天
            $startTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $endTime   = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            break;
        case 2:
            // 昨天
            $startTime = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
            $endTime   = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
            break;
        case 3:
            // 本周
            //$startTime = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
            //$endTime   = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));
            $startTime = strtotime(date('Y-m-d', strtotime("this week Monday", time())));
            $endTime   = strtotime(date('Y-m-d', strtotime("this week Sunday", time()))) + 24 * 3600 - 1;
            break;
        case 4:
            // 上周
            //$startTime = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1-7-7, date('Y'));
            //$endTime   = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7-7-7, date('Y'));
            $startTime = strtotime(date('Y-m-d', strtotime("last  week Monday", time())));
            $endTime   = strtotime(date('Y-m-d', strtotime("last  week Sunday", time()))) + 24 * 3600 - 1;
            break;
        case 5:
            // 近一周
            $startTime = mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'));
            $endTime   = time();
            break;
        case 6:
            // 本月
            $startTime = mktime(0, 0, 0, date('m'), 1, date('Y'));
            $endTime   = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
            break;
        case 7:
            // 上月
            $startTime = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
            $endTime   = mktime(23, 59, 59, date('m'), 0, date('Y'));
            break;
        case 8:
            // 近一月
            $startTime = mktime(0, 0, 0, date('m') - 1, date('d'), date('Y'));
            $endTime   = time();
            break;
        case 9:
            // 近三月
            $startTime = mktime(0, 0, 0, date('m') - 3, date('d'), date('Y'));
            $endTime   = time();
            break;
        case 10:
            // 今年
            $startTime = mktime(0, 0, 0, 1, 1, date('Y'));
            $endTime   = time();
            break;
        case 11:
            // 近一年
            $startTime = mktime(0, 0, 0, date('m'), date('d'), date('Y') - 1);
            $endTime   = time();
            break;
        default:
            // 自定义时间
            // $startTime = input('startTime', '');
            // $startTime = !empty($startTime) ? strtotime($startTime) : '';
            // $endTime   = input('endTime', '');
            // $endTime   = !empty($endTime) ? strtotime($endTime) : '';
            $startTime = 0;
            $endTime = 0;
    }
    return ['startTime' => $startTime, 'endTime' => $endTime];
}
/**
 * 检查验证码
 */
function captchaCheck($captcha_key = "", $captcha = "")
{
    $keyName = "captcha:" . $captcha_key;
    $captchaRedis =  Redis::get($keyName);
    if ($captchaRedis != strtolower($captcha)) {
        return ['code' => 201, 'msg' => '验证码错误', 'data' => null];
    }
    return ['code' => 200, 'msg' => '验证码正确', 'data' => null];
}


function redisLock($key, $value = 1, $time = 10)
{
    $data = [];
    $status = Redis::setNx($key, $value); //直接写if 条件  不生效
    if ($status) {
        //设定成功 则说明不需要锁定  并设置锁定结束时间
        $data['lock_status'] = false;
        $data['return_data'] = '';
        Redis::expireAt($key, time() + $time);
    } else {
        //设定不成功 则说明需要锁定 
        $data['lock_status'] = true;
        $data['return_data'] = new Response(403, ['Content-Type' => 'application/json'], json_encode(['code' => 403, 'msg' => '访问频繁，请稍后重试', 'data' => null]));
    }
    return $data;
}
function redisUnlock($key)
{
    Redis::del($key);
    return true;
}

//加密返回接口信息
function encryptedJson($data, int $options = JSON_UNESCAPED_UNICODE): Response
{
    /*
    $requestrAndResponseConfigData =  config("customConfig.requestrAndResponseConfig");
    if (!$requestrAndResponseConfigData['response']['enable']) {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
    }
    //判断是否显示加密前数据
    if ($requestrAndResponseConfigData['response']['showRawData']) {
        $data['raw_data'] = $data['data'];
    }
    // $data['data'] = (['uid' => 1, 'validate_timestamp' => '1713359173', 'validate_nonceStr' => '2fe4c0', 'validate_signature' => 'c1849f6d4c0016e8972202a0d1777366']);
    //返回参数进行加密
    $data['data'] = encryptWithOpenssl(json_encode($data['data'], $options), $requestrAndResponseConfigData['secretKey'],  $requestrAndResponseConfigData['iv']);
    return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
    */

    $requestrAndResponseConfigData =  config("customConfig.requestrAndResponseConfig");
    if (!$requestrAndResponseConfigData['response']['enable']) {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
    }
    //判断是否显示加密前数据
    if ($requestrAndResponseConfigData['response']['showRawData']) {
        $data['raw_data'] = $data['data'];
    }
    //  $data['data'] = (['uid' => 1, 'validate_timestamp' => '1713359173', 'validate_nonceStr' => '2fe4c0', 'validate_signature' => 'c1849f6d4c0016e8972202a0d1777366']);
    //返回参数进行加密
    $data['data'] = encryptWithOpenssl(json_encode($data['data'], $options), $requestrAndResponseConfigData['secretKey'],  $requestrAndResponseConfigData['iv']);
    return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
}
//解密openssl
function decryptWithOpenssl($data, $key, $iv)
{
    return openssl_decrypt(base64_decode($data), "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
}
//加密openssl
function encryptWithOpenssl($data, $key, $iv)
{
    return base64_encode(openssl_encrypt($data, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv));
}