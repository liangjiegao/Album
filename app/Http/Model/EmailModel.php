<?php


namespace App\Http\Model;


use App\Http\Bean\EmailBean;
use App\Http\Config\EmailContentConf;
use App\Http\Config\ReturnInfoConf;
use App\Http\Model\Impl\IEmailModel;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailModel implements IEmailModel
{



    public function sendRegEmail($requestParams){
        $emailContentConf = new EmailContentConf();


        $tagEmail   = $requestParams['tag_email'];
        $code       = $requestParams['code'];

        $regTitle   = $emailContentConf -> getRegTitle();
        $regContent = $emailContentConf -> getRegContent($code);


        $emailBean = \App::make("EmailBean");

        $emailBean ->setTarEmail($tagEmail);
        $emailBean ->setTitle($regTitle);
        $emailBean ->setContent($regContent);
        $emailBean ->setForm('1445808283@qq.com');
        $emailBean ->setFormName('云相册');

        $code = $this->doSend($emailBean);

        return ReturnInfoConf::getReturnTemp($code);
    }

    public function doSend(EmailBean $emailBean){
        $serverName = 'qq';
        $server     = EmailContentConf:: getServer($serverName);

        $mail = new PHPMailer(true);
        try {

            $mail->CharSet = "UTF-8";                           //设定邮件编码
            $mail->SMTPDebug = 0;                               // 调试模式输出
            $mail->isSMTP();                                    // 使用SMTP
            $mail->Host = $server['host'];               // SMTP服务器
            $mail->SMTPAuth = true;                             // 允许 SMTP 认证
            $mail->Username = $server['username'];       // SMTP 用户名  即邮箱的用户名
            $mail->Password = $server['password'];       // SMTP 密码  部分邮箱是授权码(例如163邮箱)
//            $mail->SMTPSecure = 'ssl';                          // 允许 TLS 或者ssl协议
            $mail->Port = 25;                                  // 服务器端口 25 或者465 具体要看邮箱服务器支持


            $mail->Mailer   = "smtp";
            $mail->setFrom($server['username']);  //发件人
            $mail->From     = $emailBean->getForm();
            $mail->FromName = $emailBean->getFormName();
            $mail->addAddress($emailBean->getTarEmail());  // 收件人


            //Content
            $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
            $mail->Subject  = $emailBean->getTitle();
            $mail->Body     = $emailBean->getContent();
            $mail->AltBody  = '您的客户端不支持HTML显示';

            $files = $emailBean->getFiles();
            // 附件
            foreach ($files as $file) {
                $enclosurePath  = isset($file['enclosure_path']) ? $file['enclosure_path'] : '';
                $enclosureName  = isset($file['enclosure_name']) ? $file['enclosure_name'] : '';
                $mail->AddAttachment($enclosurePath, $enclosureName);
            }

            $mail->send();

        } catch (\Exception $e) {
            // 记录邮件发送异常数据
            $errorContent   = $mail->ErrorInfo;
            $code           = substr($errorContent, -3);
            if (strpos($errorContent, 'SMTP connect() failed') !== FALSE) {

                // 邮箱服务器连接异常
                Log::info("连接异常");
                return 55007;
            } elseif ($code == '559') {

                // 接受者邮箱有误
                return 55004;
            } elseif ($code == '554') {

                return 55005;
            }

            return 55001;
        }

        return 10000;

    }
}
