<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 请求处理类
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \despote\base\Service;

class Request extends Service
{
    ////////////////
    // 获取环境信息 //
    ////////////////

    /**
     * 获取用户请求的 URI 地址
     */
    public function getUri()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }

    /**
     * 获取服务器端口
     */
    public function getPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? (integer) $_SERVER['SERVER_PORT'] : 80;
    }

    /**
     * 获取服务器 IP
     */
    public function getIP()
    {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
    }

    /**
     * 获取 HOST
     * @param boolean $schema 是否显示协议头 http(s)://
     */
    public function getHost($schema = false)
    {
        // 判断使用的协议类型
        $isHttps = $this->isHttps();
        $host    = $schema ? ($isHttps ? 'https://' : 'http://') : '';

        // 获取服务器域名
        if (isset($_SERVER['HTTP_HOST'])) {
            $host .= $_SERVER['HTTP_HOST'];
        } else if (isset($_SERVER['SERVER_NAME'])) {
            $host .= $_SERVER['SERVER_NAME'];
            $port = $this->getPort();

            // 如果不是 HTTP 或 HTTPS 的默认端口，则加上端口显示
            if ((!$isHttps && $port !== 80) || ($isHttps && $port !== 443)) {
                $host .= ':' . $port;
            }
        }

        return $host;
    }

    /**
     * 获取请求字符串，即 ? 后面的内容
     */
    public function getQuery()
    {
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    }

    /**
     * 获取 UserAgent
     */
    public function getUA()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    /**
     * 返回客户端 IP，如果使用代理将会返回最后一个代理服务器的 IP 地址
     */
    public function getUserIP()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * 返回客户端 IP，获取使用代理时的真实 IP 地址，如果没有使用代理，该值为 null
     */
    public function getUserRealIP()
    {
        return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
    }

    /**
     * 返回客户端端口
     * @return string|null
     */
    public function getUserPort()
    {
        return isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : null;
    }

    ////////////////
    // 请求方式判断 //
    ////////////////

    /**
     * 返回请求方法:GET/POST/HEAD/PUT/PATCH/DELETE/OPTIONS/TRACE
     * @return string
     */
    public function getMethod()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
    }

    /**
     * 是否为GET请求
     * @return boolean
     */
    public function isGet()
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * 是否为POST请求
     * @return boolean
     */
    public function isPost()
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * 是否为AJAX请求
     * @return boolean
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * 是否为PJAX请求
     * @return boolean
     */
    public function isPjax()
    {
        return $this->isAjax() && isset($_SERVER['HTTP_X_PJAX']);
    }

    /**
     * 是否为HTTPS
     * @return boolean
     */
    public function isHttps()
    {
        return isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0;
    }

    /**
     * 判断是否在CLI模式下运行
     * @return boolean
     */
    public function isCli()
    {
        return php_sapi_name() === 'cli';
    }

    /////////////
    // 数据获取 //
    /////////////

    private $data;

    /**
     * 初始化数据，只初始化一次
     * @param  Array  $get 获取到的 GET 数组
     */
    public function load($get)
    {
        if ($this->data === null) {
            $this->data = [
                'get'  => $get,
                'post' => $_POST,
            ];
        }
    }

    /**
     * 获取 GET 数组的数据
     * @param  String $name       GET 数组中的键名，留空则返回整个 GET 数组
     * @param  Mixed  $defaultVal 若获取的键名不存在时的默认值，留空则默认为 null
     * @return Mixed              返回 GET 数组或键名对应的键值
     */
    public function get($name = null, $defaultVal = null)
    {
        if ($name === null) {
            return $this->data['get'];
        } else {
            return isset($this->data['get'][$name]) ? $this->data['get'][$name] : $defaultVal;
        }
    }

    /**
     * 获取 GET 数组的数据
     * @param  String $name       GET 数组中的键名，留空则返回整个 GET 数组
     * @param  Mixed  $defaultVal 若获取的键名不存在时的默认值，留空则默认为 null
     * @return Mixed              返回 GET 数组或键名对应的键值
     */
    public function post($name = null, $defaultVal = null)
    {
        if ($name === null) {
            return $this->data['post'];
        } else {
            return isset($this->data['post'][$name]) ? $this->data['post'][$name] : $defaultVal;
        }
    }

    /**
     * 返回请求的原始数据
     * @return boolean|string
     */
    public function getRawData()
    {
        return file_get_contents('php://input');
    }
}
