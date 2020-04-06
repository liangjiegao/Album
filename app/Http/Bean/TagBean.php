<?php


namespace App\Http\Bean;


class TagBean
{
    private  $id            ;
    private  $tag_key       ;
    private  $name          ;
    private  $create_time    ;
    private  $is_delete = 0 ;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
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




}
