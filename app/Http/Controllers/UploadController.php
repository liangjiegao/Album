<?php


namespace App\Http\Controllers;


use App\Http\Model\Impl\IUploadModel;
use App\Http\Model\UtilsModel;
use Illuminate\Http\Request;

class UploadController
{
    private $_upload_model;

    public function __construct(IUploadModel $model)
    {
        $this->_upload_model = $model;
    }

    public function uploadImg( Request $request ){

        $params['dir_id']   = 'dir_id';

        $requestParams      = ControllerUtil::paramsFilter($request, $params);

        $code               = $this->_upload_model->uploadImg($requestParams);

        return UtilsModel::getCallbackJson($code);

    }

    public function createDir( Request $request ){

        $params['dir_name']   = 'dir_name';
        $params['pid'     ]   = 'pid'     ;

        $requestParams      = ControllerUtil::paramsFilter($request, $params);

        $code               = $this->_upload_model->createDir($requestParams);

        return UtilsModel::getCallbackJson($code);
    }

    public function deleteDir( Request $request ){

        $params['dir_id']   = 'dir_id';

        $requestParams      = ControllerUtil::paramsFilter($request, $params);

        $code               = $this->_upload_model->deleteDir($requestParams);

        return UtilsModel::getCallbackJson($code);
    }

    public function deleteImg( Request $request ){

        $params['img_key']  = 'img_key';

        $requestParams      = ControllerUtil::paramsFilter($request, $params);

        $code               = $this->_upload_model->deleteImg($requestParams);

        return UtilsModel::getCallbackJson($code);
    }


}
