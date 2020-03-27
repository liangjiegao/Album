<?php


namespace App\Http\Commend;


use App\Http\Model\UtilsModel;
use Illuminate\Support\Facades\DB;

class ImageKeyUrlMapUtils
{

    const IMG_INFO_TABLE = 'img_info';

    public static function mapUrlByImgKey( array $list, array $columns ){

        // 提取要映射ImgKey
        $fromImgKey = [];
        foreach ($list as $item) {

            foreach ($columns as $from => $to ) {

                if ( isset( $item[ $from ] ) ){

                    $fromImgKey[] = $item[ $from ];

                }

            }
        }
        // 获取图片数据
        $imgInfoList = self::getImgInfoBatch( $fromImgKey );
        // 提取 img_key - path 映射表
        $keyPathMap = [];
        foreach ($imgInfoList as $imgInfo) {
            $keyPathMap[ $imgInfo['img_key'] ] = $imgInfo['path'];
        }

        // 将名称映射到账号上
        foreach ($list as &$item) {

            foreach ($columns as $from => $to ) {

                if ( isset( $item[$from] ) && isset( $keyPathMap[ $item[$from] ] ) ){

                    $item[$to] = $keyPathMap[ $item[$from] ];

                }

            }

        }

        return $list;
    }

    private static function getImgInfoBatch( array $imgKeys){
        $imgInfos = DB::table( self::IMG_INFO_TABLE )
                    -> select( [ 'path', 'img_key' ] )
                    -> whereIn( 'img_key', $imgKeys )
                    -> get();
        $imgInfos = UtilsModel::changeMysqlResultToArr( $imgInfos );

        return $imgInfos;

    }

}
