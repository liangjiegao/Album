<?php


namespace App\Http\Controllers;


use App\Http\Config\CodeConf;
use App\Http\Model\Impl\ILoginModel;
use App\Http\Model\Impl\IShareModel;
use App\Http\Model\LoginModel;
use App\Http\Model\UtilsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShareController
{
    private $_shareModel;

    public function __construct(IShareModel $model)
    {
        $this->_shareModel = $model;
    }


    public function createShare( Request $request ) {

        $params['img_key']          = 'img_key'     ;
        $params['info']             = 'info'        ;
        $params['share_group']      = 'share_group' ;
        $params['share_type']       = 'share_type' ;

        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $code     = $this->_shareModel -> createShare($requestParams);

        return UtilsModel::getCallbackJson($code);

    }

    public function getShareList( Request $request ) {

        $params['list_type']    = 'list_type'        ;
        $params['page']         = 'page'        ;
        $params['count']        = 'count'        ;

        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $list     = $this->_shareModel -> getShareList($requestParams);

        return UtilsModel::getCallbackJson(CodeConf::OPT_SUCCESS, [ 'data' => [ 'list' => $list ] ] );

    }

    public function deleteShare( Request $request ) {

        $params['share_key']    = 'share_key'        ;

        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $code     = $this->_shareModel -> deleteShare($requestParams);

        return UtilsModel::getCallbackJson( $code );

    }

    public function upShare( Request $request ) {

        $params['share_key']    = 'share_key'        ;

        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $code     = $this->_shareModel -> upShare($requestParams);

        return UtilsModel::getCallbackJson( $code );

    }

    public function commentShare( Request $request ) {

        $params['share_key']            = 'share_key';
        $params['comment_info']         = 'comment_info';
        $params['first_comment_key']    = 'first_comment_key';
        $params['second_comment_key']   = 'second_comment_key';

        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $code     = $this->_shareModel -> commentShare($requestParams);

        return UtilsModel::getCallbackJson( $code );

    }

    public function getCommentList( Request $request ) {

        $params['share_key']        = 'share_key';
        $params['p_comment_key']    = 'p_comment_key';
        $params['page']             = 'page';
        $params['count']            = 'count';

        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $returnInfo     = $this->_shareModel -> getCommentList($requestParams);

        $code = $returnInfo['code'];
        $list = $returnInfo['info'];

        return UtilsModel::getCallbackJson( $code, [ 'data' => [ 'list' => $list ] ] );

    }

    public function getWorldImgList( Request $request ) {

        $params['page']             = 'page';
        $params['count']            = 'count';
        $params['tab_info']         = 'tab_info';
        $params['keyword']          = 'keyword';

        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $list     = $this->_shareModel -> getWorldImgList($requestParams);

        return UtilsModel::getCallbackJson( CodeConf::OPT_SUCCESS, [ 'data' => [ 'list' => $list ] ] );

    }
}
