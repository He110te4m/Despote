<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 自定义特殊路由，用于加载特殊路由
 * @author      He110 (i@he110.info)
 */

return [
    // 匹配 http://doname/Install
    '/Install'   => [
        // 需要调用的控制器类
        'ctrl'  => 'app\Home\controller\Index',
        // 需要调用的类方法
        'action' => 'index',
    ],
];
