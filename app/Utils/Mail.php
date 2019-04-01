<?php
 
namespace App\Utils;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Mail{

    public function smtpMail(){
        $mail = new PHPMailer(true);

        //使用STMP服务
        $mail->isSMTP();

        //这里使用我们第二步设置的stmp服务地址
        $mail->Host = "smtp.exmail.qq.com";

        //设置是否进行权限校验
        $mail->SMTPAuth = true;

        //第二步中登录网易邮箱的账号
        $mail->Username = "sendinfo@galaxy-immi.com";

        //客户端授权密码，注意不是登录密码
        $mail->Password = "Galaxy0308";

        //使用ssl协议
        $mail->SMTPSecure = 'ssl';
        
        //端口设置
        $mail->Port = 465;

        //字符集设置，防止中文乱码
        $mail->CharSet= "utf-8";

        //设置邮箱的来源，邮箱与$mail->Username一致，名称随意
        $mail->setFrom("sendinfo@galaxy-immi.com", "");

        //设置回复地址，一般与来源保持一直
        $mail->addReplyTo("sendinfo@galaxy-immi.com", "");

        $mail->isHTML(true);

        return $mail;
    }
 
    //发送邮件
    public function sendEmail($email,$headline,$msg){

        //初始化配置
        $mail = $this->smtpMail();
        //设置收件的邮箱地址
        $mail->addAddress($email);
            
        //标题
        $mail->Subject = $headline;
        //正文
        $mail->Body = $msg;
        $status  = $mail->send();

        if(!empty($mail->ErrorInfo)){
            $status = $mail->ErrorInfo;
        }
        return $status;
    }
}
 
