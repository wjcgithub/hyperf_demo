<?php
namespace App\Event;

/**
 * Created by PhpStorm.
 * User: evolution
 * Date: 19-10-11
 * Time: 下午4:42
 */
class ManagerCallback
{
    public function mangerStart(\Swoole\Http\Server $server)
    {
        \Xes\Rpc\Tool\Config::$rootPath = __DIR__;
        \Xes\Rpc\Tool\Config::$config['app'] = config('serverdiscovery');
        $discovery = new \Xes\Sdk\RPC\Server();
        $discovery->onManagerStart($server);
    }
}