<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 文件上传类
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \despote\base\Service;

class Upload extends Service
{
    ////////////////
    // 上传文件设置 //
    ////////////////

    // 上传文件保存的路径
    protected $path;
    // 设置限制上传文件的类型
    protected $allowType = ['jpg', 'gif', 'png', 'jpeg'];
    // 限制文件上传大小（字节），默认为 10M
    protected $maxSize = 10485760;
    // 设置是否随机重命名文件， false 不随机
    protected $isRandonName = true;

    ////////////////
    // 上传文件属性 //
    ////////////////

    // 源文件名
    private $originName;
    // 临时文件名
    private $tmpFileName;
    // 文件后缀
    private $suffix;
    // 文件大小
    private $fileSize;
    // 新文件名
    private $newFileName;
    // 新文件全路径
    private $newFilePath;

    protected function init()
    {
        empty($this->path) && $this->path = PATH_RES . 'uploads' . DS;
        if (!is_dir($this->path) && !\Despote::file()->create($this->path, true)) {
            $this->error('创建目录失败，请手动创建目录');
        }
        is_writable($this->path) || $this->error('目录不可写，请先修改权限');
    }

    /**
     * 对外 API 接口
     * @param  String $field 字段名
     * @throws String        出错会输出相关错误信息，可以使用 try...catch... 结构获取错误信息
     */
    public function up($field)
    {
        // 获取文件信息并保存
        $name     = $_FILES[$field]['name'];
        $tmp_name = $_FILES[$field]['tmp_name'];
        $size     = $_FILES[$field]['size'];
        $error    = $_FILES[$field]['error'];

        // 是数组就循环处理
        if (is_array($name)) {
            for ($i = 0; $i < count($name); ++$i) {
                $this->uploadFile($name[$i], $tmp_name[$i], $size[$i], $error[$i]);
            }
        } else {
            $this->uploadFile($name, $tmp_name, $size, $error);
        }
    }

    public function getOriginName()
    {
        return $this->originName;
    }

    public function getNewFileName()
    {
        return $this->newFileName;
    }

    /**
     * 上传文件
     * @param  String  $name     源文件名字
     * @param  String  $tmp_name 临时文件名，绝对路径
     * @param  integer $size     文件大小，单位为字节
     * @param  integer $error    错误代码，0 表示无错误
     */
    private function uploadFile($name, $tmp_name, $size, $error)
    {
        // 保存文件信息，便于后续操作
        $this->setInfo($name, $tmp_name, $size, $error);
        // 校验文件大小和文件后缀
        $this->verify();
        // 获取新文件名
        $this->setNewFileName();
        // 保存文件
        $this->saveFile();
    }

    /**
     * 保存文件相关信息
     * @param  String  $name     源文件名字
     * @param  String  $tmp_name 临时文件吗，绝对路径
     * @param  integer $size     文件大小，单位为字节
     * @param  integer $error    错误代码，0 表示无错误
     * @return Boolean           如果错误代码不为零则返回 false，否则返回 true
     */
    private function setInfo($name = '', $tmp_name = '', $size = 0, $error = 0)
    {
        // 查错
        if ($error > 0) {
            $this->error('上传文件过程中出错：' . $error);
            return false;
        }

        // 设置源文件名
        $this->originName = $name;
        // 设置临时文件名
        $this->tmpFileName = $tmp_name;
        // 获取文件后缀
        $this->suffix = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        // 文件大小
        $this->fileSize = $size;

        return true;
    }

    /**
     * 文件尺寸、类型校验
     * @return Boolean 校验结果，成功返回 true，错误返回 false
     */
    private function verify()
    {
        if ($this->fileSize > $this->maxSize) {
            $this->error('文件尺寸超出允许范围');
            return false;
        }
        if (!in_array($this->suffix, $this->allowType)) {
            $this->error('该文件类型不被允许上传');
            return false;
        }
        return true;
    }

    /**
     * 获取新文件名字
     * @return String 新的文件名
     */
    private function setNewFileName()
    {
        if ($this->isRandonName) {
            $filename          = date('Ymd_His_') . sprintf("%'.03d", mt_rand(0, 999)) . '.' . $this->suffix;
            $this->newFileName = $filename;

            return is_file($filename) ? $this->setNewFileName() : $filename;
        }
        return $this->originName;
    }

    /**
     * 保存文件
     * @return String 保存文件结果，如果移动文件过程中出错了则返回 false，否则返回 true
     */
    private function saveFile()
    {
        // 获取文件全路径
        $newFilePath = rtrim($this->path, '/') . '/' . $this->newFileName;

        // 上传文件
        if (move_uploaded_file($this->tmpFileName, $newFilePath)) {
            $this->newFilePath[] = $newFilePath;
            return true;
        } else {
            $this->error('文件保存失败');
            return false;
        }
    }

    /**
     * 通过输出错误信息
     * @param  String $msg 错误信息
     */
    private function error($msg)
    {
        throw new \Exception($msg, 500);
    }
}
