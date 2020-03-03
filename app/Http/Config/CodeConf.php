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

    const OPT_SUCCESS   = 10000;
    const LOGIN_SUCCESS = 10001;

    public static function getConf($code, $other = array()){
        $config =  array(
            self::OPT_SUCCESS   => '操作成功',
            self::LOGIN_SUCCESS => '登录成功',

        );
        if (is_array($other) && count($other) > 0){
            return (array('code'=> $code, 'msg'=> $config[$code]) + $other);
        }
        return array('code'=> $code, 'msg'=> $config[$code]);
    }
}
