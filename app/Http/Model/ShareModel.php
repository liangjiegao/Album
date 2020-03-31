<?php


namespace App\Http\Model;


use App\Http\Bean\DBOptBean;
use App\Http\Commend\CommentInfoMap;
use App\Http\Commend\ImageKeyUrlMapUtils;
use App\Http\Commend\UserInfoMapUtils;
use App\Http\Config\CodeConf;
use App\Http\Config\RedisHeadConf;
use App\Http\Config\ReturnInfoConf;
use App\Http\Model\Impl\IShareModel;
use Illuminate\Support\Facades\DB;

class ShareModel implements IShareModel
{

    private $_share_info_table  = 'share_info';
    private $_relation_table    = 'user_relation';
    private $_comment_table     = 'comment';

    /**
     * @inheritDoc
     */
    public function createShare(array $params)
    {
        $imgKey     = $params['img_key'];
        $info       = $params['info'];
        $group      = $params['share_group'];
        $account    = $params['account'];
        $shareType  = $params['share_type'];

        // 文案长度限制在1000字
        if ( strlen( $info ) > 1000 ) {

            return CodeConf::DATA_TOO_LONG;

        }

        $shareKey = $this->buildShareKey( $account );

        $shareInfoBean = \App::make('ShareInfoBean');

        $shareInfoBean->setShareKey( $shareKey );
        $shareInfoBean->setAccount( $account );
        $shareInfoBean->setImgKey( $imgKey );
        $shareInfoBean->setInfo( $info );
        $shareInfoBean->setShareGroup( $group );
        $shareInfoBean->setCreateTime( time() );
        $shareInfoBean->setShareType( $shareType );


        $insertObj  = new InsertUpdateObjectUtils( $shareInfoBean );
        $insertCode = $insertObj->insertObject( $this->_share_info_table );

        return $insertCode;
    }

    private function buildShareKey( $account ){


        return md5( $account . time() . rand() . RedisHeadConf::getHead('share_info_sort') );
    }

    /**
     * @inheritDoc
     */
    public function deleteShare( array $params )
    {
        $shareKey   = $params['share_key'];
        $account    = $params['account'];

        // 判断是否存在该分享
        $shareBean = $this->getShare($shareKey );
        if ( empty($shareBean) || $shareBean->getAccount() != $account ){

            return CodeConf::SHARE_NON_EXISTENT;

        }

        // 将分享设置未删除状态
        $shareBean->setIsDelete( 1 );

        $insertObj  = new InsertUpdateObjectUtils( $shareBean );
        $updateCode = $insertObj->updateObject( $this->_share_info_table, 'id', $shareBean->getId() );

        return $updateCode;
    }

    public function getShare( string $shareKey ){

        $shareInfo = DB::table( $this->_share_info_table )
                    -> select( ['*'] )
                    -> where( [ 'share_key' => $shareKey, 'is_delete' => 0 ] )
                    -> first();
        $shareInfo = UtilsModel::objectToArray( $shareInfo );

        if ( empty( $shareInfo ) ){
            return null;
        }
        $shareBean = \App::make( 'ShareInfoBean' );
        $shareBean = (new ObjectParse($shareBean)) -> parseArrToObject( $shareInfo );

        return $shareBean;
    }

    /**
     * @inheritDoc
     */
    public function getShareList(array $params)
    {
        $account    = $params['account'];
        $listType   = $params['list_type'];
        $page       = $params['page'];
        $count      = $params['count'];
        $page       = empty( $page )    | $page < 0     ? 0     : $page;
        $count      = empty( $count )   | $count < 0    ? 10    : $count;


        $dbOptBean  = \App::make( "DBOptBean" );
        $dbOptBean->setPage( $page );
        $dbOptBean->setCount( $count );

        if ( $listType == 'friend' ) {

            return $this->getFriendShare( $account, $dbOptBean );

        }elseif ( $listType == 'world' ){

            return $this->getWorldShare( $dbOptBean );
        }

        return [];
    }

    public function getFriendShare( string $account, DBOptBean $optBean ) {

        $relationTable  = $this->_relation_table;
        $shareTable     = $this->_share_info_table;

        $sqlGetMyApplyFriend    = "select account_friend from {$relationTable} where account_self = :account_self and is_pass = 1 and is_delete = 0";

        $sqlGetFriend           = "select account_self from {$relationTable} where account_self in ($sqlGetMyApplyFriend) and is_pass = 1 and is_delete = 0";

        $select                 = "share_key, account, img_key, info, create_time, up_account, share_type";
        $sqlGetShare            = "select * from {$shareTable} 
                                    where 
                                          (share_group like :user or share_type = 1 or share_type = 2 ) 
                                        and (account in ($sqlGetFriend) or account = :account)
                                        and is_delete = 0
                                    order by create_time desc
                                    limit {$optBean->getPage()}, {$optBean->getCount()}";

        $selectParams['user']           = $account;
        $selectParams['account_self']   = $account;
        $selectParams['account']        = $account;

        $list = DB::select( $sqlGetShare, $selectParams );

        $list = UtilsModel::changeMysqlResultToArr($list);

        // 映射用户信息
        $mapParams = [
            'account' => 'share_user_info'
        ];
        $list = UserInfoMapUtils::mapUserInfoByAccount( $list, $mapParams );

        $list = UserInfoMapUtils::mapNameToAccount( $list, 'up_account', 'json', 'array' );

        // 图片url映射
        $mapParams = [
            'img_key' => 'url'
        ];
        $list = ImageKeyUrlMapUtils::mapUrlByImgKey( $list, $mapParams );

        // 评论映射
        $list = CommentInfoMap::mapCommentInfoToShare( $list, 'share_key' );

        $sensitiveColumns = [
            'id', 'share_group', 'addr', 'is_delete', 'share_type'
        ];
        $list = UtilsModel::clearSensitiveInfo($list, $sensitiveColumns);

        return $list;
    }

