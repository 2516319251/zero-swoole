<?php

namespace app\home\controller;

use zero\server\tcp\TcpClient;

/**
 * Class Index
 * @package app\home\controller
 * @controller(prefix="/v1/index")
 */
class Index
{
    /**
     * @requestMapping(route="/index/index", method="get")
     * @return string
     */
    public function index()
    {
        $client = new TcpClient();
        $list = $client->service('ListService')->version('1.0')->list([
            'number' => 3,
            'type' => 'json',
            'by' => 'swoole'
        ]);
        return $list;
    }

    /**
     * @requestMapping(route="/index/home", method="get")
     * @return array
     */
    public function home()
    {
        return ['code' => 2000, 'message' => 'success~'];
    }

}
