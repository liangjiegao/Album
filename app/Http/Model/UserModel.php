<?php


namespace App\Http\Model;


use App\Http\Config\CodeConf;
use App\Http\Config\RedisHeadConf;
use App\Http\Model\Impl\IUserModel;
use Illuminate\Support\Facades\DB;

class UserModel implements IUserModel
{

    private $_table = 'user_info';

    public function createUser(array $requestParams)
    {

        $email      = $requestParams['email'];

        $password   = UtilsModel::getSqlPassword($requestParams['password']);
        $account    = md5( $email . time() . rand(10000, 99999) . RedisHeadConf::getHead('account_sort') );

        $createTime = time();

        $userBean = \App::make('UserBean');

        $userBean-> setEmail($email);
        $userBean-> setPassword($password);
        $userBean-> setAccount($account);
        $userBean-> setCreateTime($createTime);

        $utils  = new InsertUpdateObjectUtils($userBean);
        $code   = $utils->insertObject($this->_table);

        return $code;
    }

    public function editUser($requestParams){

        $userBean = \App::make("UserBean");

        $account = $requestParams['account'];


        $userBean->setPhone($requestParams['phone']);
        $userBean->setEmail($requestParams['email']);
        $userBean->setBirthday(date($requestParams['birthday']));
        $userBean->setNickname($requestParams['nickname']);
        $userBean->setRemark($requestParams['remark']);

        $utils  = new InsertUpdateObjectUtils($userBean);
        $code   = $utils->updateObject($this->_table, 'account', $account);

        return $code;

    }

    public function changePassword($requestParams){

        $account        = $requestParams['account'];           // 账号
        $oldPassword    = $requestParams['old_password'];      // 旧密码
        $newPassword    = $requestParams['new_password'];      // 新密码
        $confirmPassword= $requestParams['confirm_password'];  // 确认密码

        // 验证新密码长度
        if ( strlen($newPassword) < 6) {
            return CodeConf::PASSWORD_LEN_TOO_SHORT;
        }

        // 验证新密码与确认密码是否相等
        if ( !empty($newPassword) && $newPassword == $confirmPassword ){
            return CodeConf::CONF_PASSWD_UN_EQUAL;
        }

        $md5OldPassword = UtilsModel::getSqlPassword( $oldPassword );




    }

    public function fetchUserInfo( string $uniqueKey , string $uniqueVal){

        $userInfo = DB::table($this->_table) -> select('*') -> where($uniqueKey, '=', $uniqueVal) ->get();

        $userInfo = UtilsModel::changeMysqlResultToArr($userInfo);

        if ( isset( $userInfo[0] ) ) {

            return $userInfo[0];

        }
        return [];
    }

    public function getUserInfo($requestParams){




    }


    public function saveFile(){



    }


}