    private function getWorldShare( DBOptBean $optBean ){

        $list = DB::table( $this->_share_info_table )
                -> select( ["share_key", "account", "img_key", "info", "create_time", "up_account", "share_type"] )
                -> where( 'share_type', '=', 2 )
                -> where( 'is_delete', '=', 0 )
                -> orderBy( 'create_time', 'desc' )
                -> forPage( $optBean->getPage(), $optBean->getCount() )
                -> get();
        $list = UtilsModel::changeMysqlResultToArr($list);

        return $list;
    }

    /**
     * @inheritDoc
     */
    public function upShare(array $params)
    {
        $account    = $params['account'];
        $shareKey   = $params['share_key'];

        $shareBean = $this->getShare( $shareKey );
        if ( empty( $shareBean ) ){

            return CodeConf::SHARE_NON_EXISTENT;

        }
        $upAccount = $shareBean->getUpAccount();
        if ( empty( $upAccount ) ){

            $upAccount = [];

        }else{

            $upAccount = json_decode(  $upAccount, true );

        }

        // 判断是否已经点赞
        foreach ($upAccount as $getAccount) {
            if ( $account == $getAccount ){

                return CodeConf::ALREADY_UP;

            }
        }

        $upAccount[] = $account;
        $shareBean->setUpAccount( json_encode($upAccount) );

        $insertObj = new InsertUpdateObjectUtils( $shareBean );

        $updateCode = $insertObj->updateObject( $this->_share_info_table, 'id', $shareBean->getId() );

        return $updateCode;
    }

    /**
     * @inheritDoc
     */
    public function commentShare(array $params)
    {

        $account            = $params['account'];
        $shareKey           = $params['share_key'];
        $commentInfo        = $params['comment_info'];
        $firstCommentKey    = $params['first_comment_key'];
        $secondCommentKey   = $params['second_comment_key'];

        // 判断分享是否存在
        $shareInfo = $this->getShare( $shareKey );
        if ( empty( $shareInfo ) ){

            return CodeConf::SHARE_NON_EXISTENT;

        }

        $pidFirst   = 0;
        $pidSecond  = 0;
        if ( !empty( $firstCommentKey ) ){

            // 获取顶级评论id
            $firstCommentBean   = $this->getCommentByCommentKey( $firstCommentKey );
            if ( !empty( $firstCommentBean ) ){

                $pidFirst = $firstCommentBean->getId();

            }

            // 获取上一级评论id
            $secondCommentBean   = $this->getCommentByCommentKey( $secondCommentKey );
            if ( !empty( $secondCommentBean ) ){

                $pidSecond = $secondCommentBean->getId();

            }

        }





        $commentKey = $this->buildCommendKey( $account );

        $commentBean = \App::make( 'CommentBean' );
        $commentBean->setCommentKey( $commentKey );
        $commentBean->setCommentInfo( $commentInfo );
        $commentBean->setPidFirst( $pidFirst );
        $commentBean->setPidSecond( $pidSecond );
        $commentBean->setAccount( $account );
        $commentBean->setCreateTime( time() );
        $commentBean->setShareKey( $shareKey );

        $insertObj  = new InsertUpdateObjectUtils( $commentBean );
        $insertCode = $insertObj->insertObject( $this->_comment_table );

        return $insertCode;
    }

    public function buildCommendKey( $account ){

        return md5( $account . time() . rand() . RedisHeadConf::getHead('share_info_sort') );
    }

    /**
     * 获取指定评论
     * @param $commentKey
     * @return mixed
     */
    public function getCommentByCommentKey( $commentKey ){

        $comment = DB::table( $this->_comment_table )
                    -> select( ["*"] )
                    -> where( 'comment_key', '=', $commentKey )
                    -> first();
        $comment = UtilsModel::objectToArray($comment);

        $commentBean = \App::make( 'CommentBean' );
        $commentBean = (new ObjectParse($commentBean)) -> parseArrToObject( $comment );

        return $commentBean;
    }

    /**
     * @inheritDoc
     */
    public function getCommentList(array $params)
    {
        $account    = $params['account'];
        $shareKey   = $params['share_key'];
        $pCommentKey= $params['p_comment_key'];
        $page       = $params['page'];
        $count      = $params['count'];
        $page       = empty( $page )    | $page < 0     ? 0     : $page;
        $count      = empty( $count )   | $count < 0    ? 10    : $count;

        // 判断分享是否存在
        $shareInfo = $this->getShare( $shareKey );
        if ( empty( $shareInfo ) ){

            return ReturnInfoConf::getReturnTemp(CodeConf::SHARE_NON_EXISTENT);

        }

        // 获取评论

        $sql = DB::table( $this->_comment_table )
                -> select( ["*"] );


        if ( !empty( $pCommentKey ) ){

            $pCommentBean = $this->getCommentByCommentKey( $pCommentKey );

            if ( !empty( $pCommentBean ) ){

                $sql = $sql -> where( 'pid_second', '=', $pCommentBean->getId() );


            }else{

                $sql = $sql -> where( 'share_key', '=', $shareKey ) -> where( 'pid_first', '=', 0 );

            }

        } else {

            $sql = $sql -> where( 'share_key', '=', $shareKey ) -> where( 'pid_first', '=', 0 );

        }

        $list = $sql    -> orderBy( 'create_time', 'desc' )
                        -> forPage( $page, $count )
                        -> get();
        $list = UtilsModel::changeMysqlResultToArr($list);

        return ReturnInfoConf::getReturnTemp(CodeConf::OPT_SUCCESS, $list );
    }


}
