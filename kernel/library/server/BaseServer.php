<?php

namespace zero\server;

use Swoole\Timer;
use zero\Container;

class BaseServer
{
    /**
     * @var object $server swoole 服务实例
     */
    protected $server;

    /**
     * @var array $config 配置信息
     */
    protected $config;

    /**
     * BaseServer constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->config = Container::get('config')->get('app');
    }

    public function workerStart()
    {
        // 加载路由注解
        Container::get('route')->loadRouterAnnotations();
    }

    public function start()
    {
        $reloadCls = Container::get('reload');
        Timer::tick($this->config['hot_reload_time'], function () use ($reloadCls) {
            if ($reloadCls->hotReload()) {
                $this->server->reload();
            }
        });
    }
}
