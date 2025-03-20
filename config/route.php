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

use support\Request;
use Webman\Route;


$adminAddress =  config("customConfig.adminAddress");


// 管理后台登录地址
Route::any($adminAddress, function () {
    return redirect('/admin/admin_basic_framework.html');
});
// 正确的用法
Route::any($adminAddress . '/', function () {
    return redirect('/admin/admin_basic_framework.html');
});

Route::fallback(function (Request $request) {
    // ajax请求时返回json
    if ($request->expectsJson()) {
        return json(['code' => 404, 'msg' => '404 not found']);
    }
    // 页面请求返回404.html模版
    return view('404', ['error' => ''])->withStatus(404);
});
