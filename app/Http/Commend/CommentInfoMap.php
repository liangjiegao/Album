<?php


namespace App\Http\Commend;


use App\Http\Model\UtilsModel;
use Illuminate\Support\Facades\DB;

class CommentInfoMap
{

    public static function mapCommentInfoToShare( array $list, string $column ){

        // 提取shareKey
        $shareKeys = [];
        foreach ($list as $item) {

            if ( isset( $item[$column] ) ){

                $shareKeys[] = $item[$column];

            }

        }

        $commentList = self::getCommentInfoBatch( $shareKeys );
        // 映射评论用户名
        $mapColumns = [
            'account' => 'user_info',
        ];
        $commentList = UserInfoMapUtils::mapUserInfoByAccount( $commentList, $mapColumns );

        foreach ($list as &$item) {

            if ( isset( $item[$column] ) ){
                $item['comment_list'] = [];
                foreach ($commentList as $comment) {

                    if ( $item[$column] == $comment['share_key'] ){

                        $item['comment_list'][] = $comment;

                    }

                }

            }

        }

        return $list;
    }

    private static function getCommentInfoBatch( $shareKeys ){

        $commentList = DB::table( 'comment' )
                    -> select(['*'])
                    -> whereIn( 'share_key', $shareKeys )
                    -> where( 'pid_first', '=', 0 )
                    -> where( 'is_delete', '=', 0 )
                    -> get();
        $commentList = UtilsModel::changeMysqlResultToArr($commentList);

        return $commentList;
    }

}
