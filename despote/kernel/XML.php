<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * XML 操作类，封装一些 XML 的操作
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \despote\base\Service;
use \Utils;
use \XMLReader;

class XML extends Service
{
    ///////////////
    // XML 与数组 //
    ///////////////

    /**
     * 数组或字符串转成 XML 格式
     * @param  Mixed  $data 数组/字符串
     * @param  String $root 根结点
     * @return String       生成的 XML
     */
    public function convertToXML($data, $root = 'xml')
    {
        if (is_array($data)) {
            $result = '';
            if (Utils::isAssoc($data)) {
                $result .= "<{$root}>";
                foreach ($data as $index => $item) {
                    $result .= $this->convertToXML($item, $index);
                }
                $result .= "</{$root}>";
            } else {
                // 传入一个索引数组，即多个 xml，故递归处理
                foreach ($data as $item) {
                    $result .= $this->convertToXML($item, $root);
                }
            }
        } else {
            // 递归最底层，拼接数据
            $result = "<{$root}>{$data}</{$root}>";
        }

        return $result;
    }

    /**
     * XML 转成数组，不能处理命名空间，只能处理严格格式 XML
     * @param  String $xml XML 字符串
     * @return Array       XML 对应的数组
     */
    public function convertToArray($xml)
    {
        // 关闭其他占用 XML 文件的资源，避免 XML 加载出错
        libxml_disable_entity_loader(false);
        // 加载 XML 为对象
        $xmlObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        // 使用 json 序列化/反序列将对象化转成数组
        $result = json_decode(json_encode($xmlObj), true);

        return $result;
    }

    /**
     * 获取 XML 中某个结点的数据，不会加载整个文档到内存中
     * @param  String $xml          XML 文件路径 或 XML 地址 或 XML 字符串
     * @param  String $node         需要获取数据的 XML 结点名
     * @param  String $defaultValue 获取不到结点数据时返回的内容
     * @return Mixed                成功返回键名为结点名的关联数组，包含该结点的数据
     */
    public function getVal($xml, $node, $defaultValue = null)
    {
        $result = $defaultValue;

        // 关闭其他占用 XML 文件的资源，避免加载 XML 出错
        libxml_disable_entity_loader(false);
        $reader = new XMLReader();

        // 根据传入参数选择加载方式
        if (is_file($xml) || filter_var($xml, FILTER_VALIDATE_URL)) {
            // 使用地址作为参数，需要使用 open 方法加载
            $reader->open($xml);
        } else if (is_string($xml)) {
            // 使用 XML 数据作为参数，需要使用 xml 方法加载
            $reader->xml($xml);
        } else {
            return $result;
        }

        // 循环读取 xml 结点
        while ($reader->read()) {
            // 匹配成功则返回
            if ($reader->name == $node) {
                $result = $this->convertToArray($reader->readOuterXml());
                break;
            }
        }

        return $result;
    }
}
