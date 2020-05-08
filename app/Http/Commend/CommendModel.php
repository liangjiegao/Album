<?php


namespace App\Http\Commend;


use App\Http\Config\CodeConf;
use App\Http\Config\EmailContentConf;
use App\Http\Config\PublicPath;
use App\Http\Config\RedisHeadConf;
use App\Http\Model\UtilsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CommendModel
{
    const CHECK_CODE_LIVE   = 60 * 5;
    const IMG_TAG_TABLE     = 'img_tag';
    const TAG_TABLE         = 'tag';

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

        // 有效期5分钟
        Redis::setex( $redisHead . md5($email), self::CHECK_CODE_LIVE, $code);
        return  $code;
    }

    public static function pathFormatToUrl( array $list, array $columns ){

        foreach ($list as &$item) {

            foreach ($columns as $from => $to ) {

                if ( isset( $item[$from] ) ){

                    $item[$to] = str_replace( PublicPath::getPath( 'resource_head' ), PublicPath::getPath( 'server_root' ) . 'head/', $item[$from]);;

                }

            }

        }

        return $list;
    }

    public static function getTagImgKey( $tabInfos, $keywords ){


        $tabLikeWhere = self::getListLikeBinding($tabInfos, 'name', 'or');
        $tabLikeParam = self::getListLikeParams($tabInfos, 'name');

        $keywordLikeWhere = self::getListLikeBinding($keywords, 'name', 'or', count($tabLikeParam));
        $keywordLikeParam = self::getListLikeParams($keywords, 'name', count($tabLikeParam));

        $where = '';
        $params = array_merge( $tabLikeParam , $keywordLikeParam);
        if ( !empty( $tabLikeWhere ) ){
            $where .= $tabLikeWhere;
        }
        if ( !empty( $keywordLikeWhere ) ){
            if ( !empty( $where ) )
                $where = $where . ' and ' . $keywordLikeWhere;
            else $where = $keywordLikeWhere;
        }
        $imgKeys = DB::table( self::IMG_TAG_TABLE )
                -> leftJoin( self::TAG_TABLE, self::IMG_TAG_TABLE . '.tag_key', '=',  self::TAG_TABLE . '.tag_key')
                -> select( ['img_key'] )
                -> whereRaw( $where, $params )
                -> get();

        $imgKeys    = UtilsModel::changeMysqlResultToArr($imgKeys);
        return array_column($imgKeys, 'img_key');
    }

    public static function getListLikeBinding($list, $column, $symbol, $start = 0)
    {

        $list = is_array($list) ? $list : [$list];
        $where = '';

        foreach ($list as $key => $item) {
            $index = $start + $key;
            $where .= "{$column} like :{$column}{$index}";
            if ($key < sizeof($list) - 1) {
                $where .= " $symbol ";
            }
        }
        if ( !empty( $where ) ){
            $where = "({$where})";
        }

        return $where;
    }

    public static function getListLikeParams($list, $column, $start = 0){
        $params = [];
        foreach ($list as $key => $item) {
            $index = $start + $key;
            $params["{$column}{$index}"] = '%' . $item . '%';
        }
        return $params;
    }
}
