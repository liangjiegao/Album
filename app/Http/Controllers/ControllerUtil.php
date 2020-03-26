<?php


namespace App\Http\Controllers;


use App\Http\Model\Common\UserCommonModel;
use App\Http\Model\Impl\IUserModel;
use App\Http\Model\UtilsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ControllerUtil
{

    /**
     * 参数过滤
     * @param Request $request
     * @param $params
     * @return array
     */
    public static function paramsFilter(Request $request, $params){
        $requestParams = [];
        $token                      = $request->input('token', '');
        if ( !empty( $token ) ) {

            $requestParams["account"]   = UtilsModel::getAccount($token);

        }


        foreach ($params as $key => $param) {

            if ( $key == 'opt_email' ){
                $account = $requestParams["account"];
                $userModel = \App::make(IUserModel::class);
                $p = [
                    'unique_key'    => 'account',
                    'unique_val'    => $account,
                ];
                $userInfo                   = $userModel->getUserInfo( $p );
                $requestParams['opt_email'] = isset($userInfo['email']) ? $userInfo['email'] : '';
            }else{
                $result = $request->input($param);
                $requestParams[$key] = isset($result) ? $result : '';
            }



        }
        return $requestParams;
    }


}
