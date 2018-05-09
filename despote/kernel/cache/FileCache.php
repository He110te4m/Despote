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

use \Despote;

class FileCache extends Cache
{
    // GC 设置，取值范围为 [0, 100]，GC 越小，清理越频繁，默认为 50
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
        is_dir($this->path) || Despote::file()->create($this->path, true);
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
     * @param  Boolean $flush 是否清除所有缓存，传入 false 时将仅清理失效缓存，传入 true 则清空所有缓存
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

    /**
     * 添加数据，当键名已存在时不添加
     * @param String   $key    键名
     * @param Mixed    $value  键值
     * @param Integer  $value  缓存有效时间，单位为 s(秒)，默认为 3 天
     */
    public function add($key, $value, $expiry = 259200)
    {
        $this->has($key) || $this->set($key, $value);
    }

    /**
     * 设置缓存数据
     * @param String   $key    键名
     * @param Mixed    $value  键值
     * @param Integer  $value  缓存有效时间，单位为 s(秒)，默认为 3 天
     */
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

    /**
     * 删除缓存数据
     * @param  String $key 要删除的键名
     */
    public function del($key)
    {
        $this->has($key) && @unlink($this->getCacheName($key));
    }

    /**
     * 获取数据
     * @param  String $key      键名
     * @param  String $default 键值不存在时返回的值，默认为空字符串
     * @return Mixed            键名对应的键值
     */
    public function get($key, $default = false)
    {
        // 获取默认值和缓存文件名
        $value     = $default;
        $cacheName = $this->getCacheName($key);

        // 使用文件独占锁尝试读取文件并反序列化
        if ($this->has($key) && $fp = @fopen($cacheName, 'r')) {
            @flock($fp, LOCK_SH);
            // 获取键值
            $value = unserialize(stream_get_contents($fp));
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }

        return $value;
    }

    /**
     * 查询数据是否存在
     * @param  String   $key  需要判断是否存在缓存的键名
     * @return Boolean        是否存在此缓存
     */
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
