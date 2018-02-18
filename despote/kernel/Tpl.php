<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \Despote;
use \despote\base\Service;

class Tpl extends Service
{
    /////////////
    // 模板配置 //
    ////////////

    // 模板所在模块名
    public $module;
    // // 模板内部变量
    // private $vars = [];
    // 模板路径，默认为视图路径
    private $tplDir;
    // 缓存路径，默认为视图路径下的 cache 文件夹
    private $cacheDir;
    // 模板引擎左定界符，默认为 <{
    private $tplBegin;
    // 模板引擎右定界符，默认为 }>
    private $tplEnd;
    // 模板文件后缀，默认为 tpl
    private $suffix;
    // 不使用缓存，每次都需要编译，默认为 false
    private $noCache;

    public function init()
    {
        // 校验模板引擎配置文件可读性
        if (!is_readable(PATH_CONF . 'tpl.php')) {
            $this->showError('模板引擎配置文件不存在');
            return;
        }

        // 加载模板引擎配置
        $conf = require PATH_CONF . 'tpl.php';
        $path = PATH_APP . $this->module . DS . 'tpl' . DS;

        // 使用配置文件初始化属性
        $this->tplDir   = isset($conf['tplDir']) ? $conf['tplDir'] : $path;
        $this->cacheDir = isset($conf['cacheDir']) ? $conf['cacheDir'] : $path . 'cache' . DS;
        $this->tplBegin = isset($conf['tplBegin']) ? $conf['tplBegin'] : '<{';
        $this->tplEnd   = isset($conf['tplEnd']) ? $conf['tplEnd'] : '}>';
        $this->suffix   = isset($conf['suffix']) ? $conf['suffix'] : 'tpl';
        $this->noCache  = isset($conf['noCache']) ? $conf['noCache'] : false;
    }

    /**
     * 模板引擎对外接口，用于加载模板
     * @param  String $filename 模板文件名，不需要加后缀
     */
    public function display($filename)
    {
        $tpl   = $this->getTpl($filename);
        $cache = $this->getCache($filename);
        $this->cache($tpl, $cache);

        require $cache;
    }

    // public function assign($key, $value)
    // {
    //     $this->vars[$key] = $value;
    // }

    /**
     * 获取模板绝对路径
     * @param  String $filename 模板文件名
     * @return String           模板文件绝对路径
     */
    private function getTpl($filename)
    {
        return $this->tplDir . DS . $filename . '.' . $this->suffix;
    }

    /**
     * 获取缓存文件绝对路径
     * @param  String $filename 缓存文件名
     * @return String           缓存文件绝对路径
     */
    public function getCache($filename)
    {
        return $this->cacheDir . DS . $filename . '.php';
    }

    /**
     * 是否已经有缓存了
     * @param  String  $tpl   模板文件的绝对路径
     * @param  String  $cache 缓存文件的绝对地址
     * @return boolean        缓存存在且有效返回 true，否则返回 false
     */
    private function isCache($tpl, $cache)
    {
        if (!is_readable($tpl)) {
            $this->showError('模板文件 ' . $tpl . ' 不可读');
            return false;
        }
        if ($this->noCache or !is_file($cache)) {
            return false;
        }
        if (filemtime($tpl) > filemtime($cache)) {
            return false;
        }

        return true;
    }

    /**
     * 开始进行模板缓存
     * @param  String $tpl   模板文件的绝对路径
     * @param  String $cache 缓存文件的绝对路径
     */
    private function cache($tpl, $cache)
    {
        if (!$this->isCache($tpl, $cache)) {
            $this->writeCache($cache, $this->compile($tpl));
        }
    }

    /**
     * 写入缓存
     * @param  String $cache   缓存文件路径
     * @param  String $content 缓存文件内容
     */
    private function writeCache($cache, $content)
    {
        if (!Despote::file()->create($cache) || !is_readable($cache)) {
            $this->showError('生成缓存文件失败，请检查 PHP 权限');
        }

        // 访问校验
        $content = "<?php defined('DESPOTE') || die('Forbidden access'); ?>\r\n" . $content;
        file_put_contents($cache, $content);
    }

