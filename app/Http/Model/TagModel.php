<?php


namespace App\Http\Model;


use App\Http\Config\CodeConf;
use App\Http\Config\RedisHeadConf;
use App\Http\Config\ReturnInfoConf;
use App\Http\Model\Impl\ITagModel;
use Illuminate\Support\Facades\DB;

class TagModel implements ITagModel
{

    private $_tag       = 'tag';
    private $_img_tag   = 'img_tag';

    /**
     * @inheritDoc
     */
    public function addTag(array $params)
    {

        $imgKey     = $params['img_key'];
        $tagName    = $params['tag_name'];
        $score      = $params['score'];
        $score      = empty( $score ) ? 0.5 : $score;

        $tag = $this->getTagByName( $tagName );

        if ( empty( $tag ) ){

            // 创建tag
            $tagKey     = md5( rand(10000, 99999) . time() . $imgKey . RedisHeadConf::getHead( 'tag_key_sort' ) );

            $tagBean    = \App::make( 'TagBean' );

            $tagBean -> setTagKey( $tagKey );
            $tagBean -> setName( $tagName );
            $tagBean -> setCreateTime( time() );

            $insertObj      = new InsertUpdateObjectUtils( $tagBean );
            $insertTagCode  = $insertObj->insertObject( $this->_tag );

            if ( $insertTagCode != CodeConf::OPT_SUCCESS ){

                return $insertTagCode;

            }

        }else{

            $tagKey = $tag['tag_key'];

        }




        // 创建tag 和 img 的映射关系
        $imgTagBean    = \App::make( 'ImgTagBean' );

        $imgTagBean -> setTagKey( $tagKey );
        $imgTagBean -> setImgKey( $imgKey );
        $imgTagBean -> setScore( $score );
        $imgTagBean -> setCreateTime( time() );

        $insertObj      = new InsertUpdateObjectUtils( $imgTagBean );
        $insertTagCode  = $insertObj->insertObject( $this->_img_tag );

        if ( $insertTagCode != CodeConf::OPT_SUCCESS ){

            return ReturnInfoConf::getReturnTemp($insertTagCode, []);

        }

        return ReturnInfoConf::getReturnTemp(CodeConf::OPT_SUCCESS, ['tag_key' => $tagKey] );
    }

    private function getTagByName( string $tagName ){

        $tag = DB::table( $this->_tag )
                -> select( [ 'tag_key' ] )
                -> where( 'name', $tagName )
                -> first();
        $tag = UtilsModel::objectToArray($tag);
        return $tag;
    }

    /**
     * @inheritDoc
     */
    public function deleteTag(array $params)
    {

        $tagKey = $params['tag_key'];
        $imgKey = $params['img_key'];

        $re = DB::table( $this->_img_tag )
                -> where( [ 'img_key' => $imgKey, 'tag_key' => $tagKey ] )
                -> update( [ 'is_delete' => 1 ] );
        if ( $re === false ){

            return CodeConf::DB_OPT_FAIL;

        }
        return CodeConf::OPT_SUCCESS;
    }

}
