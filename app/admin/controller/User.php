<?php

namespace app\admin\controller;

use app\admin\model\User as ModelUser;
use support\Request;

class User
{
    public function getUserList(Request $request)
    {
        $data = $request->only(['uid',  'user_name', 'nick_name', 'real_status', 'money', 'create_time','order', 'sort', 'page', 'limit']);
        $result = ModelUser::getUserList($data);
        return json($result);
    }
   
    public function getUserInfo(Request $request)
    {
        $data = $request->only(['uid']);
        $result = ModelUser::getUserInfo($data);
        return json($result);
    }
    public function modifyUser(Request $request)
    {
        $data = $request->only(['uid','user_name', 'nick_name',  'login_status','real_status', 'real_name', 'card_id' ,'alipay_real_name','alipay_account']);
        $result = ModelUser::modifyUser($data);
        return json($result);
    }

    public function modifyUserMoney(Request $request)
    {
        $data = $request->only(['uid', 'change_value', 'note']);

        $result = ModelUser::modifyUserMoney($data);

        return json($result);
    }

}
