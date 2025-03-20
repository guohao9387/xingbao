<?php

return [
    //请求、返回数据加解密配置
    'requestrAndResponseConfig' => [
        //请求验证
        'request' => [
            'enable' => false, //开启验证状态  开启 true 关闭 false
            'encryptStatus' => false, //验证状态  开启 true 关闭 false  开启后仅接受 encrypted_data 进行参数传递
            'replay' => false, //防止重放 开启 true 关闭 false   
            'timeout' => 0, //请求过期时间  0不限制  默认设置 10
        ],
        //API接口返回加密
        'response' => [
            'enable' => true, //开启加密状态  开启 true 关闭 false
            'showRawData' => true, //开启加密状态  开启 true 关闭 false
        ],
        'secretKey' => 'e0dbcf8ab08870f76270eb12bacbb552',  //必须32位字符
        'iv' => 'PYkkBrCHn7Wfy2d5', //必须16位字符
    ],
    //管理后台访问地址
    'adminAddress' => '/zxcvf', //随机字符串

];
