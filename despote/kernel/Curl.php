<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * cURL 操作类，封装一些 cURL 的操作
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \Despote;
use \despote\base\Service;
use \Exception;

class Curl extends Service
{
    /////////////
    // 连接选项 //
    /////////////

    // 默认 cURL 会话选项
    private $options = [
        // 不取回返回的 head 信息
        CURLOPT_HEADER         => 0,
        // cURL 脚本的最长时间
        CURLOPT_TIMEOUT        => 30,
        // Accept-Encoding，用于解码返回信息
        CURLOPT_ENCODING       => '',
        // 允许的 IP 地址类型，设置为 IPv4，防止为了解析 IPv6 导致的长时间等待
        CURLOPT_IPRESOLVE      => 1,
        // 将 curl_exec() 返回值以字符串返回而不是直接输出
        CURLOPT_RETURNTRANSFER => true,
        // 关闭证书验证
        CURLOPT_SSL_VERIFYPEER => false,
        // 尝试连接等待时间
        CURLOPT_CONNECTTIMEOUT => 10,
    ];
    // 每个 cURL 自定义的设置，默认使用 $options 设置方案
    private $setting;

    /////////////
    // 传输选项 //
    /////////////

    // 传输携带的 post 数据
    private $post = [];
    // cURL 执行后网页响应内容
    private $data;
    // cURL 传输信息
    private $info;
    // cURL 错误代码
    private $errno;
    // cURL 错误信息
    private $errmsg;

    public function init()
    {
        $this->setting = $this->options;
    }

    public function __GET($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }

    /**
     * 防止单例并发下出现问题，可使用此方法创建另一个实例再继续操作
     * @return Object Curl 对象
     */
    public function create()
    {
        return new static;
    }

    /**
     * 设置 cURL 连接选项
     * @param String $item  cURL 设置选项名
     * @param String $value cURL 设置值
     */
    public function setOpt($item, $value = null)
    {
        if (is_array($item)) {
            // 维护 setting 数组定义，键名必须为 curl_setopt 中对应的常量或者对应的整数
            foreach ($item as $k => $v) {
                if (is_string($k)) {
                    // 取得对应的常量值
                    $this->setting[constant(strtoupper($k))] = $v;
                } else if (is_numeric($k)) {
                    // 数字可以直接作为键名，不需要转换
                    $this->setting[$k] = $v;
                }
            }
        } else {
            if (is_string($item)) {
                // 取得对应的常量值
                $this->setting[constant(strtoupper($item))] = $value;
            } else if (is_numeric($item)) {
                // 数字可以直接作为键名，不需要转换
                $this->setting[$item] = $value;
            }
        }

        return $this;
    }

    /**
     * 使用 GET 发起请求
     * @param  String  $url  请求地址
     * @return Object        URL 非法会触发 E_USER_ERROR 错误，执行后返回 $this
     */
    public function get($url)
    {
        // 验证 URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            // 提交 cURL 请求地址并执行 cURL
            $this->setOpt(CURLOPT_URL, $url)->commit();
        } else {
            trigger_error('无效 URL', E_USER_ERROR);
        }

        return $this;
    }

    /**
     * 设置 POST 数据列表
     * @param String $item  POST 参数键名
     * @param String $value POST 参数值
     */
    public function setPost($item, $value = '')
    {
        if (is_array($item)) {
            $this->post = array_merge($this->post, $item);
        } else {
            $this->post[$item] = $value;
        }

        return $this;
    }

    /**
     * 使用 POST 发起请求
     * @param  String  $url  请求地址
     * @param  Boolean $back 是否返回请求地址返回数据
     * @return Object        URL 非法会触发 E_USER_ERROR 错误，执行后返回 $this
     */
    public function post($url, $data = [], $back = true)
    {
        // 验证 URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            empty($data) || $this->setPost($data);

            // 提交 cURL 请求地址并执行 cURL
            $this->setOpt(CURLOPT_URL, $url)->commit();
        } else {
            trigger_error('无效 URL', E_USER_ERROR);
        }

        return $this;
    }

    /**
     * 上传文件
     * @param String $field 文件的字段名
     * @param String $file  需要上传的文件路径
     * @param String $type  需要上传的文件 MIME 类型
     * @param String $name  上传后的文件名
     */
    public function setFile($field, $file, $type, $name)
    {
        // 过滤带有路径的名字
        $name = basename($name);

        // 判断是否支持 CURLFile，不支持使用旧方法提交，支持使用 CURLFile 对象
        if (class_exists('CURLFile')) {
            // 设置上传参数
            $this->setOpt('CURLOPT_SAFE_UPLOAD', true);
            $field_data = new \CURLFile($file, $type, $name);
        } else {
            $field_data = "@{$file};type={$type};filename={$name}";
        }

        return $this->setPost($field, $field_data);
    }

    /**
     * 保存文件，在 get / post 方法之后调用，将返回的数据保存
     * @param  String $file 保存的文件名（带路径）
     * @return Object       Curl 对象
     */
    public function save($file)
    {
        if (isset($this->data)) {
            // 判断之前请求是否出错
            if ($this->errno) {
                trigger_error($this->errmsg, E_USER_ERROR);
            }
            // 判断文件目录是否存在
            $path = dirname($file);
            if (!is_dir($path)) {
                // 尝试创建目录
                try {
                    // 如果已创建直接获取
                    $obj = Despote::file();
                } catch (Exception $e) {
                    // 如果未创建手动创建
                    $obj = new File();
                }
                $obj->cerate($path, true) || trigger_error('文件路径不存在', E_USER_ERROR);
            }

            file_put_contents($file, $this->data, LOCK_EX);
        } else {
            trigger_error('未发起任何请求，没有可以保存的内容', E_USER_ERROR);
        }

        return $this;
    }

    /**
     * 提交 cURL 请求
     */
    private function commit()
    {
        // 初始化 cURL
        $ch = curl_init();
        // 设置 cURL 选项
        curl_setopt_array($ch, $this->setting);

        // 不为空则加上传输 POST 数据选项
        if (!empty($this->post)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->post));
        }

        // 获取 cURL 执行结果
        $this->data   = curl_exec($ch);
        $this->info   = curl_getinfo($ch);
        $this->errno  = curl_errno($ch);
        $this->errmsg = $this->errno ? curl_error($ch) : '';

        // 释放 cURL 句柄
        curl_close($ch);
    }
}
