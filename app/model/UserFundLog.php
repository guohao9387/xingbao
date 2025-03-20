<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;



class UserFundLog extends Model
{
    use SoftDelete;
    protected $pk = 'lid';
    protected $name = 'user_fund_log';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public static function getChangeTypeData()
    {
        $data = [1 => '系统调节', 2 => '用户提现扣除', 3 => '卖出商品后台打款', 4 => '后台拒绝提现退回'];
        return $data;
    }
    public  function getChangeTypeTextAttr($value, $data)
    {
        $status = $this::getChangeTypeData();
        return empty($status[$data['change_type']]) ? '' : $status[$data['change_type']];
    }

    public static function getFundTypeData()
    {
        $data = [1 => '账户余额'];
        return $data;
    }
    public  function getFundTypeTextAttr($value, $data)
    {
        $status = $this::getFundTypeData();
        return empty($status[$data['fund_type']]) ? '' : $status[$data['fund_type']];
    }
}
