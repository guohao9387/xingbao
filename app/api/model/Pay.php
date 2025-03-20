<?php

namespace app\api\model;

use app\model\Recharge;
use app\model\RechargeConfig;
use app\model\User;
use llianpay\accp\client\LLianPayClient;
use llianpay\accp\security\LLianPayAccpSignature;
use support\Log;
use think\facade\Db;
use think\Model;
use Yansongda\Pay\Pay as PayPay;


class Pay extends Model
{
    //支付服务订单
    public static function payOrder($authInfo, $data, $request)
    {
        if (empty($data['order_sn'])) {
            return ['code' => 201, 'msg' => '订单号参数错误', 'data' => null];
        }
        if (empty($data['pay_type'])) {
            return ['code' => 201, 'msg' => '支付方式参数错误', 'data' => null];
        }

        $rechargeStatus = get_system_config('recharge_status');
        if ($rechargeStatus == 2) {
            return ['code' => 201, 'msg' => '维护中，请稍后重试', 'data' => null];
        }
        $nowTime = time();
        $payData = [];
        $payData['pay_status'] = 1; //1待支付2已支付
        $payData['pay_type'] = $data['pay_type']; //支付方式
        $payData['data'] = null; //对应支付方式参数
        $map = [];
        $map[] = ['uid', '=', $authInfo['uid']];
        $map[] = ['order_sn', '=', $data['order_sn']];
        $rechargeOrderInfo = Recharge::where($map)->findOrEmpty();
        if ($rechargeOrderInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '订单信息不存在', 'data' => null];
        }
        if ($rechargeOrderInfo->pay_status != 1) {
            return ['code' => 201, 'msg' => '订单已支付', 'data' => null];
        }
        //超过一小时的订单关闭支付
        if ($nowTime - strtotime($rechargeOrderInfo['create_time']) > 3600) {
            return ['code' => 201, 'msg' => '订单已超时，关闭支付', 'data' => null];
        }
        $lianlianType = Recharge::getLianlianTYpeAttr();
        if ($data['pay_type'] == 3) {
            /*dump($data);
            dump($data['lianlian_type']);
            dump($lianlianType);*/
            if (empty($data['lianlian_type']) || !in_array($data['lianlian_type'], $lianlianType)) {
                return ['code' => 201, 'msg' => '支付类型异常', 'data' => null];
            }
            $rechargeOrderInfo->lianlian_type = $data['lianlian_type'];
        }
        //更新支付方式
        $rechargeOrderInfo->pay_type = $data['pay_type'];
        $rechargeOrderInfo->save();
        $pay_amount = $rechargeOrderInfo->money;
        switch ($data['pay_type']) {
            case '1':
                return ['code' => 201, 'msg' => '支付宝支付维护中', 'data' => null];
                // 3.app支付
                $order = [
                    'out_trade_no' => $rechargeOrderInfo->order_sn,
                    'total_amount' => $pay_amount,
                    'subject' => '订单支付',
                    '_method' => 'get' // 使用get方式跳转
                ];
                $payData['data'] = PayPay::alipay(Config("plugin.payment.app"))->app($order)->getBody()->getContents();
                break;
            case '2':
                return ['code' => 201, 'msg' => '请关注公众号：xiha嘻哈派对', 'data' => null];
                $order = [
                    'out_trade_no' => $rechargeOrderInfo->order_sn,
                    'description' => '订单支付',
                    'amount' => [
                        'total' => $pay_amount * 100,
                    ],
                ];
                $payData['data'] = PayPay::wechat(Config("plugin.payment.app"))->app($order);
                break;
            case '3':
                //关闭支付宝通道
                if ($data['lianlian_type'] == 'ALIPAY_NATIVE') {
                    return ['code' => 201, 'msg' => '支付宝支付维护中', 'data' => null];
                }
                //连连支付
                $basePath = BASE_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'llianpay' . DIRECTORY_SEPARATOR . 'yd' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
                require_once($basePath . 'cfg.php');
                require_once($basePath . 'client/LLianPayClient.php');
                // 构建请求参数1
                $params = [];
                $current = date("YmdHis");
                $params['mch_id'] = '402403050000017609';
                $params['user_id'] = '1002';
                $params['busi_type'] = '100001';
                $params['txn_seqno'] = $rechargeOrderInfo->order_sn;
                $params['txn_time'] = $current;
                $params['order_amount'] = $pay_amount;
                $params['notify_url'] = 'http://mengdong.xyyyapp.com:27001/api/Pay/lianlianPayNotify';
                $params['risk_item'] = json_encode(['ip_addr' => $request->getRealIp()]);
                $params['pay_method_infos'][] = [
                    'pay_type' => $data['lianlian_type'],
                    'amount' => $pay_amount
                ];
                $params['payee_infos'][] = [
                    'payee_uid' => '402403050000017609',
                    'payee_accttype' => 'MCHOWN',
                    'payee_type' => 'MCH',
                    'payee_amount' => $pay_amount
                ];
                $params['goods_info'][] = ['goods_id' => '18', 'goods_name' => '萌动'];
                $params['device_info'] = ['device_id' => 'SMQ'];
                $params['store_info'] = ['store_id' => '88888801'];
                $url = 'https://openapi.lianlianpay.com/mch/v1/ipay/createpay';
                $result = LLianPayClient::sendRequest($url, json_encode($params));
                if (empty($result)) {
                    return ['code' => 201, 'msg' => '支付失败,请重试', 'data' => null];
                }
                $result_array = json_decode($result);

                if ($result_array->ret_code !== '0000') {
                    return ['code' => 201, 'msg' => '支付创建失败', 'data' => $result_array];
                }
                $payData['data'] = [
                    'order_sn' => $result_array->txn_seqno,
                    'pay_amount' => $result_array->order_amount,
                    'payload' => empty($result_array->payload) ? '' : $result_array->payload,
                    'lianlian_sn' => $result_array->platform_txno
                ];
                break;
            case '4':
                require_once(base_path() . "/extend/adapay_sdk_php_v1.4.4_zfb/adaPay.php");
                $AdaPayment = new \AdaPayment();
                $notify_url = "http://mengdong.xyyyapp.com:27001/api/Pay/adaPayNofity";
                $rechargeOrderInfo->order_sn = $rechargeOrderInfo->order_sn;
                $result = $AdaPayment::create($rechargeOrderInfo->order_sn,  $pay_amount, $notify_url);
                if ($result['code'] != 200) {
                    return ['code' => 201, 'msg' => '支付创建失败', 'data' => $result_array];
                }
                return (['code' => 200, 'msg' => '获取成功', 'data' => $result['data']['expend']]);
            default:
                return ['code' => 201, 'msg' => '未知支付方式', 'data' => null];
                break;
        }


