<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * Token 工具类
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \Despote;
use \despote\base\Service;
use \Utils;

class Token extends Service
{
    // 用于加密签名的密钥
    protected $secret;

    /**
     * 创建一个 Token
     * @param  String $opera    Token 的类型，如：login
     * @param  Mixed  $raw_data Token 内存放的数据，一般为数组，也可以是对象或者是字符串
     * @return String           Token
     */
    public function create($opera, $raw_data)
    {
        $raw_header = [
            'opera' => $opera,
        ];

        $header = base64_encode(json_encode($raw_header, JSON_UNESCAPED_UNICODE));
        $data   = base64_encode(json_encode($raw_data, JSON_UNESCAPED_UNICODE));

        $raw_sign = $header . ',' . $data;
        $sign     = base64_encode(Utils::encrypt($raw_sign, $this->secret));

        return $raw_sign . ',' . $sign;
    }

    /**
     * 获取 Token 中的操作类型，如果是 Token 非法会返回 false
     * @param  String $token 用 create() 方法生成的 Token 字符串
     * @return String        Token 中的类型
     */
    public function verify($token)
    {
        if (empty($token)) {
            return false;
        }
        list($header, $data, $sign) = explode(',', $token);

        if (empty($header) || empty($data) || empty($sign)) {
            return false;
        }

        $raw_header = json_decode(base64_decode($header), true);
        $raw_data   = json_decode(base64_decode($data), true);
        $raw_sign   = $header . ',' . $data;

        $verify_sign = base64_encode(Utils::encrypt($raw_sign, $this->secret));

        return $verify_sign == $sign ? $raw_header['opera'] : false;
    }

    /**
     * 获取 token 中的数据段
     * @param  String  $token     用 create() 方法生成的 Token 字符串
     * @param  Boolean $getMethon 默认会 true，为 true 强制以数组返回，为 false 则以对象返回
     * @return Mixed              Token 中的数据段
     */
    public function getData($token, $getMethon = true)
    {
        if (empty($token)) {
            return false;
        }
        list($header, $data, $sign) = explode(',', $token);

        if (empty($header) || empty($data) || empty($sign)) {
            return false;
        }

        $raw_header = json_decode(base64_decode($header), true);
        $raw_data   = json_decode(base64_decode($data), true);
        $raw_sign   = $header . ',' . $data;

        $verify_sign = base64_encode(Utils::encrypt($raw_sign, $this->secret));

        return $verify_sign == $sign ? $raw_data : false;
    }
}
