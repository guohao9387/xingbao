<?php

namespace app\model;

use app\model\UserRealCheck;

use support\Redis;
use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Db;

class User extends Model
{
    use SoftDelete;
    protected $pk = 'uid';
    protected $name = 'User';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public static $deleteUserDefaultImage = "/config/delete_user_icon.png";

    protected function setImageAttr($value)
    {
        return empty($value) ? '' : storageLocalAddress($value);
    }
    public function getImageAttr($value)
    {
        return storageNetworkAddress($value);
    }

    public function getAgeAttr($value)
    {
        $birthday = date('Y-m-d', $value);
        list($year, $month, $day) = explode("-", $birthday);
        $year_diff = date("Y") - $year;
        $month_diff = date("m") - $month;
        $day_diff = date("d") - $day;
        if ($day_diff < 0 || $month_diff < 0)
            $year_diff--;
        return $year_diff;
    }


    protected function setPasswordAttr($value)
    {
        return md5($value);
    }
    protected function setTradePasswordAttr($value)
    {
        return md5($value);
    }

    public static function onBeforeInsert($data)
    {
        $data->invite_code = User::getInviteCode();
        // $data->number = User::getUserNumber();
        $data->nick_name = "游客" . mt_rand(1000, 9999);
        $data->transfer_status = 1;
        $data->give_self_gift_status = 1;
    }

    //使用where条件情况下 需要手动调用触发！！！
    public static function onAfterWrite($data)
    {
        //修改后更新缓存
        $authInfo =  User::where('uid', $data['uid'])->findOrEmpty();
        if (!$authInfo->isEmpty()) {
            Redis::set("user:authInfo:" . $authInfo['login_token'], json_encode($authInfo->toArray()), "EX", 365 * 24 * 3600); //一年有效期
        }
    }

    protected static function getInviteCode()
    {
        $inviteCode = substr(str_shuffle('ABCDEFGHIJKLMNPQRSTUVWXYZ123456789'), 0, 6);
        $userInfo = User::where(['invite_code' => $inviteCode])->find();
        if (!empty($userInfo)) {
            User::getInviteCode();
        } else {
            return $inviteCode;
        }
    }

    protected static function getNickName()
    {
        $nick_name = "游客" .  strtolower(substr(str_shuffle('ABCDEFGHIJKLMNPQRSTUVWXYZ123456789'), 0, 4));
        //检测是否靓号
        $userInfo = User::where(['nick_name' => $nick_name])->find();
        if (!empty($userInfo)) {
            User::getNickName();
        } else {
            return $nick_name;
        }
    }


    public function getOnlineStatusTextAttr($value, $data)
    {
        if ($data['online_status'] == 0) {
            return '在线';
        } elseif ($data['online_status'] == 1) {
            return '离线';
        } elseif ($data['online_status'] == 2) {
            return '登出';
        }
    }
 
    public  function getRealStatusTextAttr($value, $data)
    {
        $status = UserRealCheck::getStatusData();
        return $status[$data['real_status']];
    }

    public static function getAccountStatusData()
    {
        $data = [1 => '正常',  2 => '注销'];
        return $data;
    }
    public static function getTixianStatusData()
    {
        $data = [1 => '未绑定',  2 => '已绑定'];
        return $data;
    }
    public function getTixianStatusTextAttr($value, $data)
    {
        $status = $this::getTixianStatusData();
        return $status[$data['tixian_status']];
    }




    //获取多用户数据
    public static function getUsersData($uidData, $addField = "")
    {

        $field = 'uid,nick_name,image,user_name';
        if (!empty($addField)) {
            $field .= "," . $addField;
        }
        $map = [];
        $map[] = ['uid', 'in', $uidData];
        $list = User::where($map)->field($field)->select()->toArray();
        $data = [];
        foreach ($list as $k => $v) {
            $v['head_attrie_image'] = "";
            $data[$v['uid']] = $v;
        }
        return $data;
    }



    public static function changeUserFund($uid, $fund_type, $change_type, $change_value, $note)
    {
        $data = [];
        $data['uid'] = $uid;
        $data['fund_type'] = $fund_type;
        $data['change_type'] = $change_type;
        $data['change_value'] = $change_value;
        $data['note'] = $note;
        $validate = new \app\validate\UserFundLog;
        if (!$validate->scene('create')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        if (empty($change_value)) {
            return ['code' => 200, 'msg' => '更新成功', 'data' => null];
        }
        $user_info = User::where('uid', $uid)->field('uid,money')->findOrEmpty();
        if ($user_info->isEmpty()) {
            return ['code' => 201, 'msg' => '用户信息不存在', 'data' => null];
        }
        $changeFile = "money";
        // switch ($fund_type) {
        //     case '1':
        //         $changeFile = "money";
        //         break;
        //     default:
        //         return ['code' => 201, 'msg' => '资金类型不存在', 'data' => null];
        //         break;
        // }
        $map = [];
        $map[] = ['uid', "=", $uid];
        $map[] = [$changeFile, ">=", -$change_value];
        $money = $user_info[$changeFile] + $change_value;
        $data['change_after_value'] = $money;
        $data['username'] = $user_info['user_name'];
        // 启动事务
        Db::startTrans();
        try {
            $result = User::where($map)->inc($changeFile, $change_value)->update(['update_time' => time()]);
            if (!$result) {
                Db::rollback();
                if ($money < 0) {
                    return ['code' => 201, 'msg' => '账户资金不足', 'data' => null];
                }
                return ['code' => 201, 'msg' => '更新失败1', 'data' => null];
            }

            $result = UserFundLog::create($data);
            if (!$result) {
                Db::rollback();
                return ['code' => 201, 'msg' => '更新失败2', 'data' => null];
            }
            // 提交事务
            Db::commit();
            // User::onAfterWrite(['uid' => $uid]); //更新缓存
            return ['code' => 200, 'msg' => '更新成功', 'data' => null];
        } catch (\Exception $e) {
            
            // 回滚事务
            Db::rollback();
            return ['code' => 201, 'msg' => '更新失败3', 'data' => $e];
        }
    }
}
