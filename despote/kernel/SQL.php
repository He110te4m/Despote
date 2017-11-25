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
 * @namespace   despote\kernel;
 */
namespace despote\kernel;

use \despote\base\Service;
use \Event;
use \Exception;
use \PDO;

class SQL extends Service
{
    /////////////
    // 单例对象 //
    /////////////

    // PDO 对象
    private $pdo;

    ////////////////
    // 数据库配置 //
    ////////////////

    // 数据库类型
    protected $type = 'mysql';

    //////////////
    // 公共配置 //
    //////////////

    // 数据库连接选项，可选
    protected $opts = [];

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

    /////////////////
    // SQLite 配置 //
    /////////////////
    // 数据库地址
    protected $file;
    // 是否开启强制磁盘同步
    // 可选值为：
    //   FULL  (完全磁盘同步：断电或死机不会损坏数据库，但是很慢)
    //   NORMAL(普通磁盘同步：大部分情况天断电或死机不会损坏数据库，比 OFF 慢)
    //   OFF   (不强制磁盘同步：断电或死机后很容易损坏数据库，但是插入或更新速度比 FULL 提升 50 倍，由系统自行将更改写入数据库中而不强制同步到磁盘)
    protected $sync = 'OFF';

    public function init()
    {
        $pdo = &$this->pdo;
        switch ($this->type) {
            case 'mysql':
                // 创建 PDO 对象
                $pdo = new PDO('mysql:dbname=' . $this->name . ';host=' . $this->host . ';port=' . $this->port, $this->usr, $this->pwd, $this->opts);
                // 设置默认字符集
                $pdo->exec('SET NAMES ' . $this->charset);
                // 设置以报错形式
                $pdo->setAttribute(PDO::ATTR_ERRMODE, $this->errModeList[$this->errMode]);
                // 设置 fetch 时返回数据形式
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->fetchList[$this->fetch]);
                // 设置是否启用模拟预处理
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $this->pretreat);
                break;

            case 'sqlite':
                break;

            default:
                throw new Exception('暂不支持 ' . $this->type . ' 类型的数据库', 500);
                break;
        }
    }

    /**
     * 执行 SQL 语句
     * @param  String $sql  SQL 语句，可包含带预处理符号，如：delete from `user` where `Id` = ?
     * @param  array  $data 如果传入的 SQL 语句带有预处理，则必须传入该参数，用于赋值给预处理变量
     * @return Mixed        如果执行成功，返回记录集对象；处理失败返回 false
     */
    public function execSQL($sql, $data = [])
    {
        if ($data === []) {
            // 直接返回结果
            $res = $this->pdo->query($sql);
        } else {
            // 对 SQL 语句进行预处理
            $res = $this->pdo->prepare($sql);
            // 如果预处理失败则直接返回
            if (!$res) {
                return false;
            }
            // 执行预处理后的语句
            $res->execute($data);
        }

        return $res;
    }

    /**
     * 插入数据
     * @param  String   $table      待插入的表名
     * @param  String   $colName    插入的字段名，多个字段使用 , 隔开
     * @param  array    $data       要插入的值，与字段一一对应
     * @return Object               执行 SQL 后返回的记录集
     */
    public function insert($table, $colName, $data = [])
    {
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

        Event::trigger('BRFORE_INSERT');

        return $this->execsql($sql, $data);
    }

    /**
     * 删除数据
     * @param  String   $table      需要删除的表名
     * @param  String   $condition  删除条件，可以包含 where 等语句，如：where name=? and pass=?
     * @param  Array    $data       条件中涉及的变量
     * @return Object               执行 SQL 后的记录集对象
     */
    public function delete($table, $condition, $data = [])
    {
        $sql = "DELETE FROM $table $condition";

        Event::trigger('BRFORE_DELETE');

        return $this->execsql($sql, $data);
    }

    /**
     * 更新数据
     * @param  String   $table      需要更新的表名
     * @param  String   $set        更新的数据，格式为：字段名=?，如果需要同时更新多个字段，使用 , 隔开
     * @param  String   $condition  更新条件可以包含 where 等语句，如：where name=? and pass=?
     * @param  Array    $data       更新的数据和更新条件中涉及的变量
     * @return Object               执行 SQL 后的记录集对象
     */
    public function update($table, $set, $condition, $data = [])
    {
        $sql = "UPDATE $table SET $set $condition";

        Event::trigger('BRFORE_UPDATE');

        return $this->execsql($sql, $data);
    }

    /**
     * 查找数据
     * @param  String   $colName    需要查找的字段名
     * @param  String   $table      需要查找的表
     * @param  String   $condition  查找条件，可以包含 where 等语句，如：where name=? and pass=?
     * @param  array    $data       条件中涉及的变量
     * @return Object               执行 SQL 后的记录集对象
     */
    public function select($colName, $table, $condition = '', $data = [])
    {
        $sql = "SELECT $colName FROM $table $condition";

        Event::trigger('BRFORE_SELETE');

        return $this->execsql($sql, $data);
    }
}
