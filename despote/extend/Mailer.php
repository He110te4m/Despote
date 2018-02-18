<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 简易封装的邮件发送类，基于 PHPMailer
 * @author      He110 (i@he110.top)
 * @namespace
 * @example
 */

namespace despote\extend;

use \despote\base\Service;
use \PHPMailer\PHPMailer\PHPMailer;

class Mailer extends Service
{
    // 单例 PHPMailer 类
    private $obj = null;

    // 使用的安全协议
    protected $SMTPSecure = 'ssl';
    // 邮件的字符编码
    protected $CharSet = 'UTF-8';
    // 是否进行安全认证
    protected $SMTPAuth = true;
    // 邮件服务器端口
    protected $Port = 465;
    // 邮件服务器地址
    protected $Host = 'smtp.exmail.qq.com';
    // 邮件服务器登陆的用户名
    protected $User = 'i@he110.top';
    // 邮件服务器登陆的密码
    protected $Pwd = 'test';
    // 发送人邮箱
    protected $Form = 'i@he110.top';
    // 发送人姓名
    protected $FormName = 'He110';
    // 回复邮箱
    protected $ReplyTo = 'i@he110.top';
    // 回复姓名
    protected $ReplyName = 'He110';

    public function init()
    {
        empty($this->obj) && $this->obj = new PHPMailer(false);
    }

    public function config($c)
    {
        // 使用的安全协议
        $this->SMTPSecure = isset($c['SMTPSecure']) ? $c['SMTPSecure'] : $this->SMTPSecure;
        // 邮件的字符编码
        $this->CharSet = isset($c['CharSet']) ? $c['CharSet'] : $this->CharSet;
        // 是否进行安全认证
        $this->SMTPAuth = isset($c['smtpauth']) && !empty($c['smtpauth']) ? true : $this->SMTPAuth;
        // 邮件服务器端口
        $this->Port = isset($c['Port']) ? $c['Port'] : $this->Port;
        // 邮件服务器地址
        $this->Host = isset($c['Host']) ? $c['Host'] : $this->Host;
        // 邮件服务器登陆的用户名
        $this->User = isset($c['User']) ? $c['User'] : $this->User;
        // 邮件服务器登陆的密码
        $this->Pwd = isset($c['Pwd']) ? $c['Pwd'] : $this->Pwd;
        // 发送人邮箱
        $this->Form = isset($c['formmail']) ? $c['formmail'] : $this->Form;
        // 发送人姓名
        $this->FormName = isset($c['FormName']) ? $c['FormName'] : $this->FormName;
        // 回复邮箱
        $this->ReplyTo = isset($c['ReplyTo']) ? $c['ReplyTo'] : $this->ReplyTo;
        // 回复姓名
        $this->ReplyName = isset($c['ReplyName']) ? $c['ReplyName'] : $this->ReplyName;
    }

    public function send($to, $title, $body, $file = null, $length = 80)
    {
        // 获取 PHPMailer 对象
        $mail = &$this->obj;

        // 设置安全协议
        if (!empty($this->SMTPSecure)) {
            $mail->SMTPSecure = $this->SMTPSecure;
        }
        // 设置编码
        $mail->CharSet = $this->CharSet;
        // 使用 base64 加密
        $mail->Encoding = 'base64';
        // 使用 SMTP 方式
        $mail->IsSMTP();
        // 设置端口
        $mail->Port = $this->Port;
        // 设置服务端地址
        $mail->Host = $this->Host;
        // 设置服务器认证配置
        $mail->SMTPAuth = $this->SMTPAuth;
        // 是否开启 Debug 模式
        $mail->SMTPDebug = \Utils::config('error_catch', false);
        // 设置发件人邮箱
        $mail->Username = $this->User;
        // 设置发件人密码
        $mail->Password = $this->Pwd;
        // 发送的邮箱
        $mail->From = $this->Form;
        // 发送人
        $mail->FromName = $this->FormName;
        // 设置回复地址
        $mail->AddReplyTo($this->ReplyTo, $this->ReplyName);
        // 添加接收人
        $mail->AddAddress($to);
        // 设置邮件标题
        $mail->Subject = $title;
        // 设置邮件内容
        $mail->Body = $body;
        // 设置邮件每行字符串长度
        $mail->WordWrap = $length;
        // 如果有附件就设置附件，如果是数组循环设置附件
        if (!empty($file)) {
            if (is_array($file)) {
                foreach ($file as $item) {
                    $mail->AddAttachment($item);
                }
            } else {
                $mail->AddAttachment($file);
            }
        }
        // 发送 HTML
        $mail->IsHTML(true);

        // 返回发送结果
        return $mail->Send();
    }
}
