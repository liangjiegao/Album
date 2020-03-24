<?php


namespace App\Http\Model;


use App\Http\Commend\CommendModel;
use App\Http\Config\CodeConf;
use App\Http\Config\PublicPath;
use App\Http\Config\RedisHeadConf;
use App\Http\Config\ReturnInfoConf;
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

        $account        = $requestParams['account'];                    // 账号
        $oldPassword    = trim($requestParams['old_password']);         // 旧密码
        $newPassword    = trim($requestParams['new_password']);         // 新密码
        $confirmPassword= trim($requestParams['confirm_password']);     // 确认密码

        // 验证新密码长度
        if ( strlen($newPassword) < 6) {
            return CodeConf::PASSWORD_LEN_TOO_SHORT;
        }

        // 验证新密码与确认密码是否相等
        if ( $newPassword != $confirmPassword ){
            return CodeConf::CONF_PASSWD_UN_EQUAL;
        }
        // 验证新旧密码是否相等
        $md5OldPassword = UtilsModel::getSqlPassword( $oldPassword );
        $userInfo       = $this->fetchUserInfo('account', $account);
        if ( $userInfo['password'] != $md5OldPassword ) {

            return CodeConf::LOGIN_PASSWD_MISMATCH;

        }
        $newPassword = UtilsModel::getSqlPassword($newPassword);
        // 修改密码
        $code = $this->doChangePassword($account, $newPassword);

        return $code;
    }

    private function doChangePassword( $account, $password ) {


        $re = DB::table($this->_table) -> where('account', '=', $account) -> update(['password' => $password]);

        if ( $re === false ) {
            return CodeConf::DB_OPT_FAIL;
        }

        return CodeConf::OPT_SUCCESS;
    }

    private function fetchUserInfo( string $uniqueKey , string $uniqueVal){

        $userInfo = DB::table($this->_table) -> select('*') -> where($uniqueKey, '=', $uniqueVal) -> get();
//        $userInfo = DB::table($this->_table) -> select('*') -> where($uniqueKey, '=', $uniqueVal) -> toSql();

        $userInfo = UtilsModel::changeMysqlResultToArr($userInfo);

        if ( isset( $userInfo[0] ) ) {

            return $userInfo[0];

        }
        return [];
    }

    public function getUserInfo( array $requestParams){

        $uniqueKey = $requestParams['unique_key'];
        $uniqueVal = $requestParams['unique_val'];

        $userInfo = $this->fetchUserInfo($uniqueKey, $uniqueVal);

        // 去除敏感字段
        $sensitiveColumns = [
            'id', 'last_login_ip', 'last_login_time'
        ];
        $userInfo = UtilsModel::clearSensitiveInfo([$userInfo], $sensitiveColumns)[0];

        return $userInfo;
    }



    /**
     * @inheritDoc
     */
    public function changeHeadIcon(array $params)
    {
        $account = $params['account'];

        // 获取原始文件名
        $originFileName = $_FILES["file"]["name"];
        $file           = $_FILES["file"]["tmp_name"];

        // 创建文件名
        $fileName   = md5($originFileName . time() . rand(100000, 999999));
        $fileFormat = strstr( $originFileName, '.'); // 后缀
        $fileName   .= $fileFormat;

        $path       = PublicPath::getPath( 'resource_head' ) ;

        $userBean   = \App::make('UserBean' );
        $userBean->setIcon( $path . $fileName );


        $code = CommendModel::saveFile( $file, $path, $fileName );

        if ( $code != CodeConf::OPT_SUCCESS ) {

            return ReturnInfoConf::getReturnTemp($code);

        }

        // 保存数据库字段
        $code = (new InsertUpdateObjectUtils($userBean)) -> updateObject( $this->_table, 'account', $account );

        if ( $code != CodeConf::OPT_SUCCESS ) {

            return ReturnInfoConf::getReturnTemp($code);

        }

//        $url = PublicPath::getPath( 'server_root' );
        $url = str_replace( PublicPath::getPath( 'resource_head' ), PublicPath::getPath( 'server_root' ), $path . $fileName);

        $returnInfo = ['url' => $url];

        return ReturnInfoConf::getReturnTemp( $code, $returnInfo );
    }
}
