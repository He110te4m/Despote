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
use \MongoDB\BSON\ObjectID;
use \MongoDB\Driver\BulkWrite;
use \MongoDB\Driver\Manager;
use \MongoDB\Driver\Query;
use \MongoDB\Driver\WriteConcern;

class Mongo extends Service
{
    /////////////
    // 配置信息 //
    /////////////

    // 服务器相关信息，包括地址、端口（可不写）、库名等
    protected $servers = [];
    // 操作的数据库名
    protected $db;
    // 操作的集合
    protected $collection;
    // 连接选项
    protected $opts = [];

    /////////////
    // 对象属性 //
    /////////////

    // MongoDB 的 Manager 对象
    private $manage;
    // 筛选条件，即 SQL 中的 where 子句
    private $filter;

    public function init()
    {
        $server = &$this->servers;

        ///////////////
        // 参数初始化 //
        //////////////

        // MongoDB 服务器地址，必填
        if (isset($server['host'])) {
            $host = $server['host'];
        } else {
            $this->error('未正确配置 MongoDB 地址');
        }
        // 端口
        $port = isset($server['port']) ? ':' . $server['port'] : '';
        // 数据库名
        isset($server['name']) && $this->db = $server['name'];
        // 集合名
        isset($server['collection']) && $this->collection = $server['collection'];

        // 连接 MongoDB
        $this->manage = new Manager('mongodb://' . $host . $port, $this->opts);
    }

    /**
     * 插入数据
     * @param  Array    $data  需要插入的数据
     * @return Integer         成功插入的记录数
     */
    public function insert($data)
    {
        $bulk = new BulkWrite;
        $bulk->insert($data);

        // 插入数据并返回成功插入行数
        return $this->manage->executeBulkWrite($this->db . '.' . $this->collection, $bulk)->getInsertedCount();
    }

    /**
     * 删除数据
     * @return  Integer  成功删除的记录数
     */
    public function delete()
    {
        $bulk   = new BulkWrite;
        $filter = $this->filter;
        $bulk->delete($filter);

        // 删除数据并返回成功删除行数
        return $this->manage->executeBulkWrite($this->db . '.' . $this->collection, $bulk)->getDeletedCount();
    }

    /**
     * 修改数据
     * @param  Array    $data      更新后的数据
     * @param  Array    $opts      更新选项，默认只更新第一个匹配的
     *                             找不到满足 filter 的不插入新数据
     * @param  Integer  $wFlag     判断执行成功的标志
     *                             默认为大多数 MongoDB 服务器均执行成功才返回成功
     *                             修改为 0 则不管成不成功直接返回
     *                             修改为 1 则只要主 MongoDB 服务器执行成功就返回
     * @param  Integer  $timeout
     * @return Integer
     */
    public function update($data, $opts = [], $wFlag = WriteConcern::MAJORITY, $timeout = 1200)
    {
        $bluk = new BlukWrite;
        // 根据 filter 筛选需要更新的数据，使用 $set 对应的值更新数据
        $bluk->update($this->filter, ['$set' => $data], $opts);
        $wc = new WriteConcern($w, $timeout);

        return $this->manage->executeBulkWrite($this->db . '.' . $this->collection, $bulk, $wc)->getModifiedCount();
    }

    /**
     * 获取数据
     * @param  Array  $opts 查询配置选项
     * @return Array        查询出的结果
     */
    public function select($opts = [])
    {
        // 实例化查询对象
        $query = new Query($this->filter, $opts);

        // 执行查询命令
        try {
            $cursor = $this->manage->executeQuery($this->db . '.' . $this->collection, $query);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        // 结果转换为数组
        $res = $cursor->toArray();

        return $res;
    }

    /**
     * 读取一条记录，即判断是否存在
     * @param  Array  $opts 查询配置选项
     * @return Mixed        查询到返回查询结果，查询不到返回 null
     */
    public function find($opts = [])
    {
        $res = $this->select($opts);

        return isset($res[0]) ? $res($opts)[0] : null;
    }

    /**
     * 删除某条记录
     * @return Mixed 返回被删除的记录
     */
    public function remove()
    {
        $opts = [
            'findAndModify' => $collection,
            'query'         => $this->filter,
            'remove'        => true,
        ];
        $res = $this->select($opts);

        return $res[0];
    }

    public function count()
    {
    }

    /**
     * 条件筛选
     * @param  Array  $cond 筛选的条件
     * @return Object       本操作类对象本身，用于链式操作
     */
    public function where($cond)
    {
        // 将 ID 转化为对象
        is_string($cond['_id']) && $cond['_id'] = new ObjectID($cond['_id']);
        // 保存筛选条件
        $this->filter = $cond;

        return $this;
    }

    /**
     * 修改操作的数据库
     * @param String $db 数据库名
     */
    public function setDB($db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * 修改操作的集合（Collection）
     * @param String $coll 集合名
     */
    public function setColl($coll)
    {
        $this->collection = $coll;

        return $this;
    }

    /**
     * 显示异常，将显示方式交由错误捕获类（ErrCatch）处理
     * @param  String  $msg  异常信息
     * @param  Integer $code 异常代码
     */
    private function showError($msg, $code = 500)
    {
        throw new Exception($msg, $code);
        die;
    }
}
