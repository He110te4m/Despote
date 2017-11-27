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
    // SQL 数据库操作类
    'sql'       => [
        'class' => '\despote\kernel\SQL',
        // // 数据库类型，暂仅支持 MySQL
        // 'type'  => 'mysql',

        // // 数据库地址，默认为 localhost
        // 'host'  => 'localhost',

        // // 数据库端口，默认为 3306
        // 'port'  => 3306,

        // // 数据库用户名，默认为 root
        // 'usr'   => 'root',

        // // 数据库密码，默认为 root
        // 'pwd'   => 'root',

        // 数据库名，默认为 test
        'name'  => 'despote',

        // // 是否开启持久连接，默认为 true
        // 'pconn' => true,

        // // 字符集，默认为 utf8
        // 'charset' => 'utf8',

        // // PDO 错误处理方式
        // // 可选的常量有：
        // // 1：只设置错误代码，缺省值
        // // 2：除了设置错误代码以外，PDO 还将发出一条传统的 E_WARNING 消息。
        // // 3：除了设置错误代码以外，PDO 还将抛出一个 PDOException，并设置其属性，以反映错误代码和错误信息。
        // 'errMode' => 1,

        // // 记录集返回方式
        // // 可选的常量有：
        // // 1：返回关联数组
        // // 2：返回数字数组
        // // 3：同时返回数字数组和关联数组
        // // 4：将结果集中的每一行作为一个属性名对应列名的对象返回
        // // 5：将结果集中的每一行作为一个对象返回，此对象的变量名对应着列名
        // // 6：从结果集中的下一行返回所需要的那一列
        // 'fetch'   => 1,

        // // 模拟预处理，默认为 false
        // 'pretreat' => false,
    ],
    // 日志记录
    'logger'    => [
        'class' => '\despote\kernel\Logger',
        'path'  => PATH_LOG,
        'limit' => 5,
    ],
    // // 反向代理设置
    // 'proxy'     => [
    //     'class'  => '\despote\kernel\Proxy',
    //     'host'   => '107.178.194.20',
    //     'port'   => 8089,
    //     'subDir' => 'Index/home',
    // ],
    // 文件上传
    'upload'    => '\despote\kernel\Upload',
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
                '127.0.0.1',
                // 服务器端口
                '11211',
                // 服务器权重
                33,
                // ], [
                //     '127.0.0.1',
                //     '11212',
                //     33,
                // ], [
                //     '127.0.0.1',
                //     '11213',
                //     33,
            ],
        ],
    ],
];
