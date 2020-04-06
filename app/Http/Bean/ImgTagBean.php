<?php


namespace App\Http\Bean;


class ImgTagBean
{
    private $id             ;
    private $tag_key        ;
    private $img_key        ;
    private $create_time     ;
    private $is_delete = 0  ;

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
    public function getTagKey()
    {
        return $this->tag_key;
    }

    /**
     * @param mixed $tag_key
     */
    public function setTagKey($tag_key): void
    {
        $this->tag_key = $tag_key;
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
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * @param mixed $creat_time
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



}
