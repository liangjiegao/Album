<?php


namespace App\Http\Config;


class EmailContentConf
{

    const REG         = 'reg';
    const CH_PASSWORD = 'ch_passwd';

    private $_title             = [
        'reg'       => '注册验证码',
        'ch_passwd' => '修改密码验证码'
    ];

    private $_content       = [
        'reg'       => '您好，您的注册验证码是：%s，有效期为5分钟。</br>如果不是本人操作请忽略此邮件。',
        'ch_passwd' => '您好，您的验证码是：%s，有效期为5分钟。</br>如果不是本人操作请忽略此邮件。',
    ];

    private static $_servers = [

        "qq"        => ["host" => "smtp.qq.com", "username" => "1445808283@qq.com", "password" => "iknecrtwqpgwigai"],

    ];

    /**
     * @param $type
     * @return string
     */
    public function getTitle( string $type ): string
    {
        switch ($type){
            case self::REG :
                return $this->_title[self::REG];
            case self::CH_PASSWORD :
                return $this->_title[self::CH_PASSWORD];
            default : {
                return $this->_title[self::REG];
            }
        }
    }


    /**
     * @param string $code
     * @param string $type
     * @return string
     */
    public function getContent(string $code, string $type): string
    {
        switch ($type){
            case self::REG :
                return sprintf( $this->_content[self::REG], $code );
            case self::CH_PASSWORD :
                return sprintf( $this->_content[self::CH_PASSWORD], $code );
            default : {
                return sprintf( $this->_content[self::REG], $code );
            }
        }
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
