<?php


namespace App\Http\Commend;


use App\Http\Config\RedisHeadConf;
use Illuminate\Support\Facades\Redis;

class CommendModel
{

    public static function verificationCheckCode($email, $code){

        $oldCode = Redis::get( RedisHeadConf::getHead('email_reg_code') . md5($email)  );

        if ( $oldCode == $code ) {

            return true;

        }

        return false;
    }



}
