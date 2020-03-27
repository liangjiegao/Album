<?php


namespace App\Http\Model\Impl;


interface IShareModel
{
    /**
     * 创建分享记录
     * @param array $params
     * @return mixed
     */
    public function createShare( array $params );

    /**
     * 删除分享
     * @param array $params
     * @return mixed
     */
    public function deleteShare( array $params );

    /**
     * 获取分享列表
     * @param array $params
     * @return mixed
     */
    public function getShareList( array $params );

    /**
     * 点赞或取消点赞
     * @param array $params
     * @return mixed
     */
    public function upShare( array $params);

    /**
     * 评论
     * @param array $params
     * @return mixed
     */
    public function commentShare( array $params);

    /**
     * 获取评论
     * @param array $params
     * @return mixed
     */
    public function getCommentList( array $params );
}
