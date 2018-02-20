<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * SQL 数据库操作类
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel\db;
 */
namespace despote\kernel\db;

use \Despote;
use \despote\base\Service;
use \Event;
use \PDO;

class MySQL extends Service
{
    /////////////
    // 单例对象 //
    /////////////

    // PDO 对象
    private $pdo = [];

    ////////////////
    // 数据库配置 //
    ////////////////

    // 事件集
    private $event = [
        'BEFORE_INSERT' => '',
        'BEFORE_DELETE' => '',
        'BEFORE_UPDATE' => '',
        'BEFORE_SELECT' => '',
        'AFTER_INSERT'  => '',
        'AFTER_DELETE'  => '',
        'AFTER_UPDATE'  => '',
        'AFTER_SELECT'  => '',
    ];

    //////////////
    // 公共配置 //
    //////////////

    // 数据库连接选项，可选
    protected $opts = [];
    // 默认字符集，可选
    protected $charset = 'utf8';
    // PDO 错误处理方式
    // 可选的常量有：
    // 1：只设置错误代码，缺省值
    // 2：除了设置错误代码以外，PDO 还将发出一条传统的 E_WARNING 消息。
    // 3：除了设置错误代码以外，PDO 还将抛出一个 PDOException，并设置其属性，以反映错误代码和错误信息。
    protected $errMode = 1;
    // PDO 错误处理方式可选列表
    private $errModeList = [
        '1' => PDO::ERRMODE_SILENT,
        '2' => PDO::ERRMODE_WARNING,
        '3' => PDO::ERRMODE_EXCEPTION,
    ];
    // 记录集返回方式
    // 可选的常量有：
    // 1：返回关联数组
    // 2：返回数字数组
    // 3：同时返回数字数组和关联数组
    // 4：将结果集中的每一行作为一个属性名对应列名的对象返回
    // 5：将结果集中的每一行作为一个对象返回，此对象的变量名对应着列名
    // 6：从结果集中的下一行返回所需要的那一列
    protected $fetch   = 1;
    private $fetchList = [
        '1' => PDO::FETCH_ASSOC,
        '2' => PDO::FETCH_NUM,
        '3' => PDO::FETCH_BOTH,
        '4' => PDO::FETCH_OBJ,
        '5' => PDO::FETCH_LAZY,
        '6' => PDO::FETCH_COLUMN,
    ];
    // 是否开启模拟预处理
    protected $pretreat = false;

    ////////////////
    // MySQL 配置 //
    ////////////////

    // 数据库主机地址
    protected $host = 'localhost';
    // 数据库端口，可选
    protected $port = 3306;
    // 数据库用户名
    protected $usr = 'root';
    // 数据库密码
    protected $pwd = 'root';
    // 数据库名
    protected $name = 'test';
    // 是否开启持久连接，在多进程服务器（如fastcgi、php-fpm）中，使用数据库持久连接可以提升服务器性能和抗压能力，可选
    protected $pconn = true;
    // 数据库表前缀
    protected $prefix = '';

    protected function init()
    {
        $this->conn($this->name);
    }

    /////////////
    // SQL 相关 //
    /////////////

    /**
     * 执行 SQL 语句
     * @param  String $sql  SQL 语句，可包含带预处理符号，如：delete from `user` where `Id` = ?
     * @param  array  $data 如果传入的 SQL 语句带有预处理，则必须传入该参数，用于赋值给预处理变量
     * @return Mixed        如果执行成功，返回记录集对象；处理失败返回 false
     */
    public function execSQL($sql, $event = 'select', $data = [], $name = '')
    {
        $pdo = $this->getIns($name);

        if ($data === []) {
            // 直接返回结果
            $res = $pdo->query($sql);
        } else {
            // 对 SQL 语句进行预处理
            $res = $pdo->prepare($sql);
            // 如果预处理失败则直接返回
            if (!$res) {
                return false;
            }
            // 执行预处理后的语句
            $res->execute($data);
        }

        // 触发 after 事件
        $event = 'AFTER_' . strtoupper($event);
        Event::trigger($event);

        return $res;
    }

    /**
     * 插入数据
     * @param  String   $table      待插入的表名
     * @param  String   $colName    插入的字段名，多个字段使用 , 隔开
     * @param  array    $data       要插入的值，与字段一一对应
     * @return Object               执行 SQL 后返回的记录集
     */
    public function insert($table, $colName, $data = [], $name = '')
    {
        $table = $this->prefix . $table;
        $value = "VALUES(?)";
        if (strpos($colName, ',') !== false) {
            $fields = explode(',', $colName);
            $value  = "VALUES(";
            $num    = count($fields);
            for ($i = 0; $i < $num - 1; $i++) {
                $value .= '?,';
            }
            $value .= '?)';
        }
        $sql = "INSERT INTO $table ($colName) $value";

        // 触发 before 事件
        Event::trigger('BEFORE_INSERT');

        return $this->execsql($sql, 'insert', $data, $name);
    }

    /**
     * 删除数据
     * @param  String   $table      需要删除的表名
     * @param  String   $condition  删除条件，可以包含 where 等语句，如：where name=? and pass=?
     * @param  Array    $data       条件中涉及的变量
     * @return Object               执行 SQL 后的记录集对象
     */
    public function delete($table, $condition, $data = [], $name = '')
    {
        $table = $this->prefix . $table;

        $sql = "DELETE FROM $table $condition";

        // 触发 before 事件
        Event::trigger('BEFORE_DELETE');

        return $this->execsql($sql, 'delete', $data, $name);
    }

