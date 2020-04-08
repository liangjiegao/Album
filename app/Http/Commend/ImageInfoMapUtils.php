<?php


namespace App\Http\Commend;


use App\Http\Config\PublicPath;
use App\Http\Model\UtilsModel;
use Illuminate\Support\Facades\DB;

class ImageInfoMapUtils
{

    const IMG_INFO_TABLE    = 'img_info';
    const IMG_TAG_TABLE     = 'img_tag';
    const TAG_TABLE         = 'tag';

    public static function mapUrlByImgKey( array $list, array $columns ){

        // 提取要映射ImgKey
        $fromImgKey = self::extractImgListImgKey( $list, array_keys($columns) );
//        foreach ($list as $item) {
//
//            foreach ($columns as $from => $to ) {
//
//                if ( isset( $item[ $from ] ) ){
//
//                    $fromImgKey[] = $item[ $from ];
//
//                }
//
//            }
//        }
        // 获取图片数据
        $imgInfoList = self::getImgInfoBatch( $fromImgKey );
        // 提取 img_key - path 映射表
        $keyPathMap = [];
        foreach ($imgInfoList as $imgInfo) {
//            str_replace( PublicPath::getPath( 'resource_head' ), PublicPath::getPath( 'server_root' ) . 'head/', $imgInfo['path']);;


            $keyPathMap[ $imgInfo['img_key'] ] = str_replace( PublicPath::getPath( 'resource_head' ), PublicPath::getPath( 'server_root' ) . 'head/', $imgInfo['path']);
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

    private static function extractImgListImgKey( $list, $keyColumns ){

        // 提取要映射ImgKey
        $fromImgKey = [];
        foreach ($list as $item) {

            foreach ($keyColumns as $keyColumn ) {

                if ( isset( $item[ $keyColumn ] ) ){

                    $fromImgKey[] = $item[ $keyColumn ];

                }

            }
        }

        return $fromImgKey;
    }

    private static function getImgInfoBatch( array $imgKeys){
        $imgInfos = DB::table( self::IMG_INFO_TABLE )
                    -> select( [ 'path', 'img_key' ] )
                    -> whereIn( 'img_key', $imgKeys )
                    -> get();
        $imgInfos = UtilsModel::changeMysqlResultToArr( $imgInfos );

        return $imgInfos;

    }

    public static function mapTagIntoImgInfo( array $list, array $columns ){

        // 提取要映射ImgKey
        $fromImgKeys = self::extractImgListImgKey( $list, array_keys($columns) );

        // 获取标签数据
        $imgTagList = self::getImgTagBatch( $fromImgKeys );

        // 标签提取
        $imgTagGroup = [];
        foreach ($imgTagList as $imgTag) {

            $imgTagGroup[ $imgTag[ 'img_key' ] ][ $imgTag['tag_key'] ] = $imgTag[ 'tag_info' ];

        }


        // 标签映射到图片列表中
        foreach ($list as &$item) {


            foreach ($columns as $from => $to ) {
                $item[$to] = [];
                if ( isset( $item[$from] ) && isset( $imgTagGroup[ $item[$from] ] ) ){

                    $item[$to] = $imgTagGroup[ $item[$from] ];

                }

            }

        }

        return $list;
    }

    private static function getImgTagBatch( array $imgKeys ){
        $imgTags = DB::table( self::IMG_TAG_TABLE )
                    -> leftJoin( self::TAG_TABLE , self::IMG_TAG_TABLE . '.tag_key', '=', self::TAG_TABLE . '.tag_key')
                    -> select( [ 'name as tag_info', 'img_key', self::TAG_TABLE . '.tag_key' ] )
                    -> whereIn( 'img_key', $imgKeys )
                    -> where( 'img_tag.is_delete', 0 )
                    -> where( 'tag.is_delete', 0 )
                    -> get();
        $imgTags = UtilsModel::changeMysqlResultToArr( $imgTags );
        return $imgTags;

    }


}
