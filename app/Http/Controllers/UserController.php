<?php


namespace App\Http\Controllers;


use App\Http\Config\CodeConf;
use App\Http\Model\Impl\IUserModel;
use App\Http\Model\UtilsModel;
use Illuminate\Http\Request;

class UserController
{

    private $_user_model;

    public function __construct(IUserModel $userModel)
    {
        $this->_user_model = $userModel;
    }

    public function editUser(Request $request){

        $params['phone']        = "phone";
        $params['email']        = "email";
        $params['birthday']     = "birthday";
        $params['nickname']     = "nickname";
        $params['remark']       = "remark";


        $requestParams = ControllerUtil::paramsFilter($request, $params);

        $code   = $this->_user_model->editUser($requestParams);

        return UtilsModel::getCallbackJson($code);
    }


    public function getUserInfo( Request $request ){

        $params       = [];

        $requestParams = ControllerUtil::paramsFilter($request, $params);

        $requestParams['unique_key'] = 'account';
        $requestParams['unique_val'] = $requestParams['account'];

        $userInfo   = $this->_user_model->getUserInfo($requestParams);
        unset($userInfo['password']);
        return UtilsModel::getCallbackJson(CodeConf::OPT_SUCCESS, ['data' => ['user_info' => $userInfo]]);

    }

    public function changePassword( Request $request ) {

        $params['old_password']     = "old_password";
        $params['new_password']     = "new_password";
        $params['confirm_password'] = "confirm_password";


        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $code           = $this->_user_model->changePassword($requestParams);

        return UtilsModel::getCallbackJson($code);
    }

    public function changeHeadIcon( Request $request ) {

        $params = [];

        $requestParams  = ControllerUtil::paramsFilter( $request, $params );

        $returnInfo = $this->_user_model->changeHeadIcon( $requestParams );
        $code       = $returnInfo['code'];
        if ( $code != CodeConf::OPT_SUCCESS ){
            return UtilsModel::getCallbackJson( $code );
        }
        $info       = $returnInfo['info'];
        $url        = $info['url'];
        return UtilsModel::getCallbackJson( $code, ['data' => [ 'url' => $url ] ] );
    }

}
