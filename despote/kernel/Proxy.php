<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 反向代理类，便于服务器端渲染如 Vue、React 等使用虚拟 DOM 技术的页面
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \Despote;
use \despote\base\Service;
use \Exception;

class Proxy extends Service
{
    /////////////
    // 杂七杂八 //
    /////////////

    // 请求处理对象
    private $http;
    // 版本信息
    private $version;

    //////////////////
    // 代理服务器设置 //
    //////////////////

    // 需要代理的主机号
    protected $host;
    // 需要代理的 IP 地址
    protected $ip;
    // 需要代理的端口
    protected $port;
    // 子目录绑定
    protected $subDir;
    // 网页缓存过期时间
    protected $expire = 72000;

    ////////////////
    // 请求相关信息 //
    ////////////////

    // 请求的地址
    private $path;
    // 请求方法
    private $requestMethod;
    // 网页最后修改时间
    private $lastModified;

    ////////////////
    // 用户相关信息 //
    ////////////////

    // 用户发送的 POST 数据
    private $postData;
    // 用户 IP
    private $userIP;
    // 用户 UA
    private $userAgent;
    // Cookie
    private $cookie;

    //////////////////
    // 服务器返回参数 //
    //////////////////

    // 本地是否存在有效缓存
    private $isCache = false;
    // 返回的请求头
    private $returnHeader;
    // 返回的网页正文
    private $content;
    // 返回的文档类型
    private $docType;
    // 服务器响应码
    private $httpCode;

    protected function init()
    {
        $this->http    = Despote::request();
        $this->version = 'Despote Framework v3.0';
    }

    public function conn()
    {
        // 如果有缓存就直接返回缓存数据，提高响应速度
        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            // 缓存不为空，依旧有效
            $this->lastModified = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            $this->isCache      = true;
            return;
        }

        // 开始预处理
        $this->setInfo();
        // 初始化 cURL
        $ch = curl_init();

        // 如果是 POST 请求就进入 POST 处理流程
        ($this->requestMethod == "POST") && $this->getPost($ch);

        // 设置 URL 地址
        $this->setUrl($ch);

        // 设置请求头
        $this->setHeader($ch);

        // 设置其他相关参数
        $this->setOtherProp($ch);

        // 获取返回结果
        $result = curl_exec($ch);
        // 获取返回信息
        $info = curl_getinfo($ch);
        // 释放句柄
        curl_close($ch);

