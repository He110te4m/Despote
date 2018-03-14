<?php

////////////////////
// 开发相关常量定义 //
///////////////////

// 定义时区
date_default_timezone_set("PRC");
// 设置分隔符
define('DS', DIRECTORY_SEPARATOR);

/////////////
// 路径定义 //
////////////

// 静态资源相对路径
define('RES', '/static/');
// 模块所在目录
define('APP', '\app\\');

// 根目录
define('PATH_ROOT', dirname(__DIR__) . DS);

// 框架目录
define('PATH_DESPOTE', PATH_ROOT . 'despote' . DS);
// 定义运行目录
define('PATH_APP', PATH_ROOT . 'app' . DS);
// 定义静态资源目录
define('PATH_RES', PATH_ROOT . 'static' . DS);

// 框架基类文件目录
define('PATH_BASE', PATH_DESPOTE . 'base' . DS);
// 框架配置文件目录
define('PATH_CONF', PATH_DESPOTE . 'conf' . DS);
// 框架核心文件目录
define('PATH_KERNEL', PATH_DESPOTE . 'kernel' . DS);
// 框架扩展文件目录
define('PATH_EXTEND', PATH_DESPOTE . 'extend' . DS);

// 缓存目录
define('PATH_CACHE', PATH_DESPOTE . 'runtime' . DS . 'cache' . DS);
// 缓存目录
define('PATH_LOG', PATH_DESPOTE . 'runtime' . DS . 'log' . DS);
// 文件锁目录
define('PATH_LOCK', PATH_DESPOTE . 'runtime' . DS . 'lock' . DS);

// 定义视图文件访问校验
define('DESPOTE', true);
