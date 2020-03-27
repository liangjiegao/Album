<?php


namespace App\Http\Commend;


use App\Http\Model\Impl\IUserModel;
use App\Http\Model\UtilsModel;
use Illuminate\Support\Facades\DB;

class UserInfoMapUtils
{

    const USER_TABLE = 'user_info';

    public static function mapUserInfoByAccount( array $list, array $columns ){

        // 提取要映射的账号
        $fromAccount = [];
        foreach ($list as $item) {

            foreach ($columns as $from => $to ) {

                if ( isset( $item[ $from ] ) ){

                    $fromAccount[] = $item[ $from ];

                }

            }
        }

        // 获取用户数据
        $userInfoList = self::getUserInfoBatch( $fromAccount );

        // 提取账号-名称映射表
        $accountNameMap = [];
        foreach ($userInfoList as $userInfo) {
            $accountNameMap[ $userInfo['account'] ] = $userInfo;
        }

        // 将名称映射到账号上
        foreach ($list as &$item) {

            foreach ($columns as $from => $to ) {

                if ( isset( $item[$from] ) && isset( $accountNameMap[ $item[$from] ] ) ){

                    $item[$to] = $accountNameMap[ $item[$from] ];

                }

            }

        }

        return $list;
    }

    private static function getUserInfoBatch( array $accounts ){

        $userInfo = DB::table( self::USER_TABLE )
            -> select( [ 'nickname', 'account', 'icon' ] )
            -> whereIn( 'account', $accounts )
            -> get();
        $userInfo = UtilsModel::changeMysqlResultToArr( $userInfo );

        return $userInfo;
    }
}
