<?php

/////////////
// 环境校验 //
////////////

// 设置最低版本需求
version_compare(PHP_VERSION, '5.4.0', '>=') || exit('Sorry, Despote require php5.4 or higher.<br>很抱歉，Despote 需要 php5.4 或更高的版本。<br><a href="https://www.github.com/he110te4m/despote.git" target="_blank">查看项目地址</a>');

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

// 根目录
define('PATH_ROOT', __DIR__ . DS);
// 框架目录
define('PATH_DESPOTE', PATH_ROOT . 'despote' . DS);
// 框架基类文件目录
define('PATH_BASE', PATH_DESPOTE . 'base' . DS);
// 框架核心文件目录
define('PATH_KERNEL', PATH_DESPOTE . 'kernel' . DS);

/////////////
// 功能设置 //
////////////

// 开启调试模式
define('DEBUG', false);
// 开启自定义错误处理
define('ERROR_CATCH', true);
// 定义访问校验
define('DESPOTE', true);

////////////////
// 开启自动加载 //
////////////////

require 'despote/kernel/Autoload.php';
\despote\kernel\Autoload::register();

////////////////////
// 资源统计相关常量 //
///////////////////

// 开始计时
$mtime = explode(' ', microtime());
define('CORE_RUN_AT', $mtime[1] + $mtime[0]);
// 统计内存使用
define('START_MEMORY', memory_get_usage());

// 事件触发：统计时间
// 事件触发：统计内存占用
Despote::run();
