<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 文件操作类
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \despote\base\Service;

class File extends Service
{
    /**
     * 创建文件或目录
     * @param  String  $file  文件绝对地址(目录属于特殊文件)
     * @param  boolean $isDir 是否是目录，默认为 false
     * @param  integer $mode  文件权限
     * @return Boolean        创建成功返回 true，创建失败返回 false
     */
    public function create($file, $isDir = false, $mode = 0775)
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
    public function getLine($file, $line, $mode = 'rb')
    {
        // 文件反射对象
        $fileObj = new \SplFileObject($file, $mode);
        if (is_array($line) && count($line) > 1) {
            // 需要读取的文件内容
            $content = [];
            // 初始化相关变量
            $startLine = $line[0] >= 0 ? $line[0] : 0;
            $endLine   = $line[count($line) - 1];
            $count     = $endLine - $startLine;

            // 转到第 N 行, seek 方法参数从 0 开始计数
            $fileObj->seek($startLine - 1);
            for ($i = 0; $i <= $count; ++$i) {
                // current() 获取当前行内容
                $content[] = $fileObj->current();
                // 下一行
                $fileObj->next();
                // 如果到行尾就结束
                if ($fileObj->eof()) {
                    break;
                }
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

    /**
     * 获取文件大小
     * @param  String $filename 文件所在路径，支持中文，支持目录（传入目录时会递归子目录）
     * @return String           文件大小，包含单位
     */
    public function getSize($filename)
    {
        // 定义处理的单位和对应的每单位的字节大小
        $units = ['GB', 'MB', 'KB', 'B'];
        $vals  = [1073741824, 1048576, 1024, 1];
        $size  = 0;

        // 兼容中文处理
        $filename = iconv('utf-8', 'gbk', $filename);
        // 获取文件大小
        $num = 0;

        // 统计文件/目录大小
        if (file_exists($filename)) {
            if (is_dir($filename)) {
                $num  = 0;
                $list = $this->listDir($filename);
                foreach ($list as $item) {
                    $num += filesize($item);
                }
            }
            if (is_file($filename)) {
                $num = filesize($filename);
            }
        }

        // 单位转换
        foreach ($vals as $index => $item) {
            $temp = floor($num / $item);
            if ($temp > 0) {
                $size = round($num / $item, 2) . $units[$index];
                break;
            }
        }

        return $size;
    }

    /**
     * 遍历目录下所有文件，包括子目录里的文件
     * @param  String $dir 目录路径
     * @return Array       文件数组，该数组为一维的，数组元素为文件的完整路径
     */
    public function listDir($dir)
    {
        $files = [];

        // 是目录才遍历
        if (is_dir($dir)) {
            // 打开目录，获取句柄
            if ($handle = opendir($dir)) {
                // 循环读取文件和目录
                while (false !== ($file = readdir($handle))) {
                    // 排除特殊目录
                    if ($file != '.' && $file != '..') {
                        $filename = $dir . '/' . $file;
                        // 如果是子目录就递归子目录
                        if (is_file($filename)) {
                            $files[] = $filename;
                        } else {
                            $files = array_merge($files, $this->listDir($filename));
                        }
                    }
                }
                closedir($handle);
            }
        }

        return $files;
    }

    /**
     * 获取文件扩展名
     * @param  String $file 文件路径或网址
     * @return String       文件扩展名
     */
    public function getSuffix($file)
    {
        // 如果不是文件，返回 false，如果是，返回扩展名
        return (is_file($file) || filter_var($file, FILTER_SANITIZE_URL)) ? pathinfo($file, PATHINFO_EXTENSION) : false;
    }
}
