<?php

/////////////
// 环境校验 //
////////////

// 设置最低版本需求
version_compare(PHP_VERSION, '5.5.0', '>=') || exit('Sorry, Despote require php5.5 or higher.<br>很抱歉，Despote 需要 php5.5 或更高的版本。<br><a href="https://www.github.com/he110te4m/despote" target="_blank">查看项目地址</a>');
// 初始化环境常量和事件
require 'despote/init.php';
// 使用 composer 自动加载
file_exists(PATH_ROOT . 'vendor' . DS . 'autoload.php') && require PATH_ROOT . 'vendor' . DS . 'autoload.php';
// 载入内置短函数，方便调用
file_exists(PATH_DESPOTE . 'func.php') && require PATH_DESPOTE . 'func.php';

////////////////
// 开启自动加载 //
////////////////

require 'despote/kernel/Autoload.php';
\despote\kernel\Autoload::register();

Despote::run();
