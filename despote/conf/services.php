<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 自定义需要加载的组件
 * @author      He110 (i@he110.top)
 */

return [
    // 路由组件
    'router'    => [
        'class'      => '\despote\kernel\Router',
        // 默认模块
        'module'     => 'Home',
        // 默认控制器
        'controller' => 'Index',
        // 默认 Action
        'action'     => 'index',
    ],
    // 快速缓存
    'cache'     => 'despote\kernel\cache\FastCache',
    // 文件缓存
    'fileCache' => [
        'class' => 'despote\kernel\cache\FileCache',
        // 缓存路径
        'gc'    => 50,
        'path'  => PATH_CACHE,
    ],
    // MemCache
    'memCache'  => [
        'class'   => 'despote\kernel\cache\MemCache',
        // MemCache 服务器地址
        'servers' => [
            // 第一台 MemCache 服务器配置信息
            [
                // 服务器主机地址
                'host'   => '127.0.0.1',
                // 服务器端口
                'port'   => '11211',
                // 服务器权重
                'weight' => 33,
            ], [
                'host'   => '127.0.0.1',
                'port'   => '11212',
                'weight' => 33,
            ], [
                'host'   => '127.0.0.1',
                'port'   => '11213',
                'weight' => 33,
            ],
        ],
        // 缓存服务器用户名
        'usr'     => 'root',
        // 缓存服务器密码
        'pwd'     => 'root',
    ],
];
