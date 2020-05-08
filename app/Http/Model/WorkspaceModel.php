<?php


namespace App\Http\Model;


use App\Http\Commend\CommendModel;
use App\Http\Commend\ImageInfoMapUtils;
use App\Http\Config\CodeConf;
use App\Http\Config\ReturnInfoConf;
use App\Http\Model\Impl\IWorkspaceModel;
use Illuminate\Support\Facades\DB;

class WorkspaceModel implements IWorkspaceModel
{
    private $_dir_table = 'img_dir';
    private $_img_table = 'img_info';

    /**
     * @inheritDoc
     */
    public function getWorkspace(array $params)
    {

        $dirId      = $params['dir_id'];
        $account    = $params['account'];
        $keyword    = $params['keyword'];

        // 获取虚拟目录
        $sqlDir = DB::table( $this->_dir_table )
                    -> select(['id', 'name', 'pid'])
                    -> where( 'account', '=', $account )
                    -> where( 'is_delete', '=', 0 );
        // 判断搜索关键词，模糊匹配目录名
        if ( !empty( $keyword ) ){
            $sqlDir = $sqlDir -> where( 'name', 'like', '%' . $keyword . '%' );
        }else{
            $sqlDir = $sqlDir -> where( 'pid', '=', $dirId );
        }
        // 对目录进行排序
        $dirList = $sqlDir -> orderBy( 'create_time', 'desc' ) -> get();
        // 数据格式化
        $dirList = UtilsModel::changeMysqlResultToArr($dirList);



        // 获取图片
        $sqlImg = DB::table( $this->_img_table )
                    -> select(['id', 'img_name', 'dir_id', 'path', 'img_key' ])
                    -> where( 'account', '=', $account )
                    -> where( 'is_delete', '=', 0 );

        // 根据输入关键字进行标签搜索
        if ( !empty($keyword) ){
            $searchImgKeyList = CommendModel::getTagImgKey( [''], [$keyword] );
            $sqlImg = $sqlImg-> whereIn( 'img_key', $searchImgKeyList );
        }else{
            $sqlImg = $sqlImg->  where( 'dir_id', '=', $dirId );
        }
        // 对图片进行排序
        $imgList = $sqlImg -> orderBy( 'create_time', 'desc' ) -> get();
        // 数据格式化
        $imgList = UtilsModel::changeMysqlResultToArr($imgList);


        // 图片路径映射
        $imgList = ImageInfoMapUtils::mapUrlByImgKey( $imgList, [ 'img_key' => 'url' ] );

        // 标签映射
        $mapColumns = [
            'img_key' => 'tag_list',
        ];
        $imgList    = ImageInfoMapUtils::mapTagIntoImgInfo( $imgList, $mapColumns );

        $list = [ 'img' => $imgList, 'dir' => $dirList ];

        return ReturnInfoConf::getReturnTemp( CodeConf::OPT_SUCCESS, $list );
    }



}
