<?php


namespace App\Http\Bean;


class UserBean
{

    private $id             ;
    private $account        ;
    private $nickname       ;
    private $phone          ;
    private $email          ;
    private $birthday       ;
    private $icon           ;
    private $remark         ;
    private $password       ;
    private $create_time    ;
    private $last_login_time;
    private $last_login_ip  ;

    /**
     * @return mixed
     */
    public function getNickname()
    {
        return $this->nickname;
    }
    /**
     * @param mixed $nickname
     */
    public function setNickname($nickname): void
    {
        $this->nickname = $nickname;
    }

    /**
     * @return mixed
     */
    public function getLastLoginIp()
    {
        return $this->last_login_ip;
    }

    /**
     * @param mixed $last_login_ip
     */
    public function setLastLoginIp($last_login_ip): void
    {
        $this->last_login_ip = $last_login_ip;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account): void
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param mixed $birthday
     */
    public function setBirthday($birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return mixed
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param mixed $remark
     */
    public function setRemark($remark): void
    {
        $this->remark = $remark;
    }

    /**
     * @return mixed
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * @param mixed $create_time
     */
    public function setCreateTime($create_time): void
    {
        $this->create_time = $create_time;
    }

    /**
     * @return mixed
     */
    public function getLastLoginTime()
    {
        return $this->last_login_time;
    }

    /**
     * @param mixed $last_login_time
     */
    public function setLastLoginTime($last_login_time): void
    {
        $this->last_login_time = $last_login_time;
    }



}
