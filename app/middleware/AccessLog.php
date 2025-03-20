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

namespace app\middleware;

use app\model\AccessLog as ModelAccessLog;
use support\Log;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use Webman\RedisQueue\Client;

/**
 * Class StaticFile
 * @package app\middleware
 */
class AccessLog implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $request->beginTime = microtime(true);
        $request->beginMem = memory_get_usage();
        $request->authIdentity = 1; //默认匿名用户
        $request->authInfo = []; //登录用户信息
        $request->authId = 0; //登录用户ID
        $response = $handler($request); // 继续向洋葱芯穿越，直至执行控制器得到响应

        // echo $response->getStatusCode();
        //        Log::info($response_json);
        $accessCreateData = [];
        $accessCreateData['type'] = $request->authIdentity;
        $accessCreateData['id'] = $request->authId;
        $accessCreateData['request_url'] = $request->path();
        $accessCreateData['param'] = json_encode($request->all());
        $accessCreateData['header'] = json_encode($request->header());
        // $accessCreateData['response'] = $response->rawBody();
        $accessCreateData['response_code'] = $response->getStatusCode();
        $accessCreateData['run_time'] = number_format(microtime(true) - $request->beginTime, 6, '.', '');
        $accessCreateData['use_mem'] = number_format((memory_get_usage() - $request->beginMem) / 1024, 2, '.', '');
        $accessCreateData['request_ip'] = $request->getRemoteIp();
        $accessCreateData['request_true_ip'] = $request->getRealIp(true);

        ModelAccessLog::create($accessCreateData);
        //优化使用消息队列插入数据
        // $middlewareData = [];
        // $middlewareData['type'] = 'createAccessLog';
        // $middlewareData['data'] = $accessCreateData;
        // Client::send("middleware", $middlewareData);

        // $update=[];
        // $update['type']='updateRoomHotValue';
        // $update['rid']=1;
        // $update['change_value']=10;
        // Client::send("room", $update,10);
        // $update=[];
        // $update['type']='updateRoomHotValue';
        // $update['rid']=1;
        // $update['change_value']=-10;
        // Client::send("room", $update,10);
        return $response;
    }
}