    /**
     * 字符串转义，使之变为普通文本，特殊字符不作为正则表达式
     * @param  String $regular 字符串
     * @return String          转义后的字符串
     */
    private function quote($regular)
    {
        return preg_quote($regular, '/');
    }

    /**
     * 编译模板文件
     * @param  String $tpl 模板文件的绝对路径
     * @return String      编译后的模板文件内容
     */
    private function compile($tpl)
    {
        // 获取模板
        if (!is_readable($tpl)) {
            $this->showError('模板文件不可读');
            return;
        }
        $content = file_get_contents($tpl);

        // 转义定界符，用于拼接正则表达式
        $begin = $this->quote($this->tplBegin);
        $end   = $this->quote($this->tplEnd);

        // 匹配文件加载
        if (strpos($content, $this->tplBegin . 'inc') !== false) {
            // 匹配文件加载语句的正则表达式
            $regular = '/' . $begin . 'inc\s+file\s*=\s*["](.+?)["]' . $end . '/i';
            if (preg_match_all($regular, $content, $results)) {
                $files = array_unique($results[1]);
                // 循环处理文件包含
                foreach ($files as $file) {
                    // 待替换的模板
                    $incTpl = $this->tplBegin . 'inc file="' . $file . '"' . $this->tplEnd;
                    $tpl    = $this->getTpl($file);
                    if (is_file($tpl)) {
                        // 假设引入的是另一个模板，则获取对应的缓存文件名
                        $cache = $this->getCache($file);
                    } else {
                        $cache = null;
                    }

                    if ($cache) {
                        // 缓存模板，并加载
                        $this->cache($tpl, $cache);
                        $incCache = "<?php require('{$cache}'); ?>";
                    } else {
                        // 如果 file 不是模板文件，是单独的文件，加载该文件，否则结束本次循环
                        if (is_file($file)) {
                            $incCache = "<?php require('{$file}'); ?>";
                        } else {
                            continue;
                        }
                    }

                    // 替换模板
                    $content = str_ireplace($incTpl, $incCache, $content);
                }
            }
        }

        // else 替换
        $elseTpl = $this->tplBegin . 'else' . $this->tplEnd;
        if (strpos($content, $elseTpl) !== false) {
            $elseCache = "<?php }else{ ?>";
            $content   = str_ireplace($elseTpl, $elseCache, $content);
        }

        // 结束符替换，结束符是 /if、/for、/foreach、/while
        $endTpl = '/' . $begin . '\/(if|for|foreach|while)' . $end . '/i';
        if (preg_match_all($endTpl, $content, $results)) {
            $results[0] = array_unique($results[0]);
            foreach ($results[0] as $endSignTpl) {
                $content = str_replace($endSignTpl, '<?php } ?>', $content);
            }
        }

        // if 语句替换
        if (strpos($content, $this->tplBegin . 'if') !== false) {
            $regular = '/' . $begin . 'if(.*)' . $end . '/isU';
            if (preg_match_all($regular, $content, $results)) {
                // 循环匹配所有的 if
                foreach ($results[1] as $index => $condition) {
                    // 解析变量
                    $condCache = $this->parseVars($condition);
                    $content   = str_replace($results[0][$index], '<?php if(' . $condCache . '){ ?>', $content);
                }
            }
        }

        // elseif 语句替换
        if (strpos($content, $this->tplBegin . 'elseif') !== false) {
            $regular = '/' . $begin . 'elseif(.*)' . $end . '/isU';
            if (preg_match_all($regular, $content, $results)) {
                // 循环匹配所有的 elseif
                foreach ($results[1] as $indx => $condition) {
                    // 解析变量
                    $condCache = $this->parseVars($condition);
                    $content   = str_replace($results[0][$indx], '<?php }elseif(' . $condCache . '){ ?>', $content);
                }
            }
        }

        // foreach 语句替换
        if (strpos($content, $this->tplBegin . 'foreach') !== false) {
            $regular = '/' . $begin . 'foreach(.*)' . $end . '/isU';
            if (preg_match_all($regular, $content, $results)) {
                foreach ($results[1] as $key => $condition) {
                    // 判断是否省略了 as 语句，是的话自动补充为 as $key=>$value
                    if (strpos($condition, ' as') === false) {
                        $condition .= ' as $key=>$value';
                    }

                    // 解析变量
                    $condCache = $this->parseVars($condition);
                    $content   = str_replace($results[0][$key], '<?php foreach(' . $condCache . '){ ?>', $content);
                }
            }
        }

        // for 语句解析
        if (strpos($content, $this->tplBegin . 'for') !== false) {
            $regular = '/' . $begin . 'for (.*)' . $end . '/isU';
            if (preg_match_all($regular, $content, $results)) {
                foreach ($results[1] as $index => $condition) {
                    $condCache = $this->parseVars($condition);
                    $content   = str_replace($results[0][$index], '<?php for(' . $condCache . '){ ?>', $content);
                }
            }
        }

        // while 语句解析
        if (strpos($content, $this->tplBegin . 'while') !== false) {
            $regular = '/' . $begin . 'while (.*)' . $end . '/isU';
            if (preg_match_all($regular, $content, $results)) {
                foreach ($results[1] as $index => $condition) {
                    $condCache = $this->parseVars($condition);
                    $content   = str_replace($results[0][$index], '<?php while(' . $condCache . '){ ?>', $content);
                }
            }
        }

        // 解析变量定义
        $regular = '/' . $begin . '((\$[\w\.\[\]\$]+)=\s*([\'"].+?[\'"]|.+?))' . $end . '/';
        if (preg_match_all($regular, $content, $results)) {
            foreach ($results[0] as $index => $varTpl) {
                $varCache = '<?php ' . $this->parseVars($results[1][$index]) . '; ?>';
                $content  = str_replace($varTpl, $varCache, $content);
            }
        }

        // 解析不输出的语句
        $regular = '/' . $begin . '\!(.*)' . $end . '/isU';
        if (preg_match_all($regular, $content, $results)) {
            foreach ($results[1] as $index => $statement) {
                $value   = $this->parseVars($statement);
                $content = str_replace($results[0][$index], '<?php ' . $value . ';?>', $content);
            }
        }

        // 解析输出语句
        $regular = '/' . $begin . '(.*)' . $end . '/U';
        if (preg_match_all($regular, $content, $results)) {
            foreach ($results[1] as $index => $statement) {
                $value   = $this->parseVars($statement);
                $content = str_replace($results[0][$index], '<?= ' . $value . ';?>', $content);
            }
        }

        return $content;
    }

    /**
     * 变量解析
     * @param  String $content 语句
     * @return String          解析完变量的语句
     */
    private function parseVars($content)
    {
        $superVars = [
            '$post'    => '$_POST',
            '$get'     => '$_GET',
            '$cookie'  => '$_COOKIE',
            '$session' => '$_SESSION',
            '$files'   => '$_FILES',
            '$server'  => '$_SERVER',
            '$this'    => '$this',
        ];
        if (preg_match_all('/\$(\w+)/', $content, $results)) {
            foreach ($results[0] as $var) {
                $varCache = array_key_exists($var, $superVars) ? $superVars[$var] : $var;
                $content  = preg_replace('/' . $this->quote($var) . '/', $varCache, $content, 1);
            }
        }

        return $content;
    }

    /**
     * 输出错误信息，具体是显示还是写入日志看 ErrCatch 设置
     * @param  String $msg 错误信息
     * @throws        模板处理过程出现的错误
     */
    private function showError($msg)
    {
        throw new \Exception($msg, 500);
    }
}
