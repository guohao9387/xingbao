<?php

namespace app\api\model;



use app\model\SystemConfig as ModelSystemConfig;
use think\Model;
use think\facade\Db;

class SystemConfig extends Model
{

    //提现
    public static function getSystemConfigData($authInfo)
    {

        $configData = ModelSystemConfig::getSystemConfigData(true);
        $returnData = [];
        $returnData['user_withdraw_server_rate'] = $configData['user_withdraw_server_rate'];
        $returnData['min_withdraw_amount'] = $configData['min_withdraw_amount'];
        $returnData['exchange_money_rate']  = $configData['exchange_money_rate'];
        $returnData['world_message_coin']  = $configData['world_message_coin'];
        $returnData['customer_service_note']  = $configData['customer_service_note'];
        $returnData['message_forbid_word']  = $configData['message_forbid_word'];
        $returnData['conent_forbid_word']  = $configData['conent_forbid_word'];
        $returnData['xyp_show_status']  = $configData['xyp_show_status'];
        $returnData['zpzb_show_status']  = $configData['zpzb_show_status'];
        $returnData['mfzp_show_status']  = $configData['mfzp_show_status'];
        $returnData['cjj_show_status']  = $configData['cjj_show_status'];
        $returnData['tp_show_status']  = $configData['tp_show_status'];
        $returnData['pack_box_show_status']  = $configData['pack_box_show_status'];

        //$returnData['union_customer_service_note']  = $configData['union_customer_service_note'];

        return ['code' => 200, 'msg' => '获取成功', 'data' => $returnData];
    }
}
