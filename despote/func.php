<?php
/*
 *  放置一些常用的短函数，便于调用
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * @author      He110 (i@he110.info)
 */

/**
 * 获取运行到目前为止消耗的时间，单位为 秒 (s)
 * @param  String  $title 统计分类，默认为 Core，即核心框架加载
 * @return Double         从加载到现在消耗的秒数
 */
function gt($title = 'Core')
{
    return \Utils::getRunTime($title);
}

/**
 * 获取运行到目前为止占用的内存，单位为 兆 (MB)
 * @param  String  $title 统计分类，默认为 Core，即核心框架加载
 * @return Double         从加载到现在消耗的内存
 */
function gm($title = 'Core')
{
    return \Utils::getUseMemory($title);
}

/**
 * 获取系统配置信息
 * @param  String $name         配置名
 * @param  Mixed  $defaultValue 配置不存在时返回的值，不配置默认返回空字符串
 * @return Mixed                配置存在时返回配置值，否则返回 defaultValue 的值
 */
function c($name, $defaultValue = '')
{
    empty(Utils::$config) && Utils::$config = require PATH_CONF . 'config.php';

    return isset(Utils::$config[$name]) ? Utils::$config[$name] : $defaultValue;
}
