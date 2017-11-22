<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 模板引擎相关配置
 * @author      He110 (i@he110.top)
 * @namespace
 * @example
 */
return [
    // 模板所在目录
    'tplDir'   => PATH_APP . 'Home/view/',
    // 缓存所在目录
    'cacheDir' => PATH_APP . 'Home/view/cache/',
    // 模板引擎左定界符
    'tplBegin' => '<{',
    // 模板引擎右定界符
    'tplEnd'   => '}>',
    // 模板引擎右定界符
    'suffix'   => 'tpl',
    // 模板引擎右定界符
    'noCache'  => false,
];
