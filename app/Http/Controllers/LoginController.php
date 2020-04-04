<?php


namespace App\Http\Controllers;


use App\Http\Model\Impl\ILoginModel;
use App\Http\Model\LoginModel;
use App\Http\Model\UtilsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController
{
    private $_loginModel;

    public function __construct(ILoginModel $model)
    {
        $this->_loginModel = $model;
    }

    public function sendRegCode(Request $request){

        $params['tag_email']    = "tag_email";


        $requestParams  = ControllerUtil::paramsFilter($request, $params);
        $returnInfo     = $this->_loginModel -> sendRegCode($requestParams);

        $code = $returnInfo['code'];
        return UtilsModel::getCallbackJson($code);

    }

    public function sendCPCode(Request $request){

        $params['tag_email']    = "tag_email";


        $requestParams  = ControllerUtil::paramsFilter($request, $params);
        $returnInfo     = $this->_loginModel -> sendCPCode($requestParams);

        $code = $returnInfo['code'];
        return UtilsModel::getCallbackJson($code);

    }



    public function reg(Request $request){

        $params['email']        = "email";
        $params['check_code']   = "check_code";
        $params['password']     = "password";
        $params['confirm']      = "confirm";

        $requestParams  = ControllerUtil::paramsFilter($request, $params);
        $returnInfo     = $this->_loginModel -> reg($requestParams);

        $code           = $returnInfo['code'];

        return UtilsModel::getCallbackJson($code);
    }

    public function login(Request $request) {

        $params['email']        = "email";
        $params['password']     = "password";

        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $returnInfo     = $this->_loginModel -> login($requestParams);

        $code           = $returnInfo['code'];
        $token          = '';
        if ( $code == 10000 ) {

            $token = $returnInfo['info']['token'];
        }

        return UtilsModel::getCallbackJson($code, ['data' => ['token' => $token]]);

    }

    public function changePassword(Request $request) {

        $params['email']        = "email";
        $params['password']     = "password";
        $params['check_code']   = "check_code";

        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $code     = $this->_loginModel -> changePassword($requestParams);

        return UtilsModel::getCallbackJson($code);

    }

}
