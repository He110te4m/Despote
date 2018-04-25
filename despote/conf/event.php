<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 系统事件注册，本文件中所有的事件将在框架 Core 加载时自动注册
 * @author      He110 (i@he110.info)
 */

return [
    [
        'name'     => 'TICK',
        'callback' => 'Utils::tick',
    ],
    [
        'name'     => 'INIT_CONFIG',
        'callback' => 'Utils::initConf',
    ],
    [
        'name'     => 'ERROR_CATCH_ON',
        'callback' => '\despote\kernel\ErrCatch::register',
    ],
    [
        'name'     => 'ERROR_CATCH_OFF',
        'callback' => '\despote\kernel\ErrCatch::unregister',
    ],
    [
        'name'     => 'DEBUG_BEGIN',
        'callback' => 'Utils::begin',
    ],
    [
        'name'     => 'DEBUG_END',
        'callback' => 'Utils::end',
    ],
    [
        'name'     => 'LOGGER',
        'callback' => '\despote\kernel\logger::save',
    ],
];
