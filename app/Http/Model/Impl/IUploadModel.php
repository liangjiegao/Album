<?php


namespace App\Http\Model\Impl;


interface IUploadModel
{
    /**
     * 创建相册虚拟文件夹
     * @param array $params
     * @return string
     */
    public function createDir( array $params ) : string ;

    /**
     * 删除相册虚拟文件夹
     * @param array $params
     * @return string
     */
    public function deleteDir( array $params ) : string;


    /**
     * 上传图片
     * @param array $params
     * @return string
     */
    public function uploadImg( array $params ) : string;

    /**
     * 删除图片
     * @param array $params
     * @return string
     */
    public function deleteImg( array $params ) : string;

    /**
     * 上传压缩包
     * @param array $params
     * @return string
     */
    public function uploadPackage( array $params):string;
}
