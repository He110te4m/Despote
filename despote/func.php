<?php
/*
 *  放置一些常用的段函数，便于调用
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * @author      He110 (i@he110.top)
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
