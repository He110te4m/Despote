<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 自定义需要加载的组件，核心组件如未注册会根据默认配置自动加载
 * @author      He110 (i@he110.info)
 */

return [
    // // 路由组件
    // 'router'    => [
    //     'class'      => '\despote\kernel\Router',
    //     // 模块绑定，使用了模块绑定后 URL 中不需要加上模块名，变成：/controller/action
    //     // 如果设置为 false，当需要给默认模块的 action 传参或者使用非默认模块时，必须加上模块名
    //     // 本参数默认为 false
    //     'bindModule' => false,
    //     // 默认模块
    //     'module'     => 'Home',
    //     // 默认控制器
    //     'controller' => 'Index',
    //     // 默认 Action
    //     'action'     => 'index',
    // ],
    // MySQL
    'sql'    => [
        'class' => '\despote\kernel\db\MySQL',

        // // 数据库地址，默认为 localhost
        // 'host'  => 'localhost',

        // // 数据库端口，默认为 3306
        // 'port'  => 3306,

        // // 数据库用户名，默认为 root
        // 'usr'   => 'root',

        // // 数据库密码，默认为 root
        // 'pwd'   => 'root',

        // // 数据库名，默认为 test
        // 'name'  => 'blog',

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
    // MongoDB 操作类
    'mongo'  => [
        'class'  => '\despote\kernel\db\MonDB',
        'server' => [
            'host' => '192.168.157.129',
            'port' => 27017,
            'name' => 'db',
            'coll' => 'runob',
        ],
    ],
    // 日志记录
    'logger' => [
        'class' => '\despote\kernel\Logger',
        // 日志存放位置
        'path'  => PATH_LOG,
        // 日志等级
        'limit' => 5,
    ],
    // // cookie 操作类
    // 'cookie'    => [
    //     'class' => 'despote\kernel\Cookie',
    //     // 是否开启安全模式
    //     'safe'  => true,
    //     // 加密的密钥
    //     'key'   => 'Despote',
    // ],
    // // session 操作类
    // 'session' => 'despote\kernel\Session',
    // // 反向代理设置
    // 'proxy'     => [
    //     'class'  => '\despote\kernel\Proxy',
    //     'host'   => '107.178.194.20',
    //     'port'   => 8089,
    //     'subDir' => 'Index/home',
    // ],
    // // 文件上传
    // 'upload'  => '\despote\kernel\Upload',
    // // 快速缓存
    // 'cache'   => 'despote\kernel\cache\FastCache',
    // // 文件缓存
    // 'fileCache' => [
    //     'class' => 'despote\kernel\cache\FileCache',
    //     // 缓存路径
    //     'path'  => PATH_CACHE,
    //     // 缓存 GC 设置
    //     'gc'    => 50,
    // ],
    // // MemCache
    // 'memCache' => [
    //     'class'   => 'despote\kernel\cache\MemCache',
    //     // MemCache 服务器地址
    //     'servers' => [
    //         // 第一台 MemCache 服务器配置信息
    //         [
    //             // 服务器主机地址
    //             '127.0.0.1',
    //             // 服务器端口
    //             '11211',
    //             // 服务器权重
    //             33,
    //             // ], [
    //             //     '127.0.0.1',
    //             //     '11212',
    //             //     33,
    //             // ], [
    //             //     '127.0.0.1',
    //             //     '11213',
    //             //     33,
    //         ],
    //     ],
    // ],
    // 'md'       => '\despote\extend\Parsedown',
    // 'tpl'       => [
    //     'class'  => '\despote\kernel\Tpl',
    //     'module' => 'Home',
    // ],
    // 'token'   => [
    //     'class'  => '\despote\kernel\Token',
    //     // 加密的密钥
    //     'secret' => 'Despote',
    // ],
    'mail'   => [
        'class' => '\despote\extend\Mailer',
        // // 使用的安全协议
        // 'SMTPSecure' => 'ssl',
        // // 邮件的字符编码
        // 'CharSet'    => 'UTF-8',
        // // 是否进行安全认证
        // 'SMTPAuth'   => true,
        // // 邮件服务器端口
        // 'Port'  => 465,
        // // 邮件服务器地址
        // 'Host'       => 'smtp.exmail.qq.com',
        // // 邮件服务器登陆的用户名
        // 'User'  => 'i@he110.top',
        // // 邮件服务器登陆的密码
        // 'Pwd'   => 'test',
        // // 发送人邮箱
        // 'Form'       => 'i@he110.top',
        // // 发送人姓名
        // 'FormName'   => 'He110',
        // // 回复邮箱
        // 'ReplyTo'    => 'i@he110.top',
        // // 回复姓名
        // 'ReplyName'  => 'He110',
    ],
    // 'xml'    => '\despote\kernel\XML',
    'curl'   => '\despote\kernel\Curl',
];
