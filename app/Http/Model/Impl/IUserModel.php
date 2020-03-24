<?php


namespace App\Http\Model\Impl;


interface IUserModel
{

    public function createUser(array $params);

    public function editUser(array $params);

    /**
     * 获取用户信息
     * @param string $uniqueKey // 唯一键
     * @param string $uniqueVal // 唯一键值
     * @return mixed
     */
    public function fetchUserInfo(string $uniqueKey, string $uniqueVal);

}
