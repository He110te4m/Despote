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
    'router' => [
        'class'      => '\despote\kernel\Router',
        'module'     => 'Home',
        'controller' => 'Index',
        'action'     => 'index',
    ],
];