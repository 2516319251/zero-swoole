<?php

// app 应用参数设置

return [
    'http' => [
        'host'          => '0.0.0.0',
        'port'          => 80,
        'enable_tcp'    => true,
        'setting'       => [
            'reactor_num'   => 1,
            'worker_num'    => 1,
            'max_request'   => 0,
            'max_conn'      => 1000,
        ],
    ],
    'tcp' => [
        'host'      => '0.0.0.0',
        'port'      => 8000,
        'setting'   => [
            'reactor_num'   => 1,
            'worker_num'    => 1,
            'max_request'   => 0,
            'max_conn'      => 1000,
        ],
    ],
    'hot_reload_time' => 5000
];
