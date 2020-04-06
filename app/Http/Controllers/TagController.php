<?php


namespace App\Http\Controllers;


use App\Http\Model\Impl\ITagModel;
use App\Http\Model\UtilsModel;
use Illuminate\Http\Request;

class TagController
{

    private $_tagModel;

    public function __construct(ITagModel $model)
    {
        $this->_tagModel = $model;
    }

    public function addTag(Request $request){

        $params['img_key']  = "img_key";
        $params['tag_name'] = "tag_name";

        $requestParams  = ControllerUtil::paramsFilter($request, $params);
        $code           = $this->_tagModel -> addTag($requestParams);

        return UtilsModel::getCallbackJson($code);

    }

    public function deleteTag(Request $request){

        $params['img_key']  = "img_key";
        $params['tag_key']  = "tag_key";

        $requestParams  = ControllerUtil::paramsFilter($request, $params);
        $code           = $this->_tagModel -> deleteTag($requestParams);

        return UtilsModel::getCallbackJson($code);

    }

}
