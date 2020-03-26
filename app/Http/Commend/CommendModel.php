<?php


namespace App\Http\Commend;


use App\Http\Config\CodeConf;
use App\Http\Config\EmailContentConf;
use App\Http\Config\RedisHeadConf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CommendModel
{
    const CHECK_CODE_LIVE     = 60 * 5;


    public static function verificationCheckCode($email, $code, $type = ''){

        // 判断验证码类型
        switch ($type){
            case EmailContentConf::REG:
                $redisHead = RedisHeadConf::getHead('email_reg_code');
                break;
            case EmailContentConf::CH_PASSWORD:
                $redisHead = RedisHeadConf::getHead('email_change_password_code');
                break;
            default : {
                // 默认注册验证码
                $redisHead = RedisHeadConf::getHead('email_reg_code');
            }
        }


        $oldCode = Redis::get( $redisHead . md5($email)  );

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
        Log::info("全路径 " . $path . $fileName);
        // 保存文件
        if ( !move_uploaded_file( $file, $path . $fileName ) ) {

            return CodeConf::FILE_SAVE_FAIL;

        }

        return CodeConf::OPT_SUCCESS;
    }

    public static function delFile( $path ){

        return unlink($path);

    }

    /**
     * 获取验证码
     * @param $email // 注册邮箱
     * @param $type
     * @return string   // 返回字符串类型的验证码
     */
    public static function createCheckCode($email, $type = '') :string
    {
        // 判断验证码类型
        switch ($type){
            case EmailContentConf::REG:
                $redisHead = RedisHeadConf::getHead('email_reg_code');
                break;
            case EmailContentConf::CH_PASSWORD:
                $redisHead = RedisHeadConf::getHead('email_change_password_code');
                break;
            default : {
                // 默认注册验证码
                $redisHead = RedisHeadConf::getHead('email_reg_code');
            }
        }

        // 判断当前邮箱是否已经保存过验证码
        $oldCode = Redis::get( $redisHead . md5($email) );

        if ( !empty($oldCode) ){

            Redis::del( $redisHead . md5($email) );

        }


        $code = rand(100000, 999999);
        Log::info($redisHead);
        // 有效期5分钟
        Redis::setex( $redisHead . md5($email), self::CHECK_CODE_LIVE, $code);
        return  $code;
    }


}
