<?php


namespace App\Http\Model;


use App\Http\Commend\CurlUtils;
use App\Http\Config\CodeConf;
use App\Http\Config\EmailContentConf;
use App\Http\Config\RedisHeadConf;
use App\Http\Config\ReturnInfoConf;
use App\Http\Model\Impl\IEmailModel;
use App\Http\Model\Impl\ITagModel;
use App\Http\Model\UtilsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use phpDocumentor\Reflection\Types\Self_;
use Tests\TestCase;

class ImgBuildTagModel
{
    private $_client_id     = "25W9MBDrHqeFnxgLkpixvhlT";
    private $_client_secret = "dPtXXhaebY8p1U6tGcXPl1pvTjsRMEOD";

    private $_get_token_base_url = "https://aip.baidubce.com/oauth/2.0/token?";

    private $_img_parse_base_url = "https://aip.baidubce.com/rest/2.0/image-classify/v2/advanced_general?access_token=%s";

    private $_img_table     = 'img_info';
    private $_tag_table     = 'tag';
    private $_img_tag_table = 'img_tag';
    private $_img_keys;


    public function __construct( $img_keys )
    {
        $this->_img_keys = $img_keys;
    }

    public function getAccessToken(){

        // 先重Redis中查看是否有缓存
        $accessToken = $this->getAccessTokenInRedis();

        if ( !empty( $accessToken ) ){

            return ReturnInfoConf::getReturnTemp( CodeConf::OPT_SUCCESS, [ 'access_token' => $accessToken ] );

        }

        // 缓存中没有，去百度图片解析平台获取
        $post_data['grant_type']        = 'client_credentials';
        $post_data['client_id']         = $this->_client_id;
        $post_data['client_secret']     = $this->_client_secret;
        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);

        $res = CurlUtils::curlPost( $this->_get_token_base_url, $post_data );

        $getTokenResult = json_decode( $res, true );

        if ( isset( $getTokenResult['error'] ) ){

            $params['tag_email']    = '1445808283@qq.com';
            $params['code']         = '';
            $params['type']         = EmailContentConf::ERROR;
            $params['set_content']  = $getTokenResult['error_description'];

            $emailModel = \App::make( IEmailModel::class );
            $emailModel->sendEmail();

            return ReturnInfoConf::getReturnTemp( CodeConf::TOKEN_GET_FAIL);
        }else{

            if ( isset( $getTokenResult['access_token'] ) ){
                $liveTime   = $getTokenResult['expires_in'];
                $token      = $getTokenResult['access_token'];
                $this->setAccessTokenInRedis( $token, $liveTime );
                return ReturnInfoConf::getReturnTemp( CodeConf::OPT_SUCCESS, [ 'access_token' => $token ] );
            }

        }

        return ReturnInfoConf::getReturnTemp( CodeConf::TOKEN_GET_FAIL);
    }

    /**
     *
     * @param string $token
     * @param int $live_time
     */
    private function setAccessTokenInRedis( string $token, int $live_time ){

        Redis::setex( RedisHeadConf::getHead( 'img_build_tag_access_token' ), $live_time, $token );

    }

    private function getAccessTokenInRedis(){

        return Redis::get( RedisHeadConf::getHead( 'img_build_tag_access_token' ) );
    }

    public function parseImg(){
        // 要解析的图片key
        $imgKeys = $this->_img_keys;

        // 获取队列中要解析的图片
        $imgInfos = $this->getImgInfo( $imgKeys );
        $account = '';
        // 遍历数据，将图片按顺序得提交到解析服务中
        foreach ($imgInfos as $item ) {
            $account    = $item['account'];
            $imgKey     = $item['img_key'];
            $imgPath    = $item['path'];
            // 解析图片,得到标签
            $parseInfo  = $this->doParse( $imgPath );
            $parseInfo  = json_decode($parseInfo, true);
            // 如果解析成功
            if ( isset(  $parseInfo['result'] ) ){
                // 获取解析结果
                $parseResult = $parseInfo['result'];
                // 将解析结果关联到图片中
                $code = $this->mapTagToImg( $imgKey, $parseResult );

                if ( $code != CodeConf::OPT_SUCCESS ){
                    return $code;
                }
            }else{
                return CodeConf::IMG_PARSE_FAIL;
            }
        }
        Redis::rpush(RedisHeadConf::getHead('upload_img_notice'), $account);
        return CodeConf::OPT_SUCCESS;
    }

    private function doParse( $imgPath ){

        $img = file_get_contents( $imgPath );
        $img = base64_encode($img);
        $accessTokenInfo = $this->getAccessToken();
        if ( $accessTokenInfo['code'] != CodeConf::OPT_SUCCESS ){

            \Log::info( "access_token 获取异常" );
            return '';
        }

        $accessToken = $accessTokenInfo['info']['access_token'];

        $bodys = array(
            'image' => $img
        );
        $url = sprintf( $this->_img_parse_base_url, $accessToken);
        $res = CurlUtils::curlPost($url, $bodys);

        return $res;
    }

    private function getImgInfo( array $imgKeys ){

        $list   = DB::table( $this->_img_table )
                    -> select( ['img_key', 'path', 'account'] )
                    -> whereIn( 'img_key', $imgKeys )
                    -> get();
        $list   = UtilsModel::changeMysqlResultToArr( $list );

        return $list;
    }

    private function mapTagToImg( string $imgKey, array $tagInfos ){

        foreach ( $tagInfos as $tagItem ){

            $tagName    = $tagItem['keyword'];
            $score      = $tagItem['score'];


            // 创建新的 tag 和 标签-图片映射关系
            $tagModel   = \App::make( ITagModel::class );
            $params = [
                'img_key'   => $imgKey,
                'tag_name'  => $tagName,
                'score'     => $score,
            ];
            $returnInfo = $tagModel->addTag( $params );
            if ( $returnInfo['code'] != CodeConf::OPT_SUCCESS ){

                return $returnInfo['code'];

            }

        }

        return CodeConf::OPT_SUCCESS;
    }


}
