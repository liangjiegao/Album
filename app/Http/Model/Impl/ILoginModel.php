<?php


namespace App\Http\Model\Impl;


interface ILoginModel
{
    public function sendRegCode(array $params) ;

    public function reg(array $params) ;

    public function login(array $params) ;

    public function buildToken( string $account) : string ;
}
