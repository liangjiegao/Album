<?php


namespace App\Http\Model;


use App\Http\Bean\UserRelationBean;
use App\Http\Commend\CommendModel;
use App\Http\Config\CodeConf;
use App\Http\Config\EmailContentConf;
use App\Http\Config\PublicPath;
use App\Http\Config\RedisHeadConf;
use App\Http\Config\ReturnInfoConf;
use App\Http\Model\Impl\IEmailModel;
use App\Http\Model\Impl\IUserModel;
use Illuminate\Support\Facades\DB;

class UserModel implements IUserModel
{

    private $_user_table = 'user_info';
    private $_user_relation_table = 'user_relation';

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
        $code   = $utils->insertObject($this->_user_table);

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
        $code   = $utils->updateObject($this->_user_table, 'account', $account);

        return $code;

    }



    public function changePassword($requestParams){

        $account        = $requestParams['account'];                    // 账号
        $optEmail       = $requestParams['opt_email'];               // 操作邮箱
        $oldPassword    = trim($requestParams['old_password']);         // 旧密码
        $newPassword    = trim($requestParams['new_password']);         // 新密码
        $confirmPassword= trim($requestParams['confirm_password']);     // 确认密码
        $checkCode      = trim($requestParams['check_code']);           // 邮箱验证码

        if ( !CommendModel::verificationCheckCode($optEmail, $checkCode, EmailContentConf::CH_PASSWORD) ) { // 验证码无效
            return CodeConf::CHECK_CODE_INVALID;
        }
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


        $re = DB::table($this->_user_table) -> where('account', '=', $account) -> update(['password' => $password]);

        if ( $re === false ) {
            return CodeConf::DB_OPT_FAIL;
        }

        return CodeConf::OPT_SUCCESS;
    }

    private function fetchUserInfo( string $uniqueKey , string $uniqueVal){

        $userInfo = DB::table($this->_user_table) -> select('*') -> where($uniqueKey, '=', $uniqueVal) -> get();
//        $userInfo = DB::table($this->_user_table) -> select('*') -> where($uniqueKey, '=', $uniqueVal) -> toSql();

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
        $code = (new InsertUpdateObjectUtils($userBean)) -> updateObject( $this->_user_table, 'account', $account );

        if ( $code != CodeConf::OPT_SUCCESS ) {

            return ReturnInfoConf::getReturnTemp($code);

        }

//        $url = PublicPath::getPath( 'server_root' );
        $url = str_replace( PublicPath::getPath( 'resource_head' ), PublicPath::getPath( 'server_root' ), $path . $fileName);

        $returnInfo = ['url' => $url];

        return ReturnInfoConf::getReturnTemp( $code, $returnInfo );
    }

    /**
     * @inheritDoc
     */
    public function sendChangePasswordCheckCode( array $requestParams )
    {
        $tagEmail       = $requestParams['opt_email'];
        $chPasswdCode   = CommendModel::createCheckCode($tagEmail, EmailContentConf::CH_PASSWORD);

        $emailModel = \App::make(IEmailModel::class);

        $params['tag_email']    = $tagEmail;
        $params['code']         = $chPasswdCode;
        $params['type']         = EmailContentConf::CH_PASSWORD;

        $returnInfo = $emailModel->sendEmail($params);

        return $returnInfo;
    }

    /**
     * @inheritDoc
     */
    public function applyFriend(array $params)
    {

        $applyAccount   = $params['account'];
        $friendEmail    = $params['friend_email'];

        $friendInfo = $this -> fetchUserInfo( 'email', $friendEmail );

        // 判断好友账号是否存在
        if ( empty($friendInfo) ) {
            // 好用账号不存在
            return CodeConf::USER_NON_EXISTENT;

        }
        // 好友账号
        $friendAccount  = $friendInfo['account'];

        // 如果添加的账号和自己的账号相同，则不允许
        if ( $applyAccount == $friendAccount ){

            return CodeConf::NOT_ALLOW_APPLY_SELF;

        }

        // 判断是否已经是好友关系
        $relationSelf   = $this->getRelationInfoByTowAccount( $applyAccount, $friendAccount );
        $relationFriend = $this->getRelationInfoByTowAccount( $friendAccount, $applyAccount );
        // 已提交申请, 好友未处理
        if ( empty($relationFriend) && !empty($relationSelf) ) {

            return CodeConf::ALREADY_APPLY_RELATION;

        }
        // 已提交申请, 好友已处理
        if ( !empty($relationFriend) && !empty($relationSelf) ) {

            // 已经是好友
            if ( isset($relationFriend) && $relationFriend->getIsPass() ) {

                return CodeConf::ALREADY_IS_FRIEND;

            }
            // 对方拒绝，清除对方拒绝记录，表示再次发起申请
            else {
                $code = $this->clearRefusedRelation( $relationFriend->getRelationKey() );

                if ( $code != CodeConf::OPT_SUCCESS ){

                    return $code;

                }

            }


        }

        // 处理添加逻辑

        $relationKey    = $this->buildRelationKey();

        $relationBean = \App::make('UserRelationBean');
        $relationBean->setRelationKey( $relationKey );
        $relationBean->setAccountSelf( $applyAccount );
        $relationBean->setAccountFriend( $friendAccount );
        $relationBean->setCreateTime( time() );
        $relationBean->setIsPass( 1 );

        $insertObj  = new InsertUpdateObjectUtils( $relationBean );
        $insertCode = $insertObj->insertObject( $this ->_user_relation_table );

        return $insertCode;
    }

    private function getRelationInfoByTowAccount( $accountSelf, $accountFriend ) {

        $relation = DB::table( $this->_user_relation_table )
                    -> where( 'account_self', '=', $accountSelf )
                    -> where( 'account_friend', '=', $accountFriend )
                    -> first();
        $relation = UtilsModel::objectToArray( $relation );
        if ( empty($relation) ) {

            return null;

        }
        $relationBean = \App::make('UserRelationBean');
        $relationBean = (new ObjectParse( $relationBean) ) ->parseArrToObject($relation);

        return $relationBean;
    }

    private function clearRefusedRelation( $relationKey ){
        $relationBean = \App::make('UserRelationBean');

        $insertObj  = new InsertUpdateObjectUtils( $relationBean );
        $updateCode = $insertObj->updateObject( $this->_user_relation_table, 'relation_key', $relationKey );

        return $updateCode;
    }

    /**
     * @inheritDoc
     */
    public function optFriendApply(array $params)
    {

        $account        = $params['account'];
        $relationKey    = $params['relation_key'];
        $opt            = $params['opt'];
        $pass           = $opt == 'pass' ? 1 : 0;

        $getRelationBean = $this->getRelationByRelationKey( $relationKey );

        // 是否有申请
        if ( empty( $getRelationBean ) ) {

            return CodeConf::APPLY_NOT_EXIST;

        }

        // 不能通过自己的申请
        if ( $account == $getRelationBean->getAccountSelf() ){

            return CodeConf::NOT_ALLOW_APPLY_SELF;

        }

        $relationSelf   = $this->getRelationInfoByTowAccount( $account, $getRelationBean->getAccountSelf() );

        // 判断是否已经接受或拒绝过该好友申请
        if ( !empty( $relationSelf ) ) {

            $passResult = $relationSelf->getIsPass();

            if ( $passResult == 0 ){

                return CodeConf::ALREADY_REFUSED;

            } else {

                return CodeConf::ALREADY_ACCEPT;

            }

        }

        $relationKey    = $this->buildRelationKey();

        $relationBean = \App::make('UserRelationBean');
        $relationBean->setRelationKey( $relationKey );
        $relationBean->setAccountSelf( $account );
        $relationBean->setAccountFriend( $getRelationBean->getAccountSelf() );
        $relationBean->setCreateTime( time() );
        $relationBean->setIsPass( $pass );

        $insertObj  = new InsertUpdateObjectUtils( $relationBean );
        $updateCode = $insertObj->insertObject( $this->_user_relation_table );

        return $updateCode;
    }



    private function getRelationByRelationKey( $relationKey ){

        $relation = DB::table( $this->_user_relation_table )
                    -> where( 'relation_key', '=', $relationKey )
                    ->first();
        $relation = UtilsModel::objectToArray($relation);
        if ( empty( $relation ) ) {
            return null;
        }

        $relationBean   = \App::make( 'UserRelationBean' );
        $relation       = ( new ObjectParse( $relationBean ) ) ->parseArrToObject( $relation );

        return $relation;
    }

    public function buildRelationKey(){

        return md5( time() . rand() . RedisHeadConf::getHead('relation_sort') );

    }

    /**
     * @inheritDoc
     */
    public function getOtherApplyList(array $params)
    {

        $account = $params['account'];

        // 获取申请列表
        $applyList = DB::table( $this->_user_relation_table )
                    -> leftJoin( $this -> _user_table , $this->_user_relation_table . '.account_self', '=', $this->_user_table . '.account')
                    -> select( ['relation_key', 'nickname', 'account'] )
                    -> where( 'account_friend', '=', $account )
                    -> get();
        $applyList = UtilsModel::changeMysqlResultToArr($applyList);

        return $applyList;
    }

    /**
     * @inheritDoc
     */
    public function getMyApplyList(array $params)
    {
        $account = $params['account'];

        // 获取申请列表
        $applyList = DB::table( $this->_user_relation_table )
            -> leftJoin( $this -> _user_table , $this->_user_relation_table . '.account_self', '=', $this->_user_table . '.account')
            -> select( ['relation_key', 'nickname', 'account'] )
            -> where( 'account_self', '=', $account )
            -> get();
        $applyList = UtilsModel::changeMysqlResultToArr($applyList);

        return $applyList;
    }
}