        // 处理返回结果
        $this->prepare($result, $info);
    }

    public function display()
    {
        // 获取现行时间和缓存过期时间
        $now    = date("D, d M Y H:i:s");
        $expire = date("D, d M Y H:i:s", (time() + $this->expire));

        if ($this->isCache) {
            $this->useCache($now);
            return;
        }
        $this->loadPage($now, $expire);
    }

    /**
     * 设置环境信息
     */
    private function setInfo()
    {
        // 获取必要信息
        $this->userAgent     = $this->http->getUA();
        $this->requestMethod = $this->http->getMethod();

        // 获取用户的 cookie
        $cookie = '';
        foreach ($_COOKIE as $key => $value) {
            $cookie .= '&' . $key . '=' . $value;
        }
        $this->cookie = ltrim($cookie, '&');

        // 获取用户的 IP 地址
        $this->userIP = $this->http->getUserIP();
        // 处理代理 IP
        ($realIP = $this->http->getUserRealIP()) && ($this->userIP = $this->userIP . ', ' . $realIP);
    }

    /**
     * 处理 POST 请求
     * @param  Object &$ch cURL 句柄
     */
    private function getPost(&$ch)
    {
        // 设置 POST 模式
        curl_setopt($ch, CURLOPT_POST, 1);

        // POST 数据容器
        $postData = [];
        // 是否含有上传文件
        $isFileUpload = false;

        // 如果有上传文件，调用上传组件上传
        if (count($_FILES) > 0) {
            foreach ($_FILES as $field => $file) {
                Despote::upload()->up($field);
            }

            // 标记有上传文件
            $isFileUpload = true;
        }

        // 获取 POST 数组数据
        foreach ($this->http->post() as $key => $value) {
            // 根据 PHP cURL 的要求,传递 POST 数组时必须用一维数组,或者是类似于 GET 的传参的形式
            $postData[$key] = is_array($value) ? serialize($value) : $value;
        }

        // 如果不是文件上传，把 POST 数组的东西转换为类似 GET 传参的形式
        if (!$isFileUpload) {
            $postString = "";
            foreach ($postData as $key => $value) {
                $postString .= '&' . urlencode($key) . '=' . urlencode($value);
            }
            $postData = ltrim($postString, '&');
        }

        // 设置 POST 数据,并标明带有 POST 参数
        $this->postData = $postData;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    /**
     * 设置 URL 请求地址
     * @param Object &$ch cURL 句柄
     */
    private function setUrl(&$ch)
    {
        // 解析 URL
        $url = $this->convertURL(isset($this->ip) ? $this->ip : $this->host);
        // 设置请求的 URL 地址
        curl_setopt($ch, CURLOPT_URL, $url);
    }

    /**
     * URL 转化，输入 域名或 IP，转成 http://doname:port/index.php?a=1&b=2 的形式
     * @param  String $server 域名 / IP
     * @return String         转换后的 URL 地址
     */
    private function convertURL($server)
    {
        // 子目录绑定
        $this->path = $this->subDir . $this->http->getUri();

        $request = $this->convertServer($server) . $this->path;
        $query   = $this->http->getQuery();

        return empty($query) ? $request : $request . '?' . $query;
    }

    /**
     * 转换 域名/IP 地址
     * @param  String $server 域名 / IP
     * @return String         自动返回带协议头、端口的地址
     */
    private function convertServer($server)
    {
        $schema = $this->http->isHttps() ? 'https://' : 'http://';

        return $schema . $server . (isset($this->port) ? $this->port : '');
    }

    /**
     * 设置请求头
     * @param Object &$ch cURL 句柄
     */
    private function setHeader(&$ch)
    {
        // 请求头
        $reqHeader = [
            "X-Forwarded-For: " . $this->userIP,
            "User-Agent: " . $this->userAgent,
            "Host: " . $this->host,
        ];

        // 如果设置了头域，就跟着设置
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $reqHeader[] = "X-Requested-With: " . $_SERVER['HTTP_X_REQUESTED_WITH'];
        }

        // 设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $reqHeader);
    }

    /**
     * 设置 cURL 其他信息
     * @param Object &$ch cURL 句柄
     */
    private function setOtherProp(&$ch)
    {
        // 设置 cookie
        $this->cookie != "" || curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        // 禁止重定向
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        // 自动设置 referer 信息
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        // 输出 Header
        curl_setopt($ch, CURLOPT_HEADER, true);
        // 将 cURL 的结果作为字符串输出而不是直接显示
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    }

    /**
     * 对返回结果预处理
     * @param  Array   $result cURL 抓取后的网页响应
     * @param  Array   $info   cURL 抓取后的网页信息数组
     */
    private function prepare($result, $info)
    {
        // 获取文档类型
        $this->docType = $info['content_type'];
        // 获取 HTTP 状态码
        $this->httpCode = $info['http_code'];
        // 设置最后修改时间
        empty($info['last_modified']) || $this->lastModified = $info['last_modified'];

        // 获取返回 cURL 结果的 header 信息
        $this->returnHeader = substr($result, 0, $info['header_size']);
        // 获取返回的网页正文
        $content = substr($result, $info['header_size']);

        switch ($this->httpCode) {
            case '0':
            case '200':
                $this->content = $content;
                break;
            case '301':
            case '302':
                if (isset($info['redirect_url'])) {
                    // 重定向到当前服务器，继续代理，类似于递归
                    $redirect_url = str_replace($this->host, $this->http->getHost(), $info['redirect_url']);
                    header("Location: $redirect_url");
                    die;
                }
                break;
            case '404':
                // header("HTTP/1.1 404 Not Found");
                $this->error("HTTP/1.1 404 Not Found", 404);
                break;
            case '500':
                // header('HTTP/1.1 500 Internal Server Error');
                $this->error("HTTP/1.1 500 Internal Server Error", 500);
                break;
            default:
                $this->error("HTTP/1.1 " . $this->httpCode . " Internal Server Error", $this->httpCode);
                break;
        }
    }

    /**
     * 使用缓存加载网页
     * @param  String $now Header 中的现行时间
     */
    private function useCache($now)
    {
        header("HTTP/1.1 304 Not Modified");
        header("Date: Wed, $now GMT");
        header("Last-Modified: $this->lastModified");
        header("Server: $this->version");
    }

    /**
     * 加载网页
     * @param  String $now    Header 中的现行时间
     * @param  String $expire Header 中的过期时间
     */
    private function loadPage($now, $expire)
    {
        // 构造请求头
        header("HTTP/1.1 200 OK");
        // 现行时间
        header("Date: Wed, $now GMT");
        // 文档类型
        header("Content-Type: " . $this->docType);
        // 最后修改时间
        header("Last-Modified: $this->lastModified");
        // 最大缓存时间
        header("Cache-Control: max-age=$this->expire");
        // 过期时间
        header("Expires: $expire GMT");
        // 服务器信息
        header("Server: $this->version");
        // 设置 cookie
        preg_match("/Set-Cookie:[^\n]*/i", $this->returnHeader, $result);
        foreach ($result as $i => $value) {
            header($result[$i]);
        }

        // 加载网页内容
        echo $this->content;
    }

    /**
     * 抛出异常
     * @param  String  $msg  异常信息
     * @param  integer $code HTTP 响应码
     */
    private function error($msg, $code)
    {
        throw new Exception($msg, $code);
    }
}
