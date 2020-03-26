<?php


namespace App\Http\Bean;


class EmailBean
{
    private $_tar_email;        // 目标邮箱

    private $_title;            // 邮件标题

    private $_content;          // 邮件内容

    private $_files = [];       // 文件附件

    private $_form = '1445808283@qq.com';     // 发件人邮箱

    private $_form_name = '云相册';            // 发件人名



    /**
     * @return mixed
     */
    public function getTarEmail()
    {
        return $this->_tar_email;
    }

    /**
     * @param mixed $tar_email
     */
    public function setTarEmail($tar_email): void
    {
        $this->_tar_email = $tar_email;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->_title = $title;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->_content = $content;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->_files;
    }

    /**
     * @param array $files
     */
    public function setFiles(array $files): void
    {
        $this->_files = $files;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * @param mixed $form
     */
    public function setForm($form): void
    {
        $this->_form = $form;
    }

    /**
     * @return mixed
     */
    public function getFormName()
    {
        return $this->_form_name;
    }

    /**
     * @param mixed $form_name
     */
    public function setFormName($form_name): void
    {
        $this->_form_name = $form_name;
    }        // 发件人名称


}
