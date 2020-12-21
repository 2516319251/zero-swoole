<?php

namespace zero\server\tcp;

use zero\Container;

/**
 * Class TcpClient
 * @package zero\server\tcp
 */
class TcpClient
{
    /**
     * @var string $host 请求地址
     */
    protected $host;

    /**
     * @var int $port 请求端口
     */
    protected $port;

    /**
     * @var string $version 版本
     */
    protected $version = '1.0';

    /**
     * @var string $service 服务名
     */
    protected $service = '';

    /**
     * @param $name
     * @param $args
     * @return $this
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        // 服务
        if ('service' == $name) {
            $this->service = $args[0];
            return $this;
        }
        // 版本
        if ('version' == $name) {
            $this->version = $args[0];
            return $this;
        }

        // 获取服务配置
        $config = Container::get('config')->get('tcp_services')[ucfirst($this->service . '_' . $this->version)];
        if (!empty($config)) {
            $this->host = $config['host'];
            $this->port = $config['port'];
        } else {
            throw new \Exception('没有找到对应服务，请核对配置');
        }

        // 封装数据 json_rpc 编码协议
        $req = [
            "jsonrpc" => '2.0',
            "method" => sprintf("%s::%s::%s", $this->version, $this->service, $name),
            'params' => $args ?? []
        ];
        $data = json_encode($req);

        // 实例化客户端并连接服务
        $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        if (!$client->connect($this->host, $this->port)) {
            throw new \Exception("connect failed. error: {$client->errCode}\n");
        }

        // 发送请求并返回结果
        $client->send($data);
        return $client->recv();
    }

}
