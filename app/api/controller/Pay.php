<?php

namespace app\api\controller;

use app\api\model\Pay as ModelPay;
use support\Request;
use support\Log;

class Pay
{

    //支付服务订单
    public function payOrder(Request $request)
    {
        $data = $request->alone(['order_sn', 'pay_type', 'lianlian_type']);
        $keyName = 'api:Pay:payOrder:' . $request->authInfo['uid'];
        $lockResult = redisLock($keyName);
        if ($lockResult['lock_status']) {
            return $lockResult['return_data'];
        }
        $result = ModelPay::payOrder($request->authInfo, $data, $request);
        redisUnlock($keyName);
        return encryptedJson($result);
    }
    //支付回调
    public function aliPayNotify(Request $request)
    {
        $data = $request->post();
        // Log::info(json_encode($data));
        // $data = '{"gmt_create":"2023-10-10 00:39:38","charset":"utf-8","seller_email":"y15150441221@163.com","subject":"\u8ba2\u5355\u652f\u4ed8","sign":"C++s+eS+TbEtxDVIuAR5tNpnuFnr\/7gMJGK+9kAQJPy7wet2fAEuY5oSNfTQbtRiU5eDFell+xoJYH2\/egptWdmBxmBttvJ2tfz7iSvMaZ5kiJThxO5k1DtRDIxeyrlph4sqdGeV1ik9VLk8k\/Y7TPKhTDSXV6uE3I4UsDopsFlRC5Zd10f1HKyYSpC6dSjUU5QRVMWUXzNvcKcCHwIUYF+oXPY6JevUBiop7tD6UgMNqUDVNW\/xZ2x4Kb+fQEqm8\/DGIV5Nv73BppqzRSazorDh+svxGuC3rRAC+cFXATGlKEzWS8WWZ\/MTU9k5KcuDidGNyNQmLykS\/QlamnwUgg==","buyer_id":"2088232322140106","invoice_amount":"50.00","notify_id":"2023101001222003940040101440965427","fund_bill_list":"[{&quot;amount&quot;:&quot;50.00&quot;,&quot;fundChannel&quot;:&quot;ALIPAYACCOUNT&quot;}]","notify_type":"trade_status_sync","trade_status":"TRADE_SUCCESS","receipt_amount":"50.00","app_id":"2021004118638158","buyer_pay_amount":"50.00","sign_type":"RSA2","seller_id":"2088641964632973","gmt_payment":"2023-10-10 00:39:39","notify_time":"2023-10-10 00:53:40","version":"1.0","out_trade_no":"CZ202310100039351601","total_amount":"50.00","trade_no":"2023101022001440101403486800","auth_app_id":"2021004118638158","buyer_logon_id":"131****2989","point_amount":"0.00"}';

        $result = ModelPay::aliPayNotify($data);
        // if ($result['code'] == 200) {
        //     return response('success');
        // } else {
        //     return response('fail');
        // }
        return encryptedJson($result);
    }

    //支付回调
    public function wxPayNotify(Request $request)
    {
        $data = $request->post();
        // Log::info(json_encode($data));
        // $data = json_decode('{"id":"134eadb9-4771-5f4b-b23a-cdf9659f31a5","create_time":"2024-01-18T15:49:38+08:00","resource_type":"encrypt-resource","event_type":"TRANSACTION.SUCCESS","summary":"\u652f\u4ed8\u6210\u529f","resource":{"original_type":"transaction","algorithm":"AEAD_AES_256_GCM","ciphertext":"2P4d25EaREXUXYleO9WDC\/yRWK0x\/lremV6YIOmVuHErX9MqBNLP8TkBw3kBDgscA+e0zT0GN3dV7wXdbeOb+lOYW7s\/bb+S+KgbY5UdxnV0i7U0VGVDmbRO3ypR\/ADx9+0aqXoj9Yyaktmy+b08v1WeCO+0pisf6WNq2eYYYSXOW6pMdpLHT\/wJh60W4ZcNo2Y7otsyFPzvq5sTo6U6j7FQLAWxU3eZqP7+hnmZkz6GTIZGim+DTNdXfaCdmfa86Ss9BsI2VnkpML00aoIAVYaJdJrWiWvjPzR5g+7J2Xn4iNBwBCduVDVU3OIjb3v+yFj28IPKlvzpYw5AgDEit\/dQr00oLSyfbdyXUYBFtCxINnlowh266REEocYOtZ4Q7Na2av9hNUl2\/Q\/A+Ovy\/Foq6zAFOKUPJMzbX7hjqgWpx2znxqUfC3YOzy6KzUSzNqpiTx\/R4vjmDzn51hoCRJaIj\/RbYViDdNT\/Yz6VfFmJYqyUwRtbEBBK7qwsRF0q80ApAI0mGklaw\/sdVkSLUaFIl8WlChkxywlOgN8wxPEVFghhbMT4WYj33vOS0Hdk5A==","associated_data":"transaction","nonce":"OtxhBdn3qLNA"}}', true);
        $result = ModelPay::wxPayNotify($data);
        return encryptedJson($result);
    }
    //支付回调
    public function lianlianPayNotify(Request $request)
    {
        $data = [];
        $data['data'] = $request->post();
        $data['header'] = $request->header();
        $result = ModelPay::lianlianPayNotify($data);
        return encryptedJson($result);
    }

    public function adaPayNofity(Request $request)
    {
        $data = $request->post();
        // Log::info("======" . json_encode($data));


        $result = ModelPay::adaPayNofity($data);
        return encryptedJson($result);
    }
}
