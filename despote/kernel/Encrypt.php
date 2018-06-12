<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 加密解密相关工具类
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \despote\base\Service;

class Encrypt extends Service
{
    /**
     * 使用 key 加密字符串
     * @param  String  $data   密码
     * @param  String  $key    key
     * @param  Integer $expire 过期时间，如果为 0 永不过期
     * @return String          密钥
     */
    public function encode($data, $key, $expire = 0)
    {
        // 键名不需要解密，所以可以使用不可逆算法加密
        $key = md5($key);
        // 数据需要解密，所以需要使用可逆算法，为了速度，采用 base64 加密
        $data = base64_encode($data);
        // 加密后的键名长度
        $keyLen = strlen($key);
        // 加密后的数据长度
        $dataLen = strlen($data);
        // 加密时的 key 字符串索引
        $keyIndex = 0;
        // 为了加密而拼凑出来的 key
        $tempKey = '';

        // 根据加密后的键名循环获取，获得和数据一样长的 key 串
        for ($i = 0; $i < $dataLen; ++$i, ++$keyIndex) {
            // 如果已经遍历完 key 了，就重新再遍历一次
            if ($keyIndex == $keyLen) {
                $keyIndex = 0;
            }
            // 获取指定索引的 key 对应的字符
            $tempKey .= substr($key, $keyIndex, 1);
        }

        // 在密钥开头标明过期时间，长度是 10 位，和时间戳的长度一致
        $str = sprintf('%010d', $expire ? $expire + time() : 0);
        // 生成密钥
        for ($i = 0; $i < $dataLen; $i++) {
            // 使用 data 中该位置的字符的 ASCII 码和 key 中该位置的字符的 ASCII 码的和，作为新的密钥
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($tempKey, $i, 1))) % 256);
        }
        // 再次进行 base64 加密
        $str = base64_encode($str);
        // 替换敏感字符
        $str = str_replace(['=', '+', '/'], ['O0O0O', 'o000o', 'oo00o'], $str);

        // 返回密钥
        return $str;
    }

    /**
     * 解密密钥
     * @param  String $data 密钥
     * @param  String $key  key
     * @return String       密码
     */
    public function decode($data, $key)
    {
        // 敏感字符替换处理
        $data = str_replace(['O0O0O', 'o000o', 'oo00o'], ['=', '+', '/'], $data);
        // 加密 key，用于还原密钥
        $key = md5($key);
        // 初次处理过的密钥
        $data = base64_decode($data);
        // 加密时的 key 字符串索引
        $keyIndex = 0;
        // 获取过期时间
        $expire = substr($data, 0, 10);
        // 获取真实密钥
        $data = substr($data, 10);
        // 如果密钥过期，直接返回 null
        if ($expire > 0 && $expire < time()) {
            return null;
        }
        // 加密后的数据长度
        $dataLen = strlen($data);
        // 加密后的 key 长度
        $keyLen = strlen($key);
        // 初始化临时 key
        $tempKey = '';
        // 初始化密码
        $str = '';

        // 获取临时 key
        for ($i = 0; $i < $dataLen; $i++) {
            // 和加密一样，循环获取 key 字符串的值
            if ($keyIndex == $keyLen) {
                $keyIndex = 0;
            }

            // 拼凑临时 key
            $tempKey .= substr($key, $keyIndex, 1);
            $keyIndex++;
        }

        // 解密数据
        for ($i = 0; $i < $dataLen; $i++) {
            // 确保运算后的 ASCII 码比 0 大并且有意义
            if (ord(substr($data, $i, 1)) < ord(substr($tempKey, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($tempKey, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($tempKey, $i, 1)));
            }
        }

        // 返回密码
        return base64_decode($str);
    }

    /**
     * 获取随机字符串
     *
     * @param   Integer  $count  需要生成的随机字符串长度
     * @param   String   $lib    随机字符串的字符库，默认为小写字母 + 数字
     * @return  String           随机字符串
     */
    public function getRandStr($count, $lib = '0123456789qwertyuiopasdfghjklzxcvbnm')
    {
        $arr    = [];
        $libLen = strlen($lib);

        for ($j = 0; $j < $count; $j++) {
            array_push($arr, $lib[rand(0, $libLen - 1)]);
        }

        $rand = implode('', $arr);

        return $rand;
    }
}
