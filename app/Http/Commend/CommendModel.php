<?php


namespace App\Http\Commend;


use App\Http\Config\CodeConf;
use App\Http\Config\RedisHeadConf;
use Illuminate\Support\Facades\Log;
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

    public static function saveFile($file, $path, $fileName){

        if ( !file_exists( $path ) ) {
            try {
                Log::info("创建目录" .$path );
                mkdir( $path , 0777, true );
            } catch (\Exception $e) {
                Log::info('error_message' . $e->getMessage());
                return CodeConf::DIR_MAKE_FAIL;
            }
        }
        Log::info($path);
        // 保存文件
        if ( !move_uploaded_file( $file, $path . $fileName ) ) {

            return CodeConf::FILE_SAVE_FAIL;

        }

        return CodeConf::OPT_SUCCESS;
    }


}
