<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    // 全局中间件
    '' => [
        app\middleware\AccessLog::class, //记录系统请求日志
    ],
    // api应用中间件(应用中间件仅在多应用模式下有效)
    'admin' => [
        app\middleware\admin\AuthCheck::class, //验证用户身份
        app\middleware\admin\AuthPower::class, //验证是否有操作权限

    ],
    'api' => [
        app\middleware\api\AccessControl::class, //允许跨域
        app\middleware\api\RequestValidate::class, //验证接口加解密
        app\middleware\api\AuthCheck::class, //用户身份验证

    ]
];
