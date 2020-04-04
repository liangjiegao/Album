<?php


namespace App\Http\Model\Impl;


interface ILoginModel
{
    public function sendRegCode(array $params) ;

    public function sendCPCode(array $params) ;

    public function changePassword( array $params );

    public function reg(array $params) ;

    public function login(array $params) ;

    public function buildToken( string $account) : string ;
}
