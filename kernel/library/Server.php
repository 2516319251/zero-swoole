<?php

namespace zero;

use zero\server\http\HttpServer;
use zero\server\tcp\TcpServer;

/**
 * Class HttpServer
 * @package zero
 */
class Server
{
    /**
     * 处理启动服务
     * @param $input
     */
    public function handle($input)
    {
        $module = strtolower($input[1] ?? '');
        switch ($module) {

            case 'http':
                (new HttpServer())->run();
                break;

            case 'tcp':
                (new TcpServer())->run();
                break;

            default:
                var_export('module not exists: ' . $module);
                break;
        }
    }
}
