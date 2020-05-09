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

            // 修改密码
            'email_change_password_code'           => 'email_change_password_code_',

            // 用户关系
            'relation_sort'           => 'relation_sort_',

            //分享
            'share_info_sort'           => 'share_info_sort_',

            // 标签key
            'tag_key_sort'           => 'tag_key_sort_',


            // 图片解析
            'img_build_tag_access_token'           => 'img_build_tag_access_token',

            // 图片队列
            'img_list'           => 'img_list',

            'websock_account_fd' => 'websock_account_fd'

        ];

        return isset($list[$key]) ? $list[$key] : "";
    }

}
