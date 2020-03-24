<?php
/**
 * Created by PhpStorm.
 * User: LG
 * Date: 2019/4/9
 * Time: 16:28
 */

namespace App\Http\Config;


class CodeConf
{
    /**
     * 10000 - 19999 通用成功码
     * 20000 - 29999 通用失败码
     * 30000 - 39999 数据库操作失败
     * 40000 - 49999 Redis操作失败
     * 50000 - 59999 邮件操作异常
     */
    const OPT_SUCCESS               = 10000;
    const LOGIN_SUCCESS             = 10001;

    const OPT_FAIL                  = 20000;
    const CHECK_CODE_INVALID        = 20001;
    const PASSWORD_LEN_TOO_SHORT    = 20002;
    const CONF_PASSWD_UN_EQUAL      = 20003;
    const LOGIN_PASSWD_MISMATCH     = 20004;
    const USER_UN_EXIST             = 20005;

    const DB_OPT_FAIL               = 30001;
    const FILE_SAVE_FAIL            = 30002;
    const DIR_MAKE_FAIL             = 30003;

    const LOGIN_EXPIRE              = 50008;

    const EMAIL_SEND_FAIL           = 55001;
    const EMAIL_LINK_INVALID        = 55002;
    const EMAIL_UNAVAILABLE         = 55004;
    const EMAIL_CONTENT_UNAVAILABLE = 55005;
    const EMAIL_SERVER_CONN_ERROR   = 55007;
    const EMAIL_CONTENT_NULL        = 55009;

    public static function getConf($code, $other = array()){
        $config =  array(
            // 10000 - 19999
            self::OPT_SUCCESS   => '操作成功',
            self::LOGIN_SUCCESS => '登录成功',

            // 20000 - 29999
            self::OPT_FAIL                  => '操作失败',
            self::CHECK_CODE_INVALID        => '验证码无效',
            self::PASSWORD_LEN_TOO_SHORT    => '密码长度低于6位',
            self::CONF_PASSWD_UN_EQUAL      => '确认密码与新密码不一致',
            self::LOGIN_PASSWD_MISMATCH     => '密码错误',
            self::USER_UN_EXIST             => '用户不存在',

            // 30000 - 39999
            self::DB_OPT_FAIL               => '数据库修改失败',
            self::FILE_SAVE_FAIL            => '文件保存失败',
            self::DIR_MAKE_FAIL             => '目录创建失败',

            // 50000 - 59999
            self::LOGIN_EXPIRE              => '请从新登录！',
            self::EMAIL_SEND_FAIL           => '邮件发送失败',
            self::EMAIL_LINK_INVALID        => '邮件链接已失效',
            self::EMAIL_UNAVAILABLE         => '您发送的邮箱账号为异常邮箱，请检查是否输入错误后',
            self::EMAIL_CONTENT_UNAVAILABLE => '您的邮件内容被判定为垃圾邮件，请勿发送',
            self::EMAIL_SERVER_CONN_ERROR   => '邮箱服务器连接异常',
            self::EMAIL_CONTENT_NULL        => '邮箱内容不能为空',

        );
        if (is_array($other) && count($other) > 0){
            return (array('code'=> $code, 'msg'=> $config[$code]) + $other);
        }
        return array('code'=> $code, 'msg'=> $config[$code]);
    }
}