    /**
     * 更新数据
     * @param  String   $table      需要更新的表名
     * @param  String   $set        更新的数据，格式为：字段名=?，如果需要同时更新多个字段，使用 , 隔开
     * @param  String   $condition  更新条件可以包含 where 等语句，如：where name=? and pass=?
     * @param  Array    $data       更新的数据和更新条件中涉及的变量
     * @return Object               执行 SQL 后的记录集对象
     */
    public function update($table, $set, $condition, $data = [], $name = '')
    {
        $table = $this->prefix . $table;

        $sql = "UPDATE $table SET $set $condition";

        // 触发 before 事件
        Event::trigger('BEFORE_UPDATE');

        return $this->execsql($sql, 'update', $data, $name);
    }

    /**
     * 查找数据
     * @param  String   $colName    需要查找的字段名
     * @param  String   $table      需要查找的表
     * @param  String   $condition  查找条件，可以包含 where 等语句，如：where name=? and pass=?
     * @param  array    $data       条件中涉及的变量
     * @return Object               执行 SQL 后的记录集对象
     */
    public function select($colName, $table, $condition = '', $data = [], $name = '')
    {
        $table = $this->prefix . $table;

        $sql = "SELECT $colName FROM $table $condition";

        // 触发 before 事件
        Event::trigger('BEFORE_SELECT');

        // 无缓存处理
        $res = $this->execsql($sql, 'select', $data, $name);
        if ($res === false) {
            throw new \Exception("SQL 语句：{$sql} 查询失败", 500);
            return;
        }

        return $res;
    }

    public function getIns($name = '')
    {
        if (empty($name)) {
            return $this->pdo[$this->name];
        } else {
            if (!isset($this->pdo[$name])) {
                $this->conn($name);
            }
            return $this->pdo[$name];
        }
    }

    public function getLastID($name = '')
    {
        if (empty($name)) {
            return $this->getIns($name)->lastInsertId();
        }
    }

    public function conn($name)
    {
        // 引用变量节省代码量
        $pdo = &$this->pdo;

        // 存放 PDO 单例
        $pdo[$name] = new PDO('mysql:dbname=' . $name . ';host=' . $this->host . ';port=' . $this->port, $this->usr, $this->pwd, $this->opts);

        // 设置默认字符集
        $pdo[$name]->exec('SET NAMES ' . $this->charset);
        // 设置以报错形式
        $pdo[$name]->setAttribute(PDO::ATTR_ERRMODE, $this->errModeList[$this->errMode]);
        // 设置 fetch 时返回数据形式
        $pdo[$name]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->fetchList[$this->fetch]);
        // 设置是否启用模拟预处理
        $pdo[$name]->setAttribute(PDO::ATTR_EMULATE_PREPARES, $this->pretreat);
    }

    public function setDB($name)
    {
        $this->name = $name;
        // 如果这个数据库没有连接上，则创建连接
        isset($this->pdo[$name]) || $this->conn($name);
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /////////////
    // 事务相关 //
    /////////////

    /**
     * 开始事务
     * @param  string $name 开启事务的数据库名
     */
    public function begin($name = '')
    {
        $this->getIns($name)->beginTransaction();
    }

    /**
     * 提交事务
     * @param  string $name 提交事务的数据库名
     */
    public function commit($name = '')
    {
        $this->getIns($name)->commit();
    }

    /**
     * 回滚事务
     * @param  string $name 回滚事务的数据库名
     */
    public function back($name = '')
    {
        $this->getIns($name)->rollBack();
    }

    ////////////////
    // 事件触发相关 //
    ////////////////

    /**
     * 使用魔术方法回调进行事件触发
     */
    public function __call($func, $args = [])
    {
        empty($this->event[$func]) || call_user_func_array($this->event[$func], $args);
    }

    /**
     * 注册单个事件
     * @param  String  $event    事件名称，必须在本类中的 event 属性中存在的才能注册
     * @param  Closure $callback 匿名函数
     */
    public function hook($event, \Closure $callback)
    {
        if (array_key_exists($event, $this->event)) {
            $this->event[$event] = $callback;
            Event::on($event, [$this, $event]);
        }
    }

    /**
     * 批量注册事件，使用数组对多个事件进行初始化
     * @param  array  $events 关联数组，格式为：['事件名' => 匿名函数]，注意，不能传入回调函数，只能直接传入函数
     */
    public function hookArray($events = [])
    {
        foreach ($events as $event => $func) {
            $this->hook($event, $func);
        }
    }

    ////////////////////
    // 使用缓存获取数据 //
    ///////////////////

    /**
     * 获取单条数据
     * @param  Object  $res    PDOStatement 对象
     * @param  integer $expiry 缓存有效期，默认为一天，即 86400 秒
     * @return Object          获取的行数据
     */
    public function fetch($res, $expiry = 86400)
    {
        $cache = Despote::fileCache();

        $result = $cache->get($res->queryString);
        if (!$result) {
            $result = $res->fetch();
            $cache->set($res->queryString, $result, $expiry);
        }
        return $result;
    }

    /**
     * 获取多条数据
     * @param  Object  $res    PDOStatement 对象
     * @param  integer $expiry 缓存有效期，默认为一天，即 86400 秒
     * @return Array           获取的所有行数据
     */
    public function fetchAll($res, $expiry = 86400)
    {
        $cache = Despote::fileCache();

        $result = $cache->get($res->queryString);
        if (!$result) {
            $result = $res->fetchAll();
            $cache->set($res->queryString, $result, $expiry);
        }
        return $result;
    }
}
