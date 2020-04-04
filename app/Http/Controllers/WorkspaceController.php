<?php


namespace App\Http\Controllers;


use App\Http\Model\Impl\IWorkspaceModel;
use App\Http\Model\UtilsModel;
use Illuminate\Http\Request;

class WorkspaceController
{

    private $_workspace_model;

    public function __construct(IWorkspaceModel $workspaceModel)
    {
        $this->_workspace_model = $workspaceModel;
    }

    public function getWorkspace(Request $request)
    {
        $params['dir_id']       = "dir_id";
        $params['keyword']      = "keyword";


        $requestParams  = ControllerUtil::paramsFilter($request, $params);

        $returnInfo     = $this->_workspace_model->getWorkspace($requestParams);

        $code = $returnInfo['code'];
        $info = $returnInfo['info'];

        return UtilsModel::getCallbackJson($code, [ 'data' => [ 'list' => $info ] ]);
    }

}
