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

class Curl
{
    /////////////
    // 连接选项 //
    /////////////

    // 默认 cURL 会话选项
    const OPTIONS = [
        // 不取回返回的 head 信息
        'CURLOPT_HEADER'         => 0,
        // cURL 脚本的最长时间
        'CURLOPT_TIMEOUT'        => 30,
        // Accept-Encoding，用于解码返回信息
        'CURLOPT_ENCODING'       => '',
        // 允许的 IP 地址类型，设置为 IPv4，防止为了解析 IPv6 导致的长时间等待
        'CURLOPT_IPRESOLVE'      => 1,
        // 将 curl_exec() 返回值以字符串返回而不是直接输出
        'CURLOPT_RETURNTRANSFER' => true,
        // 关闭证书验证
        'CURLOPT_SSL_VERIFYPEER' => false,
        // 尝试连接等待时间
        'CURLOPT_CONNECTTIMEOUT' => 10,
    ];
    // 每个 cURL 自定义的设置，默认使用 OPTIONS 设置方案
    private $setting = OPTIONS;

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

    /**
     * 设置 cURL 连接选项
     * @param String $item  cURL 设置选项名
     * @param String $value cURL 设置值
     */
    public function setOpt($item, $value = null)
    {
        if (is_array($item)) {
            $this->setting = array_merge($this->setting, $item);
        } else {
            $this->setting[$item] = $value;
        }

        return $this;
    }

    /**
     * 使用 GET 发起请求
     * @param  String  $url  请求地址
     * @param  Boolean $back 是否返回请求地址返回数据
     * @return Mixed         失败返回 false，成功返回 true，$back 为 true 时则返回请求地址返回数据
     */
    public function get($url, $back = true)
    {
        // 验证 URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            // 提交 cURL 请求地址并执行 cURL
            $this->setOpt('CURLOPT_URL', $url)->commit();

            // 判断执行是否成功，成功再根据参数返回数据
            $result = $this->errno ? false : ($back ? $this->data : true);
        } else {
            $result = false;
        }

        return $result;
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

    public function post($url, $data = [], $back = true)
    {
        // 验证 URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            empty($data) || $this->setPost($data);

            // 提交 cURL 请求地址并执行 cURL
            $this->setOpt('CURLOPT_URL', $url)->commit();

            // 判断执行是否成功，成功再根据参数返回数据
            $result = $this->errno ? false : ($back ? $this->data : true);
        } else {
            $result = false;
        }

        return $result;
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

        // 重置默认选项，便于多次操作
        $this->post    = [];
        $this->setting = OPTIONS;
    }
}
