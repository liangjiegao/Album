<?php


namespace App\Http\Config;


class PublicPath
{
    public static function getPath( $key ){
        $env = env( 'APP_ENV', 'local' );
        if ( $env == 'local' ){
            $key = 'TEST_' . $key;
        }
        $list = [

            // 头像路径
            'resource_head'         => '/data/resource/head/',
            'TEST_resource_head'    => 'C:\Users\Administrator\Desktop\test\head\\',

            // 服务器地址
            'server_root'           => '',
            'TEST_server_root'      => 'C:\Users\Administrator\Desktop\test\\',

        ];

        return isset($list[$key]) ? $list[$key] : '';
    }
}
