<?php


namespace App\Http\Bean;


class UserRelationBean
{

    private $id            ;
    private $relation_key  ;
    private $account_self  ;
    private $account_friend;
    private $create_time    ;
    private $is_delete = 0 ;
    private $is_pass   = 2 ;

    /**
     * @return mixed
     */
    public function getId():int
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
    public function getRelationKey():string
    {
        return $this->relation_key;
    }

    /**
     * @param mixed $relation_key
     */
    public function setRelationKey($relation_key): void
    {
        $this->relation_key = $relation_key;
    }

    /**
     * @return mixed
     */
    public function getAccountSelf():string
    {
        return $this->account_self;
    }

    /**
     * @param mixed $account_self
     */
    public function setAccountSelf($account_self): void
    {
        $this->account_self = $account_self;
    }

    /**
     * @return mixed
     */
    public function getAccountFriend():string
    {
        return $this->account_friend;
    }

    /**
     * @param mixed $account_friend
     */
    public function setAccountFriend($account_friend): void
    {
        $this->account_friend = $account_friend;
    }

    /**
     * @return mixed
     */
    public function getCreateTime():int
    {
        return $this->create_time;
    }

    /**
     * @param $create_time
     */
    public function setCreateTime($create_time): void
    {
        $this->create_time = $create_time;
    }

    /**
     * @return int
     */
    public function getIsDelete(): int
    {
        return $this->is_delete;
    }

    /**
     * @param int $is_delete
     */
    public function setIsDelete(int $is_delete): void
    {
        $this->is_delete = $is_delete;
    }

    /**
     * @return int
     */
    public function getIsPass(): int
    {
        return $this->is_pass;
    }

    /**
     * @param int $is_pass
     */
    public function setIsPass(int $is_pass): void
    {
        $this->is_pass = $is_pass;
    }



}
