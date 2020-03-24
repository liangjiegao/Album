<?php


namespace App\Http\Config;


class ReturnInfoConf
{

    public static function getReturnTemp($code, $info = []){

        $returnInfo = ['code' => $code, 'info' => $info];

        return $returnInfo;
    }

}
