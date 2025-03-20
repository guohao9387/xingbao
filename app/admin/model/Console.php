<?php

namespace app\admin\model;

use app\model\User as UserModel;
use app\model\Game as ModelGame;
use app\model\GameAccount as ModelGameAccount;
use app\model\AccountOrder as ModelAccountOrder;

use app\model\Withdraw as Withdraw;

use think\Model;
use think\facade\Db;

class Console extends Model
{

    public static function getTotalData()
    {
        $data = [];
        $data['user_total_count'] = UserModel::count();
        $data['game_total_count'] = ModelGame::count();
        // $data['user_online_count'] = UserModel::where('online_status', 0)->count();
        $data['account_total_count'] = ModelGameAccount::count();
        $data['order_total_count'] = ModelAccountOrder::count();
        // dump($data);
        //获取成交相关数据
        $timeData =getBetweenTime(1); 
     
        $data['order_statistics_data1'] = ModelAccountOrder::field('count(1) as count, IFNULL(sum(account_amount), 0) as account_amount, IFNULL(sum(amount), 0) as amount')->where('create_time', "between", [$timeData['startTime'], $timeData['endTime']])->where('pay_status',2)->find();
        $timeData =getBetweenTime(2); 
        $data['order_statistics_data2'] = ModelAccountOrder::field('count(1) as count, IFNULL(sum(account_amount), 0) as account_amount, IFNULL(sum(amount), 0) as amount')->where('pay_status',2)->where('create_time', "between", [$timeData['startTime'], $timeData['endTime']])->find();
        $timeData =getBetweenTime(3); 
        $data['order_statistics_data3'] = ModelAccountOrder::field('count(1) as count, IFNULL(sum(account_amount), 0) as account_amount, IFNULL(sum(amount), 0) as amount')->where('pay_status',2)->where('create_time', "between", [$timeData['startTime'], $timeData['endTime']])->find();
        $timeData =getBetweenTime(4); 
        $data['order_statistics_data4'] = ModelAccountOrder::field('count(1) as count, IFNULL(sum(account_amount), 0) as account_amount, IFNULL(sum(amount), 0) as amount')->where('pay_status',2)->where('create_time', "between", [$timeData['startTime'], $timeData['endTime']])->find();
        $timeData =getBetweenTime(6); 
        $data['order_statistics_data5'] = ModelAccountOrder::field('count(1) as count, IFNULL(sum(account_amount), 0) as account_amount, IFNULL(sum(amount), 0) as amount')->where('pay_status',2)->where('create_time', "between", [$timeData['startTime'], $timeData['endTime']])->find();
        $data['order_statistics_data6'] = ModelAccountOrder::field('count(1) as count, IFNULL(sum(account_amount), 0) as account_amount, IFNULL(sum(amount), 0) as amount')->where('pay_status',2)->find();

         //获取提现相关数据
         $timeData =getBetweenTime(1); 
         $data['withdraw_statistics_data1'] = Withdraw::field('count(1) as count, IFNULL(sum(money), 0) as money, IFNULL(sum(server_money), 0) as server_money')->where('deal_status',2)->where('deal_time', "between", [$timeData['startTime'], $timeData['endTime']])->sum('money');
         $timeData =getBetweenTime(2); 
         $data['withdraw_statistics_data2'] = Withdraw::field('count(1) as count, IFNULL(sum(money), 0) as money, IFNULL(sum(server_money), 0) as server_money')->where('deal_status',2)->where('deal_time', "between", [$timeData['startTime'], $timeData['endTime']])->sum('money');
         $timeData =getBetweenTime(3); 
         $data['withdraw_statistics_data3'] = Withdraw::field('count(1) as count, IFNULL(sum(money), 0) as money, IFNULL(sum(server_money), 0) as server_money')->where('deal_status',2)->where('deal_time', "between", [$timeData['startTime'], $timeData['endTime']])->sum('money');
         $timeData =getBetweenTime(4); 
         $data['withdraw_statistics_data4'] = Withdraw::field('count(1) as count, IFNULL(sum(money), 0) as money, IFNULL(sum(server_money), 0) as server_money')->where('deal_status',2)->where('deal_time', "between", [$timeData['startTime'], $timeData['endTime']])->sum('money');
         $timeData =getBetweenTime(6); 
         $data['withdraw_statistics_data5'] = Withdraw::field('count(1) as count, IFNULL(sum(money), 0) as money, IFNULL(sum(server_money), 0) as server_money')->where('deal_status',2)->where('deal_time', "between", [$timeData['startTime'], $timeData['endTime']])->sum('money');
         $data['withdraw_statistics_data6'] = Withdraw::field('count(1) as count, IFNULL(sum(money), 0) as money, IFNULL(sum(server_money), 0) as server_money')->where('deal_status',2)->sum('money');

     

        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
    public static function getChatData()
    {

        //获取每日新增数据
        $sql = "SELECT FROM_UNIXTIME(create_time, '%Y-%m-%d') AS create_date,count(1) as  count
        FROM yy_user where 1=1 GROUP BY create_date ORDER BY create_date desc LIMIT 0,72";
        $list1 = Db::query($sql);


        $data1 = [];
        foreach ($list1 as $k => $v) {
            $data1[$v['create_date']] = $v['count'];
        }
        $today_start_time = strtotime(date('Y-m-d'));
        $x_data = [];
        $y_data1 = [];
        for ($i = 0; $i < 30; $i++) {
            $day =  date('Y-m-d', $today_start_time - $i * 24 * 3600);
            $x_data[$i] = $day;
            $y_data1[$i] =  0;
            if (!empty($data1[$day])) {
                $y_data1[$i] = $data1[$day];
            }
        }
        $data = [];
        $data['x_data'] = array_reverse($x_data);
        $data['y_data1'] = array_reverse($y_data1);
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
}
