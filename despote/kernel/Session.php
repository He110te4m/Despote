<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * SESSION 操作类
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \despote\base\Service;

class Session extends Service
{
    public function init()
    {
        isset($_SESSION) || session_start();
    }

    /**
     * 添加 session，如果存在则不添加
     * @param String  $key    session 的键名
     * @param Mixed   $value  session 的键值
     */
    public function add($key, $value)
    {
        $this->has($key) || $this->set($key, $value);
    }

    /**
     * 设置 session，如果存在则直接覆盖
     * @param String  $key    session 的键名
     * @param Mixed   $value  session 的键值
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 获取 session 的值
     * @param  String $key session 的键名
     * @return Mixed       session 的键值
     */
    public function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * 删除 session
     * @param  String $key session 的键名
     */
    public function del($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * 判断 session 是否存在
     * @param  String  $key session 的键名
     * @return boolean      session 是否存在
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * 清空 session 数组
     */
    public function flush()
    {
        $_SESSION = [];
    }
}
