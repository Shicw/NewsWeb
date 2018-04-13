<?php
use think\Config;
use think\Db;
use think\Url;
use think\Route;
use think\Loader;
use think\Request;


// 应用公共文件

/**
 * 验证码检查，验证完后销毁验证码
 * @param string $value
 * @param string $id
 * @return bool
 */
function cmf_captcha_check($value, $id = "")
{
    $captcha = new \think\captcha\Captcha();
    return $captcha->check($value, $id);
}

/**
 * 发送邮件，加载第三方类库PHPMailer
 * @param string $email 收件人邮箱
 * @param string $message 邮件内容
 * @return array<br>
 */
function sendEmail($email, $message)
{
    //读取config表中的配置项
    $config = Db::name('config')->field(['key','value'])->where('module','mailer')->select();
    Vendor('phpmailer.phpmailer');
    $mail        = new \phpmailer\PHPMailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();
    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet = 'UTF-8';
    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($email);
    // 设置邮件正文
    $mail->Body = $config[0]['value'].$message;
    // 设置邮件头的From字段。
    $mail->From = $config[6]['value'];
    // 设置发件人名字
    $mail->FromName = $config[1]['value'];
    // 设置邮件标题
    $mail->Subject = $config[5]['value'];
    // 设置SMTP服务器。
    $mail->Host = $config[2]['value'];
    // 设置SMTPSecure。
    $mail->SMTPSecure = $config[4]['value'];
    // 设置SMTP服务器端口。
    $mail->Port = $config[3]['value'];
    // 设置为"需要验证"
    $mail->SMTPAuth    = true;
    $mail->SMTPAutoTLS = false;
    $mail->Timeout     = 10;
    // 设置用户名和密码。
    $mail->Username = $config[6]['value'];
    $mail->Password = $config[7]['value'];
    // 发送邮件。
    if (!$mail->Send()) {
        $mailError = $mail->ErrorInfo;
        return ["status" => 0, "message" => $mailError];
    } else {
        return ["status" => 1, "message" => "success"];
    }
}
