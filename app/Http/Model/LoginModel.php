<?php


namespace App\Http\Model;


use App\Http\Commend\CommendModel;
use App\Http\Config\CodeConf;
use App\Http\Config\EmailContentConf;
use App\Http\Config\RedisHeadConf;
use App\Http\Config\ReturnInfoConf;
use App\Http\Model\Impl\IEmailModel;
use App\Http\Model\Impl\ILoginModel;
use App\Http\Model\Impl\IUserModel;
use Hamcrest\Util;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class LoginModel implements ILoginModel
{
    private $_login_live_time   = 60 * 60 * 24;
    private $_user_table        = 'user_info';

    public function sendRegCode($requestParams){

        $tagEmail   = $requestParams['tag_email'];
        $regCode    = CommendModel::createCheckCode($tagEmail, EmailContentConf::REG);


        $emailModel = \App::make(IEmailModel::class);

        $params['tag_email']    = $tagEmail;
        $params['code']         = $regCode;
        $params['type']         = EmailContentConf::REG;

        $returnInfo = $emailModel->sendEmail($params);

        return $returnInfo;
    }

    public function sendCPCode(array $params)
    {
        $tagEmail   = $params['tag_email'];
        $cpCode    = CommendModel::createCheckCode($tagEmail, EmailContentConf::CH_PASSWORD);


        $emailModel = \App::make(IEmailModel::class);

        $params['tag_email']    = $tagEmail;
        $params['code']         = $cpCode;
        $params['type']         = EmailContentConf::CH_PASSWORD;

        $returnInfo = $emailModel->sendEmail($params);

        return $returnInfo;
    }

    public function reg($requestParams){

        $email      = $requestParams['email'];
        $checkCode  = $requestParams['check_code'];
        $confirm    = $requestParams['confirm'];
        $password   = $requestParams['password'];

//        $userModel  = \App::make(IUserModel::class);

        if ( CommendModel::verificationCheckCode($email, $checkCode, EmailContentConf::REG) ) { //验证码有效

            // 验证确认密码和密码是否相同
            if (strlen($password) < 6){
                return ReturnInfoConf::getReturnTemp(CodeConf::PASSWORD_LEN_TOO_SHORT);
            }
            if ($confirm != $password){
                return ReturnInfoConf::getReturnTemp(CodeConf::CONF_PASSWD_UN_EQUAL);
            }

            // 验证用户是否存在
            $params['unique_key'] = 'email';
            $params['unique_val'] = $email;
            $userModel  = \App::make(IUserModel::class);
            $userInfo   = $userModel->getUserInfo( $params );

            if ( !empty( $userInfo ) ){

                return ReturnInfoConf::getReturnTemp(CodeConf::EMAIL_ALREADY_REG);

            }

            //创建用户
            $code = $userModel->createUser($requestParams);

            if ($code == 10000){
                // 删除缓存的验证码
                Redis::del( RedisHeadConf::getHead('email_reg_code') . md5($email) );
            }




            return ReturnInfoConf::getReturnTemp($code);
        }else{

            // 验证码无效
            return ReturnInfoConf::getReturnTemp(CodeConf::CHECK_CODE_INVALID);
        }

    }


    public function login(array $requestParams)
    {
        // 用户邮箱
        $email      = $requestParams['email'];
        // 用户密码
        $password   = $requestParams['password'];
        $userModel  = \App::make(IUserModel::class);
        // 验证用户是否存在
        $userInfo = $userModel-> getUserInfo( [ 'unique_key' => 'email', 'unique_val' => $email ] );
        if ( !empty($userInfo) ){
            // 存在用户，验证密码
            if ( UtilsModel::getSqlPassword($password) == $userInfo['password'] ){
                $account    = $userInfo['account'];
                $token      = $this->buildToken( $account );
                // 密码正确
                return ReturnInfoConf::getReturnTemp(CodeConf::OPT_SUCCESS, ['token' => $token]);
            }else{
                // 密码错误
                return ReturnInfoConf::getReturnTemp(CodeConf::LOGIN_PASSWD_MISMATCH);
            }
        }else{
            // 用户不存在
            return ReturnInfoConf::getReturnTemp(CodeConf::USER_UN_EXIST);
        }
    }


    public function buildToken(string $account) :string
    {

        $token = md5( $account . time() * rand( 10000, 99999 ) . RedisHeadConf::getHead( 'login_token_sort' ) );

        Redis::setex(RedisHeadConf::getHead('login_token') . $token, $this->_login_live_time, $account );

        return $token;
    }


    public function changePassword( array $params )
    {

        $email      =   $params['email'];
        $checkCode  =   $params['check_code'];
        $password   =   $params['password'];

        if ( CommendModel::verificationCheckCode($email, $checkCode, EmailContentConf::CH_PASSWORD) ) { //验证码有效

            $userModel = \App::make( IUserModel::class );

            $getParams = [
                'unique_key' => 'email',
                'unique_val' => $email,
            ];

            $userInfo       = $userModel->getUserInfo( $getParams );

            if ( empty( $userInfo ) ){

                return CodeConf::USER_UN_EXIST;

            }

            $newPassword    = UtilsModel::getSqlPassword($password);

            $re = DB::table($this->_user_table)
                    -> where('email', '=', $email )
                    -> update(['password' => $newPassword]);

            if ( $re === false ) {
                return CodeConf::DB_OPT_FAIL;
            }

            // 删除缓存的验证码
            Redis::del( RedisHeadConf::getHead('email_change_password_code') . md5($email) );

        }else{
            return CodeConf::CHECK_CODE_INVALID;
        }


        return CodeConf::OPT_SUCCESS;

    }

}
