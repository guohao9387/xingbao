<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Sms extends Model
{
    use SoftDelete;

    protected $pk = 'id';
    protected $name = 'sms';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    //发送验证码
    public static function sendCode($data)
    {
        if (empty($data['user_name'])) {
            return ['code' => 201, 'msg' => '手机号不能为空', 'data' => null];
        }
        /*
        if (empty($data['type'])) {
            return ['code' => 201, 'msg' => '验证码类型必须', 'data' => null];
        }
        //type 1注册  2非注册
       
        $info = User::where('user_name', $data['user_name'])->findOrEmpty();
        switch ($data['type']) {
            case 1:
                if (!$info->isEmpty()) {
                    return ['code' => 201, 'msg' => '该手机号已注册', 'data' => null];
                }
                break;
            case 2:
                if ($info->isEmpty()) {
                    return ['code' => 201, 'msg' => '手机号码未注册', 'data' => null];
                }
                break;
            default:
                return ['code' => 201, 'msg' => '验证码类型非法', 'data' => null];
                break;
        }
        */


        $systemConfigData = SystemConfig::getSystemConfigData();
        $map = [];
        $map[] = ['create_time', '>', strtotime(date('Y-m-d'))];
        $map[] = ['mobile', '=', $data['user_name']];
        $count = Sms::where($map)->count();
        if ($count >= $systemConfigData['sms_max_send']) {
            return ['code' => 201, 'msg' => '超过今日验证码接收限制', 'data' => null];
        }
        $code = mt_rand(1000, 9999);
        $limit_minute = 5; //有效期
        $map = [];
        $map[] = ['status', '=', 1];
        $map[] = ['mobile', '=', $data['user_name']];
        $smsInfo = Sms::where($map)->order('id desc')->findOrEmpty();
        if (!$smsInfo->isEmpty()) {
            if (strtotime($smsInfo->create_time) > time() - 60) {
                return ['code' => 201, 'msg' => '请勿重复发送验证码', 'data' => null];
            }
        }

        $content = '【优音】验证码：' . $code . '，若非本人操作，请勿泄露。';

        $insert_data = [];
        $insert_data['mobile'] = $data['user_name'];
        $insert_data['code'] = $code;
        $insert_data['content'] = $content;
        $insert_data['status'] = 1;
        $insert_data['over_time'] = time() + $limit_minute * 60;
        $smsInfo = Sms::create($insert_data);
        if (!$smsInfo) {
            return ['code' => 201, 'msg' => '发送失败', 'data' => null];
        }
        if ($systemConfigData['sms_send_model'] == 2) {
            $reslut = Sms::sendHuaxinMsg($data['user_name'], $content);
            if ($reslut['code'] != 200) {
                $smsInfo->remarks = $reslut['data'];
                $smsInfo->save();
            }
            return ['code' => $reslut['code'], 'msg' => $reslut['msg'], 'data' => null];
        } else {
            return ['code' => 200, 'msg' => '模拟发送成功，短信验证码为：' . $code, 'data' => null];
        }
    }


    private static function sendHuaxinMsg($mobile, $content)
    {

        $config = [];
        //华信
        $config['huaxin_account'] = "mengdong";
        $config['huaxin_password'] = "mengdong123";
        $url = "https://dx.ipyy.net/smsJson.aspx?action=send&userid=&account=" . $config['huaxin_account'] . "&password=" . $config['huaxin_password'] . "&mobile=" . $mobile . "&content=" . urlencode($content) . "&sendTime=&extno=";
        $result = myCurl($url);
        $result_arr = json_decode($result, true);
        if (empty($result_arr['returnstatus'])) {
            return ['code' => 201, 'msg' => '发送失败', 'data' => $result];
        }
        if ($result_arr['returnstatus'] == 'Success') {
            return ['code' => 200, 'msg' => '发送成功', 'data' => null];
        } else {
            return ['code' => 201, 'msg' => '发送失败', 'data' => $result];
        }
    }


    public static function validateCode($user_name, $sms_code)
    {
        if (empty($user_name)) {
            return ['code' => 201, 'msg' => '手机号不能为空', 'data' => null];
        }
        if (empty($sms_code)) {
            return ['code' => 201, 'msg' => '短信验证码必须', 'data' => null];
        }
        $smsUniversalCode = SystemConfig::getValueByName('sms_universal_code');
        if ($sms_code == $smsUniversalCode) {
            return ['code' => 200, 'msg' => '验证成功', 'data' => null];
        }
        $map = [];
        $map[] = ['status', '=', 1];
        $map[] = ['mobile', '=', $user_name];
        $smsInfo = Sms::where($map)->order('id desc')->findOrEmpty();
        if ($smsInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '请先发送验证码', 'data' => null];
        }

        if ($smsInfo->error_num >= 5) {
            //验证码错误5次则失效
            $smsInfo->status = 3;
            $smsInfo->save();
            return ['code' => 201, 'msg' => '验证码已失效，请重新发送', 'data' => null];
        }

        if ($smsInfo->over_time < time()) {
            $smsInfo->status = 3;
            $smsInfo->inc('error_num', 1)->save();

            return ['code' => 201, 'msg' => '验证码已过期', 'data' => null];
        }

        if ($smsInfo->code != $sms_code) {
            $smsInfo->inc('error_num', 1)->update();

            return ['code' => 201, 'msg' => '验证码错误', 'data' => null];
        }

        $smsInfo->status = 2;
        $result = $smsInfo->save();
        if ($result) {
            return ['code' => 200, 'msg' => '验证成功', 'data' => null];
        } else {
            return ['code' => 201, 'msg' => '验证失败', 'data' => null];
        }
    }
}
