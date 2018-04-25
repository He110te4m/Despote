<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 自定义加载指定类，用于加载不遵循 PSR4 自动加载规范类，键名为类名，键值为类文件路径
 * @author      He110 (i@he110.info)
 */

return [
    'Event'   => PATH_KERNEL . 'Event.php',
    'Utils'   => PATH_KERNEL . 'Utils.php',
    'Despote' => PATH_DESPOTE . 'Despote.php',
];
