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
}
