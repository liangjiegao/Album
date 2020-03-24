<?php


namespace App\Http\Config;


class RedisHeadConf
{

    public static function getHead($key){

        $list = [

            'email_sort'            => 'email_sort_',

            'account_sort'          => 'account_sort_',

            'password_sort'         => 'password_sort_',

            'email_reg_code'        => 'email_reg_code_',

            // 生成token的盐
            'login_token_sort'      => 'login_token_sort_',

            // 登录的token
            'login_token'           => 'login_token_',


        ];

        return isset($list[$key]) ? $list[$key] : "";
    }

}
