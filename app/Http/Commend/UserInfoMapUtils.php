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
        \Log::info($fromAccount);
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

    public static function mapNameToAccount( array $list, string $column , string $columnType = 'json', string $columnData = 'array' ){

        // 提取账号
        $accounts = [];
        foreach ($list as &$item) {

            if ( isset( $item[$column] ) ){

                if ( $columnType == 'json' ){

                    if ( $columnData == 'array' ){

                        $info           = json_decode($item[$column]);
                        $item[$column]  = $info;
                        $accounts       = array_merge($info , $accounts);

                    }


                }


            }

        }
        $userInfoList = self::getUserInfoBatch( $accounts );

        foreach ($list as &$item) {

            if ( isset( $item[$column] ) ){


                if ( $columnType == 'json' ){

                    if ( $columnData == 'array' ){
                        $userInfos = [];

                        foreach ($item[$column] as $account) {

                            foreach ($userInfoList as $userInfo) {

                                if ( isset( $userInfo['account'] ) && $userInfo['account'] == $account ){

                                    $userInfos[] = $userInfo;

                                }

                            }

                        }
                        $item[$column] = $userInfos;

                    }
                }



            }

        }

        return $list;
    }

}
