<?php


namespace App\Http\Model;


use App\Http\Commend\CommendModel;
use App\Http\Config\CodeConf;
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
    private $_reg_code_live     = 60 * 5;
    private $_login_live_time   = 60 * 60 * 24;

    public function sendRegCode($requestParams){

        $tagEmail   = $requestParams['tag_email'];
        $regCode    = $this->createRegCode($tagEmail);


        $emailModel = \App::make(IEmailModel::class);

        $params['tag_email']    = $tagEmail;
        $params['code']         = $regCode;

        $returnInfo = $emailModel->sendRegEmail($params);

        return $returnInfo;
    }

    public function reg($requestParams){

        $email      = $requestParams['email'];
        $checkCode  = $requestParams['check_code'];
        $confirm    = $requestParams['confirm'];
        $password   = $requestParams['password'];

        $userModel  = \App::make(IUserModel::class);

        if ( CommendModel::verificationCheckCode($email, $checkCode) ) { //验证码有效

            // 验证确认密码和密码是否相同
            if (strlen($password) < 6){
                return ReturnInfoConf::getReturnTemp(CodeConf::PASSWORD_LEN_TOO_SHORT);
            }
            if ($confirm != $password){
                return ReturnInfoConf::getReturnTemp(CodeConf::CONF_PASSWD_UN_EQUAL);
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

    /**
     * 获取注册验证码
     * @param $email    // 注册邮箱
     * @return string   // 返回字符串类型的验证码
     */
    public function createRegCode($email) :string
    {
        $code = rand(100000, 999999);

        // 有效期5分钟
        Redis::setex( RedisHeadConf::getHead('email_reg_code') . md5($email), $this->_reg_code_live, $code);
        return  $code;
    }

    public function login(array $requestParams)
    {

        $email      = $requestParams['email'];
        $password   = $requestParams['password'];

        $userModel  = \App::make(IUserModel::class);
        // 验证用户
        $userInfo = $userModel-> fetchUserInfo( 'email', $email );
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
}
