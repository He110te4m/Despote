<?php

////////////////////
// 开发相关常量定义 //
///////////////////

// 定义时区
date_default_timezone_set("PRC");
// 设置分隔符
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

/////////////
// 路径定义 //
////////////

// 静态资源相对路径
defined('RES') or define('RES', '/static/');
// 模块所在目录
defined('APP') or define('APP', 'app');

// 根目录
defined('PATH_ROOT') or define('PATH_ROOT', dirname(__DIR__) . DS);
// 子目录配置
defined('PATH_CHILD') or define('PATH_CHILD', '');

// 框架目录
defined('PATH_DESPOTE') or define('PATH_DESPOTE', PATH_ROOT . 'despote' . DS);
// 定义运行目录
defined('PATH_APP') or define('PATH_APP', PATH_ROOT . 'app' . DS);
// 定义静态资源目录
defined('PATH_RES') or define('PATH_RES', PATH_ROOT . 'static' . DS);

// 框架基类文件目录
defined('PATH_BASE') or define('PATH_BASE', PATH_DESPOTE . 'base' . DS);
// 框架配置文件目录
defined('PATH_CONF') or define('PATH_CONF', PATH_DESPOTE . 'conf' . DS);
// 框架核心文件目录
defined('PATH_KERNEL') or define('PATH_KERNEL', PATH_DESPOTE . 'kernel' . DS);
// 框架扩展文件目录
defined('PATH_EXTEND') or define('PATH_EXTEND', PATH_DESPOTE . 'extend' . DS);

// 缓存目录
defined('PATH_CACHE') or define('PATH_CACHE', PATH_DESPOTE . 'runtime' . DS . 'cache' . DS);
// 缓存目录
defined('PATH_LOG') or define('PATH_LOG', PATH_DESPOTE . 'runtime' . DS . 'log' . DS);
// 文件锁目录
defined('PATH_LOCK') or define('PATH_LOCK', PATH_DESPOTE . 'runtime' . DS . 'lock' . DS);

// 定义视图文件访问校验
defined('DESPOTE') or define('DESPOTE', true);
