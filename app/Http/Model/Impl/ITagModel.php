<?php


namespace App\Http\Model\Impl;


interface ITagModel
{

    /**
     * 添加标签
     * @param array $params
     * @return mixed
     */
    public function addTag( array $params);

    /**
     * 删除标签
     * @param array $params
     * @return mixed
     */
    public function deleteTag( array $params );


}