        return ['code' => 200, 'msg' => '操作成功', 'data' => $payData];
    }

    //服务订单完成支付
    public static function rechargeOrderPaySuccess($rechargeOrderInfo)
    {
        //判断是否是首充
        $map = [];
        $map[] = ['cid', '=', $rechargeOrderInfo['cid']];
        $map[] = ['first_status', '=', 2];
        $rechargeConfigInfo = RechargeConfig::where($map)->findOrEmpty();

        $map = [];
        $map[] = ['uid', '=', $rechargeOrderInfo['uid']];
        $map[] = ['cid', '=', $rechargeOrderInfo['cid']];
        $map[] = ['first_grant_status', '>', 1];
        $firstRechargeInfo = Recharge::where($map)->findOrEmpty();

        //完成支付订单
        $map = [];
        $map[] = ['rid', '=', $rechargeOrderInfo['rid']];
        $data = [];
        $data['pay_status'] = 2; //支付状态
        $data['notify_time'] = time();
        if (!$rechargeConfigInfo->isEmpty() && $firstRechargeInfo->isEmpty()) {
            $data['first_grant_status'] = 2; //有对用赠送首充配置，且没有发放过
        }
        $result = Recharge::where($map)->update($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '支付失败', 'data' => null];
        }

        $result = User::changeUserFund($rechargeOrderInfo['uid'], 2, 14, $rechargeOrderInfo['integral'], "用户充值");
        if ($result['code'] != 200) {
            return ['code' => 201, 'msg' => $result['msg'], 'data' => null];
        }
        return ['code' => 200, 'msg' => '支付成功', 'data' => null];
    }

    //支付宝异步验证
    public static function aliPayNotify($data)
    {
        try {
            if (!empty($data['fund_bill_list'])) {
                $data['fund_bill_list'] = str_replace("&quot;", '"', $data['fund_bill_list']); //转义
                // $data['fund_bill_list'] = str_replace("&amp;quot;", '"', $data['fund_bill_list']); //转义
            }

            $decryptData = PayPay::alipay(Config("plugin.payment.app"))->callback($data); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
            if (empty($decryptData['trade_status'])) {
                throw new \Exception('支付失败1');
            }
            if (empty($decryptData['out_trade_no'])) {
                throw new \Exception('支付失败2');
            }
            if ($decryptData['trade_status'] != "TRADE_SUCCESS") {
                throw new \Exception('支付失败3');
            }

            $resultData = [];
            $map = [];
            $map[] = ['order_sn', '=', $decryptData['out_trade_no']];
            $rechargeOrderInfo = Recharge::where($map)->findOrEmpty();
            if ($rechargeOrderInfo->isEmpty()) {
                throw new \Exception('订单信息不存在');
            }
            if ($rechargeOrderInfo->pay_status != 1) {
                throw new \Exception('订单已支付');
            }
            //判断订单金额

            if ($decryptData['total_amount'] != $rechargeOrderInfo->money) {
                throw new \Exception('支付金额异常');
            }
            //更新支付方式
            $rechargeOrderInfo->pay_type = 1;
            $rechargeOrderInfo->save();
            $resultData = Pay::rechargeOrderPaySuccess($rechargeOrderInfo);
            return $resultData;
        } catch (\Exception $e) {
            // $e->getMessage();
            return ['code' => 201, 'msg' => $e->getMessage(), 'data' => null];
        }
    }

    public static function wxPayNotify($data)
    {
        $config = Config("plugin.payment.app");
        try {
            $decryptData = PayPay::wechat($config)->callback($data); // 是的，验签就这么简单！
            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况

            if (empty($decryptData['resource']['ciphertext']['trade_state'])) {
                throw new \Exception('支付失败1');
            }
            if (empty($decryptData['resource']['ciphertext']['out_trade_no'])) {
                throw new \Exception('支付失败2');
            }
            if ($decryptData['resource']['ciphertext']['trade_state'] != "SUCCESS") {
                throw new \Exception('支付失败3');
            }

            $resultData = [];
            $map = [];
            $map[] = ['order_sn', '=', $decryptData['resource']['ciphertext']['out_trade_no']];
            $rechargeOrderInfo = Recharge::where($map)->findOrEmpty();
            if ($rechargeOrderInfo->isEmpty()) {
                throw new \Exception('订单信息不存在');
            }
            if ($rechargeOrderInfo->pay_status != 1) {
                throw new \Exception('订单已支付');
            }
            //判断订单金额
            if ($decryptData['resource']['ciphertext']['amount']['total'] * 0.01 != $rechargeOrderInfo->money) {
                throw new \Exception('支付金额异常');
            }
            //更新支付方式
            $rechargeOrderInfo->pay_type = 2;
            $rechargeOrderInfo->save();
            $resultData = Pay::rechargeOrderPaySuccess($rechargeOrderInfo);
            return $resultData;
        } catch (\Exception $e) {
            // $e->getMessage();
            return ['code' => 201, 'msg' => $e->getMessage(), 'data' => null];
        }
    }

    public static function lianlianPayNotify($data)
    {
        //         $data='{"data":{"mch_id":"402403050000017609","pay_method_infos":[{"amount":"0.01","pay_type":"ALIPAY_NATIVE"}],"txn_seqno":"CZ202407171103568647","platform_txno":"2024071796584924121","order_amount":"0.01","txn_status":"SUCCESS","account_date":"20240717","chnl_txno":"2024071722001405901454240624","chnl_user_id":"2088412790205905","chnl_req_serialId":"2024071715453340731","store_id":"88888801","pay_info":{"pay_mode":"ALI_PAY","payer_type":"ANONYMOUS_USER","payer_uid":"1002"},"payee_infos":[{"payee_uid":"402403050000017609","payee_acctype":"MCHOWN","payee_type":"INNER_MERCHANT","payee_amount":"0.01"}],"success_time":"20240717110450"},"header":{"host":"127.0.0.1:443","x-real-ip":"47.97.175.245","x-forwarded-for":"47.97.175.245","remote-host":"47.97.175.245","x-host":"mengdong.xyyyapp.com:443","x-scheme":"https","connection":"upgrade","content-length":"625","content-type":"text\/json;charset=UTF-8","user-agent":"httpcomponents","correlationid":"5915f10b-aa91-4046-98ec-6fe8948e8a95","signature-data":"aBQRbnd9ehtl0jZHM6eSMWNdLZsXTpNEEEcQ6IFlpZ3AkPaNMYSjCGYYePiwxLrt7l7d27KBC90rXcKNYqbBoNd51r0ccAoSrxbQ37mJi+hGn28wwQd+n5KZS6ZncniWtleba2jjJqZpJ4rChXlXBbUHcC80em+o0iTcRjb1XkY=","signature-type":"RSA","sw8":"1-Mzg2NDAwZDhjMzY1NDdiMzhjM2U0MjEwYmJiNTY4MWIuNzcyNjMuMTcyMTE4NTQ4OTc4NjMzMzM=-MDZlZTZlMjA3MWNmNDk2MGJhYjQwYTM4YTgzMzVjNzguNjUxLjE3MjExODU0ODk5Mjk0NTE2-2-cGF5X25vdGlmeV9jb3Jl-MmZiNmI2YmE2NTIzNDNkNTkzYmI5OTM2ZGMwN2Q4ZmVAMTAuMjMxLjUuMTkw-Y29tLmxpYW5wYXkubm90aWZ5LmR1YmJvLnNlcnZpY2UuSU5vdGlmaWNhdGlvblNlcnZpY2UuY2FsbGJhY2soTm90aWZ5SW5mbyk=-bWVuZ2RvbmcueHl5eWFwcC5jb206NDQz","sw8-correlation":"","sw8-x":"0","accept-encoding":"gzip,deflate"}}';
        $basePath = BASE_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'llianpay' . DIRECTORY_SEPARATOR . 'yd' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        require_once($basePath . 'cfg.php');
        require_once($basePath . 'security/LLianPayAccpSignature.php');
        $data = json_decode(json_encode($data), true);
        $header = $data['header'];
        $resultData = $data['data'];
        try {
            if (empty($header['signature-data'])) {
                throw new \Exception('支付失败');
            }
            if (LLianPayAccpSignature::checkSign(json_encode($resultData), $header['signature-data']) != 1) {
                throw new \Exception('验签失败');
            }
            if (empty($resultData['txn_status']) || empty($resultData['order_amount'])) {
                throw new \Exception('支付失败0');
            }
            if (floatval($resultData['order_amount']) != $resultData['order_amount'] || $resultData['order_amount'] < 0.01) {
                throw new \Exception('支付失败1');
            }
            if ($resultData['txn_status'] != 'SUCCESS') {
                throw new \Exception('支付失败2');
            }
            $resultDataList = [];
            $map = [];
            $map[] = ['order_sn', '=', $resultData['txn_seqno']];
            $rechargeOrderInfo = Recharge::where($map)->findOrEmpty();
            if ($rechargeOrderInfo->isEmpty()) {
                throw new \Exception('订单信息不存在');
            }
            if ($rechargeOrderInfo->pay_status != 1) {
                throw new \Exception('订单已支付');
            }
            //判断订单金额
            if ($resultData['order_amount'] != $rechargeOrderInfo->money) {
                throw new \Exception('支付金额异常');
            }
            //更新支付方式    --
            $rechargeOrderInfo->pay_type = 3;
            $rechargeOrderInfo->save();
            $resultDataList = Pay::rechargeOrderPaySuccess($rechargeOrderInfo);
            return $resultDataList;
        } catch (\Exception $e) {
            // $e->getMessage();
            return ['code' => 201, 'msg' => $e->getMessage(), 'data' => null];
        }
    }


    public static function adaPayNofity($data)
    {


        // if (empty($data)) {
        //     return (['code' => 201, 'msg' => '参数错误', 'data' => null]);
        // }
        // if (empty($data['data'])) {
        //     return (['code' => 201, 'msg' => 'data参数不存在', 'data' => null]);
        // }
        // $postDataDataArray = json_decode($data['data'], true);
        // // dump($data);
        // // dump($data['data']);
        // // dump($postDataDataArray);

        // require_once(base_path() . "/extend/adapay_sdk_php_v1.4.4_zfb/adaPay.php");
        // $AdaPayment = new \AdaPayment();
        // $result = $AdaPayment::notifyVerify($data['data'], $data['sign']);
        // if (!$result) {
        //     return ['code' => 201, 'msg' => '签名验证失败', 'data' => null];   //强制签名通过  本地通过、服务器无法通过，原因待查
        // }
        // if ($data['type'] != "payment.succeeded") {
        //     return ['code' => 201, 'msg' => '支付状态异常', 'data' => null];
        // }
        // //判断异步回调是否支付成功
        // if ($postDataDataArray['status'] != "succeeded") {
        //     return ['code' => 201, 'msg' => '支付失败', 'data' => null];
        // }

        if (empty($data)) {
            return (['code' => 201, 'msg' => '参数错误', 'data' => null]);
        }

        require_once(base_path() . "/extend/adapay_sdk_php_v1.4.4_zfb/adaPay.php");
        $AdaPayment = new \AdaPayment();

        // $postData = json_decode($data, 1);

        $postDataData = str_replace("&quot;", '"', $data['data']);
        $postDataDataArray = json_decode($postDataData, true);

        // $post_data_str = json_encode(str_replace("&quot;", '"', $postData['data']), JSON_UNESCAPED_UNICODE);
        // dump($post_data_str);
        $postSignStr = isset($data['sign']) ? $data['sign'] : '';
        // dump($data);
        //dump($postDataData);
        //dump($postSignStr);
        $result = $AdaPayment::notifyVerify($postDataData, $postSignStr);
        // dump($result);
        //dump($postDataDataArray);

        if (!$result) {
            return ['code' => 201, 'msg' => '签名验证失败', 'data' => null];
        }
        if ($data['type'] != "payment.succeeded") {
            return ['code' => 201, 'msg' => '支付状态异常', 'data' => null];
        }
        //判断异步回调是否支付成功
        if ($postDataDataArray['status'] != "succeeded") {
            return ['code' => 201, 'msg' => '支付失败', 'data' => null];
        }




        //验证通过
        $map = [];
        $map[] = ['order_sn', '=', $postDataDataArray['order_no']];
        $rechargeOrderInfo = Recharge::where($map)->findOrEmpty();
        if ($rechargeOrderInfo->isEmpty()) {
            return ['code' => 201, 'msg' => '订单信息不存在', 'data' => null];
        }
        if ($rechargeOrderInfo->pay_status != 1) {
            return ['code' => 201, 'msg' => '订单已支付', 'data' => null];
        }
        //判断订单金额
        if ($postDataDataArray['pay_amt'] != $rechargeOrderInfo->money) {
            return ['code' => 201, 'msg' => '支付金额异常', 'data' => null];
        }


        //  return ['code' => 201, 'msg' => '支付金额异常111', 'data' => null];
        //更新支付方式
        $rechargeOrderInfo->pay_type = 4;
        $rechargeOrderInfo->save();
        $resultData = Pay::rechargeOrderPaySuccess($rechargeOrderInfo);
        return $resultData;
    }
}
