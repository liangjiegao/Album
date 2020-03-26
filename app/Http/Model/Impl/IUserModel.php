<?php


namespace App\Http\Model\Impl;


interface IUserModel
{
    /**
     * 创建用户
     * @param array $params
     * @return mixed
     */
    public function createUser(array $params);

    /**
     * 编辑用户
     * @param array $params
     * @return mixed
     */

    public function editUser(array $params);


    /**
     * 获取用户信息
     * @param array $params
     * @return mixed
     */
    public function getUserInfo(array $params);

    /**
     * 修改密码
     * @param array $params
     * @return mixed
     */
    public function changePassword(array $params);

    /**
     * 修改头像
     * @param array $params
     * @return mixed
     */
    public function changeHeadIcon(array $params);

    /**
     * 发送修改密码邮箱验证码
     * @param array $params
     * @return mixed
     */
    public function sendChangePasswordCheckCode( array $params );

    /**
     * 申请添加好友
     * @param array $params
     * @return mixed
     */
    public function applyFriend( array $params );

    /**
     * 获取用户添加别的好友的申请列表
     * @param array $params
     * @return mixed
     */
    public function getMyApplyList( array $params );

    /**
     * 获取别人申请自己为好友的列表
     * @param array $params
     * @return mixed
     */
    public function getOtherApplyList( array $params );

    /**
     * 通过好友申请
     * @param array $params
     * @return mixed
     */
    public function optFriendApply( array $params);


}
