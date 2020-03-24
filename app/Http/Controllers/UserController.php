<?php


namespace App\Http\Controllers;


use App\Http\Model\UserModel;
use App\Http\Model\UtilsModel;
use Illuminate\Http\Request;

class UserController
{

    public function createUser(Request $request){

        $params['phone']        = "phone";
        $params['email']        = "email";
        $params['account']      = "account";
        $params['birthday']     = "birthday";
        $params['nickname']     = "nickname";
        $params['remark']       = "remark";


        $requestParams = ControllerUtil::paramsFilter($request, $params);

        $model  = new UserModel();
        $code   = $model->editUser($requestParams);

        return UtilsModel::getCallbackJson($code);
    }

}
