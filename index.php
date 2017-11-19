<?php

/////////////
// 环境校验 //
////////////

// 设置最低版本需求
version_compare(PHP_VERSION, '5.4.0', '>=') || exit('Sorry, Despote require php5.4 or higher.<br>很抱歉，Despote 需要 php5.4 或更高的版本。<br><a href="https://www.github.com/he110te4m/despote.git" target="_blank">查看项目地址</a>');
// 初始化环境常量和事件
require 'despote/init.php';

////////////////
// 开启自动加载 //
////////////////

require 'despote/kernel/Autoload.php';
\despote\kernel\Autoload::register();

Despote::run();
