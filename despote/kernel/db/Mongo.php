<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * MongoDB 操作类
 * @author      He110 (i@he110.info)
 * @namespace   desopte\kernel\db;
 */

namespace desopte\kernel\db;

use \desopte\base\Service;
use \Exception;
use \MongoDB\Driver\Manager;
use \MongoDB\Driver\WriteConcern;
use \Utils;

class Mongo extends Service
{
    // 服务器相关信息，包括地址、端口（可不写）、库名等
    protected $servers = [];
    // 用户名
    protected $user;
    // 密码
    protected $pwd;
    // 连接选项
    protected $opts;

    // 连接实例
    private $conn;

    public function init()
    {
        $this->connect();
    }

    private function createDsn()
    {
        // 设置别名，便于代码书写
        $servers = &$this->servers;

        // 不是数组则必须是 dsn，交由 connect 函数判断是否为合法 dsn，不是就报错
        if (!is_array($servers)) {
            return $servers;
        }

        if (Utils::isAssoc($servers)) {
            // 如果是关联数组，则只有一个数据库
            if (isset($servers['port'])) {
                $dsn = $servers['host'] . ':' . $servers['port'] . '/' . $servers['name'];
            } else {
                $dsn = $servers['host'] . '/' . $servers['name'];
            }
        } else {
            // 如果是索引数组，则有多个数据库
            foreach($servers as $index => $item) {
                if (isset($item['port'])) {
                    $servers[$index] = $item['host'] . ':' . $item['port'] . '/' . $item['name'];
                } else {
                    $servers[$index] = $item['host'] . '/' . $item['name'];
                }
            }
            $dsn = implode(',', $servers);
        }

        return 'mongo://' . $dsn;
    }

    private function connect($dsn, array $opts = [])
    {
        $user = [];
        if (isset($this->user) && isset($this->pwd)) {
            $user = [
                'username' => $this->user,
                'password' => $this->pwd,
            ];
        }
        $opts = array_merge($user, $this->opts, $opts);

        try {
            $this->conn = new Manager($dsn, $opts);
        } catch(Exception $e) {
            $this->showError($e->getMessage());
        }
    }

    public function getConn() {
        return $this->conn;
    }

    public function insert($data, $wMode = WriteConcern::MAJORITY, $timeout = 1200) {
        try {
            $wc = new WriteConcern($wMode);
        } catch(Exception $e) {
            $this->showError($e->getMessage());
        }
    }

    private function showError($msg) {
        throw new Exception($msg);
        die;
    }
}
