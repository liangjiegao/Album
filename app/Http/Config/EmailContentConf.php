<?php


namespace App\Http\Config;


class EmailContentConf
{
    private $_reg_title = '注册验证码';

    private $_reg_content = "您好，您的注册验证码是：%s，有效期为5分钟。</br>如果不是本人操作请忽略此邮件。";

    private static $_servers = [

        "qq"        => ["host" => "smtp.qq.com", "username" => "1445808283@qq.com", "password" => "iknecrtwqpgwigai"],

    ];

    /**
     * @return string
     */
    public function getRegTitle(): string
    {
        return $this->_reg_title;
    }

    /**
     * @param string $reg_title
     */
    public function setRegTitle(string $reg_title): void
    {
        $this->_reg_title = $reg_title;
    }

    /**
     * @param string $code
     * @return string
     */
    public function getRegContent(string $code): string
    {
        return sprintf($this->_reg_content, $code);
    }

    /**
     * @param string $reg_content
     */
    public function setRegContent(string $reg_content): void
    {
        $this->_reg_content = $reg_content;
    }


    /**
     * @param $serverName
     * @return array
     */
    public static function getServer($serverName): array
    {
        return isset(self::$_servers[$serverName]) ? self::$_servers[$serverName] : [];
    }


}
