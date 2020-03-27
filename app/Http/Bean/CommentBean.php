<?php


namespace App\Http\Bean;


class CommentBean
{
    private $id              ;
    private $comment_key     ;
    private $comment_info    ;
    private $pid_first       ;
    private $pid_second      ;
    private $account         ;
    private $create_time     ;
    private $is_delete = 0   ;
    private $share_key       ;

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
    public function getCommentKey()
    {
        return $this->comment_key;
    }

    /**
     * @param mixed $comment_key
     */
    public function setCommentKey($comment_key): void
    {
        $this->comment_key = $comment_key;
    }

    /**
     * @return mixed
     */
    public function getCommentInfo()
    {
        return $this->comment_info;
    }

    /**
     * @param mixed $comment_info
     */
    public function setCommentInfo($comment_info): void
    {
        $this->comment_info = $comment_info;
    }

    /**
     * @return mixed
     */
    public function getPidFirst()
    {
        return $this->pid_first;
    }

    /**
     * @param mixed $pid_first
     */
    public function setPidFirst($pid_first): void
    {
        $this->pid_first = $pid_first;
    }

    /**
     * @return mixed
     */
    public function getPidSecond()
    {
        return $this->pid_second;
    }

    /**
     * @param mixed $pid_second
     */
    public function setPidSecond($pid_second): void
    {
        $this->pid_second = $pid_second;
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



}
