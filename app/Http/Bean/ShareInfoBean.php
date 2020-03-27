<?php


namespace App\Http\Bean;


class ShareInfoBean
{

    private $id             ;
    private $share_key      ;
    private $account        ;
    private $img_key        ;
    private $info           ;
    private $share_group    ;
    private $addr           ;
    private $create_time    ;
    private $is_delete = 0  ;
    private $up_account     ;
    private $share_type     ;

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
    public function getShareKey()
    {
        return $this->share_key;
    }

    /**
     * @param mixed $share_key
     */
    public function setShareKey($share_key): void
    {
        $this->share_key = $share_key;
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
    public function getImgKey()
    {
        return $this->img_key;
    }

    /**
     * @param mixed $img_key
     */
    public function setImgKey($img_key): void
    {
        $this->img_key = $img_key;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info): void
    {
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getShareGroup()
    {
        return $this->share_group;
    }

    /**
     * @param mixed $share_group
     */
    public function setShareGroup($share_group): void
    {
        $this->share_group = $share_group;
    }

    /**
     * @return mixed
     */
    public function getAddr()
    {
        return $this->addr;
    }

    /**
     * @param mixed $addr
     */
    public function setAddr($addr): void
    {
        $this->addr = $addr;
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
    public function getIsDelete()
    {
        return $this->is_delete;
    }

    /**
     * @param mixed $is_delete
     */
    public function setIsDelete($is_delete): void
    {
        $this->is_delete = $is_delete;
    }

    /**
     * @return mixed
     */
    public function getShareType()
    {
        return $this->share_type;
    }

    /**
     * @param mixed $share_type
     */
    public function setShareType($share_type): void
    {
        $this->share_type = $share_type;
    }

    /**
     * @return mixed
     */
    public function getUpAccount()
    {
        return $this->up_account;
    }

    /**
     * @param mixed $up_account
     */
    public function setUpAccount($up_account): void
    {
        $this->up_account = $up_account;
    }



}
