<?php


namespace App\Http\Bean;


class ImageBean
{

    private $id             ;
    private $img_key        ;
    private $account        ;
    private $share_level    ;
    private $path           ;
    private $create_time    ;
    private $is_delete = 0  ;
    private $dir_id         ;
    private $img_name       ;

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
    public function getShareLevel()
    {
        return $this->share_level;
    }

    /**
     * @param mixed $share_level
     */
    public function setShareLevel($share_level): void
    {
        $this->share_level = $share_level;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
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
     * @return mixed
     */
    public function getDirId()
    {
        return $this->dir_id;
    }

    /**
     * @param mixed $dir_id
     */
    public function setDirId($dir_id): void
    {
        $this->dir_id = $dir_id;
    }

    /**
     * @return mixed
     */
    public function getImgName()
    {
        return $this->img_name;
    }

    /**
     * @param mixed $img_name
     */
    public function setImgName($img_name): void
    {
        $this->img_name = $img_name;
    }





}
