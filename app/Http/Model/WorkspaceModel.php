<?php


namespace App\Http\Model;


use App\Http\Commend\ImageKeyUrlMapUtils;
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

        // 获取文件夹
        $sqlDir = DB::table( $this->_dir_table )
            -> select(['id', 'name', 'pid'])
            -> where( 'account', '=', $account )
            -> where( 'is_delete', '=', 0 )
            -> where( 'pid', '=', $dirId );
//        if ( $dirId != 0 ){
//            $sqlDir    = $sqlDir -> where( 'id', '=', $dirId );
//        }
        $dirList = $sqlDir-> orderBy( 'create_time', 'desc' ) -> get();

        $dirList = UtilsModel::changeMysqlResultToArr($dirList);

        // 获取图片
        $sqlImg = DB::table( $this->_img_table )
            -> select(['id', 'img_name', 'dir_id', 'path', 'img_key' ])
            -> where( 'account', '=', $account )
            -> where( 'is_delete', '=', 0 )
            -> where( 'dir_id', '=', $dirId );
//        if ( $dirId != 0 ) {
//            $sqlImg = $sqlImg -> where( 'dir_id', '=', $dirId );
//
//        }
        $imgList = $sqlImg -> orderBy( 'create_time', 'desc' ) -> get();
        $imgList = UtilsModel::changeMysqlResultToArr($imgList);

        $imgList = ImageKeyUrlMapUtils::mapUrlByImgKey( $imgList, [ 'img_key' => 'url' ] );

        $list = [ 'img' => $imgList, 'dir' => $dirList ];

        return ReturnInfoConf::getReturnTemp( CodeConf::OPT_SUCCESS, $list );
    }
}
