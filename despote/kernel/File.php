<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 事件触发类
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \despote\base\Service;

class File extends Service
{
    /**
     * 创建文件或文件夹
     * @param  String  $file  文件绝对地址(文件夹属于特殊文件)
     * @param  boolean $isDir 是否是文件夹，默认为 false
     * @param  integer $mode  文件权限
     * @return Boolean        创建成功返回 true，创建失败返回 false
     */
    public static function create($file, $isDir = false, $mode = 0775)
    {
        // 如果是目录，就判断目录是否不存在，如果是文件，就判断文件所在的目录是否不存在，只要有一个条件满足，就创建目录
        if (($isDir && !is_dir($file)) || (!$isDir && !is_dir(dirname($file)))) {
            $mdir = is_dir($file) ? $file : dirname($file);
            // 创建文件并给权限
            @mkdir($mdir, $mode, true);
            @chmod($mdir, $mode);
        }

        // 前面的 if 保证目录肯定存在，如果需要的是目录，可以直接返回了
        if ($isDir) {
            return true;
        }

        // 创建空文件，如果成功，返回 true
        $fileHandle = @fopen($file, 'w');
        if ($fileHandle) {
            fclose($fileHandle);
            return true;
        }

        // 失败返回 false
        return false;
    }

    /**
     * 获取文件指定行数内容
     * @param  String $file 文件绝对路径
     * @param  Mixed  $line 读取单行传入行数，读取连续多行传入数组，如：[1, 100]
     * @param  string $mode 文件读取模式，默认为 rb
     * @return Mixed        读取单行返回字符串，读取多行返回字符串数组
     */
    public static function getLine($file, $line, $mode = 'rb')
    {
        // 文件反射对象
        $fileObj = new \SplFileObject($file, $mode);
        if (is_array($line) && count($line) > 1) {
            // 需要读取的文件内容
            $content = [];
            // 初始化相关变量
            $startLine = $line[0];
            $endLine   = $line[count($line) - 1];
            $count     = $endLine - $startLine;

            // 转到第 N 行, seek 方法参数从 0 开始计数
            $fileObj->seek($startLine - 1);
            for ($i = 0; $i <= $count; ++$i) {
                // current() 获取当前行内容
                $content[] = $fileObj->current();
                // 下一行
                $fileObj->next();
            }
        } else {
            // 判断 line 参数传入 [行数] 这种情况
            is_array($line) && $line = $line[0];
            // 转到需要读取的行
            $fileObj->seek($line - 1);
            // 获取内容
            $content = $fileObj->current();
        }

        return $content;
    }
}
