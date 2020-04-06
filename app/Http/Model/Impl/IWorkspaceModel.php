<?php


namespace App\Http\Model\Impl;


interface IWorkspaceModel
{

    /**
     * 获取用户目录数据
     * @param array $params
     * @return mixed
     */
    public function getWorkspace( array $params);


}
