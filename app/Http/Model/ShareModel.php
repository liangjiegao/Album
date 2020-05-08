<?php


namespace App\Http\Model;


use App\Http\Bean\DBOptBean;
use App\Http\Commend\CommendModel;
use App\Http\Commend\CommentInfoMap;
use App\Http\Commend\ImageInfoMapUtils;
use App\Http\Commend\UserInfoMapUtils;
use App\Http\Config\CodeConf;
use App\Http\Config\PublicPath;
use App\Http\Config\RedisHeadConf;
use App\Http\Config\ReturnInfoConf;
use App\Http\Model\Impl\IShareModel;
use Illuminate\Support\Facades\DB;

class ShareModel implements IShareModel
{

    private $_share_info_table  = 'share_info';
    private $_relation_table    = 'user_relation';
    private $_comment_table     = 'comment';
    private $_img_table         = 'img_info';
    private $_img_tag_table     = 'img_tag';


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
        if (strlen($info) > 1000) {

            return CodeConf::DATA_TOO_LONG;

        }

        // 修改图片可见性
       $code = $this -> changeImgShareLevel( $imgKey, $shareType + 1 );
        if ( $code != CodeConf::OPT_SUCCESS ){

            return $code;

        }


        $shareKey = $this->buildShareKey($account);

        $shareInfoBean = \App::make('ShareInfoBean');

        $shareInfoBean->setShareKey($shareKey);
        $shareInfoBean->setAccount($account);
        $shareInfoBean->setImgKey($imgKey);
        $shareInfoBean->setInfo($info);
        $shareInfoBean->setShareGroup($group);
        $shareInfoBean->setCreateTime(time());
        $shareInfoBean->setShareType($shareType);


        $insertObj = new InsertUpdateObjectUtils($shareInfoBean);
        $insertCode = $insertObj->insertObject($this->_share_info_table);

