<?php
/**
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 文件锁，处理并发下的代码执行问题
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \despote\base\Service;
use \Despote;

class Lock extends Service
{
    // 锁文件所在目录
    protected $path;

    protected function init()
    {
        // 校验路径是否设置
        empty($this->path) && $this->path = PATH_LOCK;
        // 校验路径是否存在
        is_dir($this->path) || Despote::file()->create($this->path, true);
    }

    /**
     * 加锁后执行代码，需要将代码封装在函数中，函数执行完自动释放锁
     * @param  String   $key  这段代码标识符，用于多个锁的情况
     * @param  callable $call 需要加锁执行的代码
     * @param  Integer  $type 锁类型，默认为 LOCK_EX，独占锁，可选值：
     *                        共享锁：LOCK_SH
     *                        独占锁：LOCK_EX
     *                        非阻塞共享锁：LOCK_SH | LOCK_NB（Windows 下无效）
     *                        非阻塞独占锁：LOCK_EX | LOCK_NB（Windows 下无效）
     * @return Mixed          成功返回函数执行结果，失败返回 false
     */
    public function lock($key, callable $call, $type = LOCK_SH)
    {
        if ($fp = @fopen($this->path . DS . md5($key), 'w')) {
            if (@flock($fp, $type)) {
                $res = call_user_func($call);
                @flock($fp, LOCK_UN);
            }
        }
        @fclose($fp);
        @unlink($this->path . DS . md5($key));

        return isset($res) ? $res : false;
    }
}
