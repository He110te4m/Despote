<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 文件缓存类，基于文件的缓存，根据设置的缓存时间决定是否生命期，能持久化，速度不如快速缓存
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel\cache
 */

namespace despote\kernel\cache;

class FileCache extends Cache
{
    // GC 设置
    protected $gc;
    // 文件缓存目录
    protected $path;

    /**
     * 在构造函数中自动调用的初始化函数
     */
    public function init()
    {
        // 校验路径是否设置
        empty($this->path) && $this->path = PATH_CACHE;
        // 校验路径是否存在
        is_dir($this->path) || \Despote::file()->create($this->path, true);
        // 校验自动清理设置
        isset($this->gc) || $this->gc = 50;
    }

    /**
     * 根据缓存键名获取缓存文件对应文件名
     * @param  String $key 缓存键名
     * @return String      缓存文件名 (含绝对路径)
     */
    private function getCacheName($key)
    {
        return $this->path . DS . md5($key) . '.cache';
    }

    /**
     * 自动随机清理
     * @param  boolean $flush 是否清除所有缓存，传入 false 时将随机调用缓存清理
     */
    private function gc($flush = false)
    {
        if ($flush) {
            foreach (glob($this->path . DS . '*.cache') as $file) {
                @unlink($file);
            }
        } else if (mt_rand(0, 100) > $this->gc) {
            foreach (glob($this->path . DS . '*.cache') as $file) {
                if (@filemtime($file) < time()) {
                    @unlink($file);
                }
            }
        }
    }

    public function add($key, $value, $expiry = 259200)
    {
        $this->has($key) || $this->set($key, $value);
    }

    // 设置数据接口规范
    public function set($key, $value, $expiry = 259200)
    {
        // 启动随机清理缓存机制
        $this->gc();

        $cacheName = $this->getCacheName($key);
        if (file_put_contents($cacheName, serialize($value), LOCK_EX) !== false) {
            $expire = time() + ($expiry > 0 ? $expiry : 259200);

            touch($cacheName, $expire);
        }
    }

    // 删除数据接口规范
    public function del($key)
    {
        $this->has($key) && @unlink($this->getCacheName($key));
    }

    // 获取数据接口规范
    public function get($key)
    {
        $cacheName = $this->getCacheName($key);

        // 使用文件独占锁读取文件并反序列化
        if ($this->has($key) && $fp = @fopen($cacheName, 'r')) {
            @flock($fp, LOCK_SH);
            $value = unserialize(stream_get_contents($fp));
            @flock($fp, LOCK_UN);
            @fclose($fp);

            return $value;
        }
        return false;
    }

    // 查询数据是否存在接口规范
    public function has($key)
    {
        if (is_file($this->getCacheName($key))) {
            return @filemtime($this->getCacheName($key)) > time();
        }
        return false;
    }

    /**
     * 清空所有缓存
     */
    public function flush()
    {
        $this->gc(true);
    }
}