        return $insertCode;
    }

    public function changeImgShareLevel( string $imgKey, string $shareLevel ){

        $update = [
            'share_level' => $shareLevel
        ];

        $re = DB::table( $this->_img_table )
                -> where( 'img_key', '=', $imgKey )
                -> update( $update );

        if ( $re === false ){

            return CodeConf::DB_OPT_FAIL;

        }

        return CodeConf::OPT_SUCCESS;
    }

    private function buildShareKey($account)
    {


        return md5($account . time() . rand() . RedisHeadConf::getHead('share_info_sort'));
    }

    /**
     * @inheritDoc
     */
    public function deleteShare(array $params)
    {
        $shareKey = $params['share_key'];
        $account = $params['account'];

        // 判断是否存在该分享
        $shareBean = $this->getShare($shareKey);
        if (empty($shareBean) || $shareBean->getAccount() != $account) {

            return CodeConf::SHARE_NON_EXISTENT;

        }

        // 将分享设置未删除状态
        $shareBean->setIsDelete(1);

        $insertObj = new InsertUpdateObjectUtils($shareBean);
        $updateCode = $insertObj->updateObject($this->_share_info_table, 'id', $shareBean->getId());

        return $updateCode;
    }

    public function getShare(string $shareKey)
    {

        $shareInfo = DB::table($this->_share_info_table)
            ->select(['*'])
            ->where(['share_key' => $shareKey, 'is_delete' => 0])
            ->first();
        $shareInfo = UtilsModel::objectToArray($shareInfo);

        if (empty($shareInfo)) {
            return null;
        }
        $shareBean = \App::make('ShareInfoBean');
        $shareBean = (new ObjectParse($shareBean))->parseArrToObject($shareInfo);

        return $shareBean;
    }

    /**
     * @inheritDoc
     */
    public function getShareList(array $params)
    {
        $account = $params['account'];
        $listType = $params['list_type'];
        $page = $params['page'];
        $count = $params['count'];
        $page = empty($page) | $page < 0 ? 0 : $page;
        $count = empty($count) | $count < 0 ? 10 : $count;


        $dbOptBean = \App::make("DBOptBean");
        $dbOptBean->setPage($page);
        $dbOptBean->setCount($count);
        $list = [];
        if ($listType == 'friend') {

            $list = $this->getFriendShare($account, $dbOptBean);

        } elseif ($listType == 'world') {

            $list = $this->getWorldShare($dbOptBean);
        }

        // 映射用户信息
        $mapParams = [
            'account' => 'share_user_info'
        ];
        $list = UserInfoMapUtils::mapUserInfoByAccount($list, $mapParams);

        foreach ($list as &$item) {
            \Log::info("??");
            \Log::info($item['share_user_info']['icon']);
            $item['share_user_info']['icon'] = str_replace( PublicPath::getPath( 'resource_head' ), PublicPath::getPath( 'server_root' ) . 'head/', $item['share_user_info']['icon']);
        }

        $list = UserInfoMapUtils::mapNameToAccount($list, 'up_account', 'json', 'array');

        // 图片url映射
        $mapParams = [
            'img_key' => 'url'
        ];
        $list = ImageInfoMapUtils::mapUrlByImgKey($list, $mapParams);

        // 评论映射
        $list = CommentInfoMap::mapCommentInfoToShare($list, 'share_key');

        $sensitiveColumns = [
            'id', 'share_group', 'addr', 'is_delete', 'share_type'
        ];
        $list = UtilsModel::clearSensitiveInfo($list, $sensitiveColumns);

        return $list;
    }

    public function getFriendShare(string $account, DBOptBean $optBean)
    {
        // 好友关系表
        $relationTable  = $this->_relation_table;
        // 分享记录表
        $shareTable     = $this->_share_info_table;
        // 获取好友子查询
        $sqlGetMyApplyFriend = "select account_friend from {$relationTable} where account_self = :account_self 
                                   and is_pass = 1 and is_delete = 0";
        $sqlGetFriend = "select account_self from {$relationTable} where account_self in ($sqlGetMyApplyFriend) 
                                 and is_pass = 1 and is_delete = 0";
        // 获取分享记录主查询
        $select = "share_key, account, img_key, info, create_time, up_account, share_type";
        $sqlGetShare = "select * from {$shareTable} 
                        where( ((share_type = 1 or share_type = 2) and (account in ($sqlGetFriend) or account = :self0) ) 
                                or ( share_type = 0 and account = :self1 ) ) and is_delete = 0
                                    order by create_time desc limit {$optBean->getPage()}, {$optBean->getCount()}";
        // 查询参数
        $selectParams['self0']          = $account;
        $selectParams['account_self']   = $account;
        $selectParams['self1']      = $account;
        // 执行查询
        $list = DB::select($sqlGetShare, $selectParams);
        // 数据格式化
        $list = UtilsModel::changeMysqlResultToArr($list);



        return $list;
    }

    private function getWorldShare(DBOptBean $optBean)
    {
        // 系统共享查询
        $list = DB::table($this->_share_info_table)
            ->select(["share_key", "account", "img_key", "info", "create_time", "up_account", "share_type"])
            ->where('share_type', '=', 2)
            ->where('is_delete', '=', 0)
            ->orderBy('create_time', 'desc')
            ->forPage($optBean->getPage(), $optBean->getCount())
            ->get();
        // 数据格式化
        $list = UtilsModel::changeMysqlResultToArr($list);

        return $list;
    }

    /**
     * @inheritDoc
     */
    public function upShare(array $params)
    {
        $account = $params['account'];      // 账号
        $shareKey = $params['share_key'];   // 分享key

        // 获取分享信息
        $shareBean = $this->getShare($shareKey);
        // 判断分享是否存在
        if (empty($shareBean)) {
            // 分享不存在
            return CodeConf::SHARE_NON_EXISTENT;

        }

        // 判断账号是否存存在
        $upAccount = $shareBean->getUpAccount();
        if (empty($upAccount)) {

            $upAccount = [];

        } else {

            $upAccount = json_decode($upAccount, true);

        }

        // 判断是否已经点赞
        foreach ($upAccount as $getAccount) {
            if ($account == $getAccount) {
                // 已经点过赞了
                return CodeConf::ALREADY_UP;

            }
        }
        // 未点赞，将点赞账号记录在该分享信息中
        $upAccount[] = $account;
        $shareBean->setUpAccount(json_encode($upAccount));
        // 插入点赞信息
        $insertObj = new InsertUpdateObjectUtils($shareBean);
        $updateCode = $insertObj->updateObject($this->_share_info_table, 'id', $shareBean->getId());

        return $updateCode;
    }

    /**
     * @inheritDoc
     */
    public function commentShare(array $params)
    {

        $account            = $params['account'];           // 账号
        $shareKey           = $params['share_key'];         // 分享key
        $commentInfo        = $params['comment_info'];      // 评论内容
        $firstCommentKey    = $params['first_comment_key'];
        $secondCommentKey   = $params['second_comment_key'];

        // 判断分享是否存在
        $shareInfo = $this->getShare($shareKey);
        if (empty($shareInfo)) {

            return CodeConf::SHARE_NON_EXISTENT;

        }

        // 获取父级评论key
        $pidFirst = 0;
        $pidSecond = 0;
        if (!empty($firstCommentKey)) {

            // 获取顶级评论id
            $firstCommentBean = $this->getCommentByCommentKey($firstCommentKey);
            if (!empty($firstCommentBean)) {

                $pidFirst = $firstCommentBean->getId();

            }

            // 获取上一级评论id
            $secondCommentBean = $this->getCommentByCommentKey($secondCommentKey);
            if (!empty($secondCommentBean)) {

                $pidSecond = $secondCommentBean->getId();

            }

        }

        // 创建评论对象
        $commentKey = $this->buildCommendKey($account);

        $commentBean = \App::make('CommentBean');
        $commentBean->setCommentKey($commentKey);
        $commentBean->setCommentInfo($commentInfo);
        $commentBean->setPidFirst($pidFirst);
        $commentBean->setPidSecond($pidSecond);
        $commentBean->setAccount($account);
        $commentBean->setCreateTime(time());
        $commentBean->setShareKey($shareKey);
        // 记录评论
        $insertObj = new InsertUpdateObjectUtils($commentBean);
        $insertCode = $insertObj->insertObject($this->_comment_table);

        return $insertCode;
    }

    public function buildCommendKey($account)
    {

        return md5($account . time() . rand() . RedisHeadConf::getHead('share_info_sort'));
    }

    /**
     * 获取指定评论
     * @param $commentKey
     * @return mixed
     */
    public function getCommentByCommentKey($commentKey)
    {

        $comment = DB::table($this->_comment_table)
            ->select(["*"])
            ->where('comment_key', '=', $commentKey)
            ->first();
        $comment = UtilsModel::objectToArray($comment);

        $commentBean = \App::make('CommentBean');
        $commentBean = (new ObjectParse($commentBean))->parseArrToObject($comment);

        return $commentBean;
    }

    /**
     * @inheritDoc
     */
    public function getCommentList(array $params)
    {
        $account = $params['account'];
        $shareKey = $params['share_key'];
        $pCommentKey = $params['p_comment_key'];
        $page = $params['page'];
        $count = $params['count'];
        $page = empty($page) | $page < 0 ? 0 : $page;
        $count = empty($count) | $count < 0 ? 10 : $count;

        // 判断分享是否存在
        $shareInfo = $this->getShare($shareKey);
        if (empty($shareInfo)) {

            return ReturnInfoConf::getReturnTemp(CodeConf::SHARE_NON_EXISTENT);

        }

        // 获取评论

        $sql = DB::table($this->_comment_table)
            ->select(["*"]);


        if (!empty($pCommentKey)) {

            $pCommentBean = $this->getCommentByCommentKey($pCommentKey);

            if (!empty($pCommentBean)) {

                $sql = $sql->where('pid_second', '=', $pCommentBean->getId());


            } else {

                $sql = $sql->where('share_key', '=', $shareKey)->where('pid_first', '=', 0);

            }

        } else {

            $sql = $sql->where('share_key', '=', $shareKey)->where('pid_first', '=', 0);

        }

        $list = $sql->orderBy('create_time', 'desc')
            ->forPage($page, $count)
            ->get();
        $list = UtilsModel::changeMysqlResultToArr($list);

        return ReturnInfoConf::getReturnTemp(CodeConf::OPT_SUCCESS, $list);
    }


    /**
     * @inheritDoc
     */
    public function getWorldImgList(array $params)
    {
        $page       = $params['page'];      // 当前页
        $count      = $params['count'];     // 一页显示数量
        $tabInfo    = $params['tab_info'];  // 标签信息
        $keyword    = $params['keyword'];   // 搜素关键词
        $account    = isset( $params['account'] ) ? $params['account'] : '';   // 用户账号
        $page       = empty($page)  | $page     < 0 ? 0     : $page;
        $count      = empty($count) | $count    < 0 ? 10    : $count;

        // 获取用户经常搜索的图片key
        $hobbyImgKeys = $this->getUserOptImgKeys( $account );
        $hobbyImg    = DB::table( $this->_img_table )
                        -> select( [ $this->_img_table . '.img_key', 'path' ] )
                        -> where( 'is_delete','=', 0 )
                        -> where( 'share_level', '=' , 3 )
                        -> whereIn( 'img_key', $hobbyImgKeys )
                        -> forPage($page, $count)
                        -> get();
        $hobbyImg    = UtilsModel::changeMysqlResultToArr( $hobbyImg );
        \Log::info($hobbyImg);
        $imgList    = [];
        if ( count($hobbyImg) < $count ){

            $count = $count - count($hobbyImg);

            // 图片获取主查询
            $sql    = DB::table( $this->_img_table )
                -> select( [ $this->_img_table . '.img_key', 'path' ] )
                -> where( 'is_delete','=', 0 )
                -> where( 'share_level', '=' , 3 )
                -> whereIn( 'img_key', $hobbyImgKeys, 'or' );

            // 标签搜索
            if ( !empty($tabInfo) || !empty($keyword) ){
                $searchImgKeyList = CommendModel::getTagImgKey( $tabInfo, $keyword );
                $sql = $sql-> whereIn( 'img_key', $searchImgKeyList );
            }

            // 对查询进行分页查询和排序
            $imgList = $sql -> forPage($page, $count)
                        -> get();
            // 格式化数据
            $imgList    = UtilsModel::changeMysqlResultToArr( $imgList );
        }
        $imgList = array_merge( $hobbyImg, $imgList );


        // 标签映射
        $mapColumns = [
            'img_key' => 'tag_list',
        ];
        $imgList    = ImageInfoMapUtils::mapTagIntoImgInfo( $imgList, $mapColumns );

        // 图片路径映射
        $imgList = ImageInfoMapUtils::mapUrlByImgKey( $imgList, [ 'img_key' => 'url' ] );

        // 记录用户搜索记录
        $code = $this->recordSearch( $params );
        if ( $code != CodeConf::OPT_SUCCESS ){
            return $code;
        }

        return $imgList;
    }

    public function getUserOptImgKeys( $account ){

        // 如果有登录，按账号查
        if ( !empty( $account ) ){

            $userInfo   = (new UserModel()) -> getUserInfo( [ 'unique_key' => 'account', 'unique_val' => $account] );
            $userId     = $userInfo['id'];
            $search     = DB::table( 'user_option' ) -> select(['search']) -> where('user_id', $userId) -> get();
            $search     = UtilsModel::changeMysqlResultToArr( $search );
            $search     = array_column( $search, 'search' );


        }
        // 如果没有登录，按照ip查
        else{
            $ip         = $this->getIp();
            $search     = DB::table( 'user_option' ) -> select(['search']) -> where('ip', $ip) -> get();
            $search     = UtilsModel::changeMysqlResultToArr( $search );
            $search     = array_column( $search, 'search' );
//            $searchImgKeyList = CommendModel::getTagImgKey( $search, $search );
        }
        $searchStr  = implode('', $search);
        $searchImgKeyList = CommendModel::getTagImgKey( $searchStr, $searchStr );

        return $searchImgKeyList;
    }

    private function recordSearch( array $params ){

        $account    = isset( $params['account'] ) ? $params['account'] : '';
        $IP         = $this->getIp();
        $tabInfo    = $params['tab_info'];
        $keyword    = $params['keyword'];

        $userId     = 0;
        if ( !empty( $account ) ){
            $userInfo   = (new UserModel()) -> getUserInfo( [ 'unique_key' => 'account', 'unique_val' => $account] );
            $userId     = $userInfo['id'];

        }
        $data = [];
        if ( !empty( $tabInfo ) ){
            $data[] =  [
                'search'        => $tabInfo,
                'ip'            => $IP,
                'user_id'       => $userId,
                'create_time'   => time(),
            ];
        }
        if ( !empty( $keyword ) ){
            $data = [
                'search'        => $keyword,
                'ip'            => $IP,
                'user_id'       => $userId,
                'create_time'   => time(),
            ];
        }
        $re = DB::table( 'user_option' ) -> insert($data);

        if ( $re === false ){

            return CodeConf::DB_OPT_FAIL;

        }
        return CodeConf::OPT_SUCCESS;
    }

    private function getIp()
    {
        static $ip = '';

        $ip = $_SERVER['REMOTE_ADDR'];

        if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {

            $ip = $_SERVER['HTTP_CDN_SRC_IP'];

        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {

            $ip = $_SERVER['HTTP_CLIENT_IP'];

        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {

            foreach ($matches[0] AS $xip) {

                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {

                    $ip = $xip;

                    break;

                }

            }

        }

        return $ip;
    }
}
