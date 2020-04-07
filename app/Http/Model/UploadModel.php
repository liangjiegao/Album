<?php


namespace App\Http\Model;


use App\Http\Commend\CommendModel;
use App\Http\Config\CodeConf;
use App\Http\Config\PublicPath;
use App\Http\Config\RedisHeadConf;
use App\Http\Config\ReturnInfoConf;
use App\Http\Model\Impl\IUploadModel;
use App\Http\Model\Impl\IUserModel;
use Illuminate\Support\Facades\DB;

class UploadModel implements IUploadModel
{
    private $_image_table   = 'img_info';
    private $_dir_table     = 'img_dir';

    /**
     * @inheritDoc
     */
    public function uploadImg(array $params): array
    {
        $account    = $params['account'];
        $dirId      = $params['dir_id'];

        if ( !$this->checkDirIsUser( $dirId, $account ) && $dirId != 0 ) {
            return ReturnInfoConf::getReturnTemp(CodeConf::DIR_NOT_EXIST);
        }

        if ( empty( $_FILES ) ){

            return ReturnInfoConf::getReturnTemp(CodeConf::UPLOAD_FILE_EMPTY);

        }

        $files  = is_array($_FILES["file"]["tmp_name"]) ? $_FILES["file"]["tmp_name"]   : [$_FILES["file"]["tmp_name"]] ;
        $errors = is_array($_FILES["file"]["error"])    ? $_FILES["file"]["error"]      : [$_FILES["file"]["error"]]    ;
        $sizes  = is_array($_FILES["file"]["size"])     ? $_FILES["file"]["size"]       : [$_FILES["file"]["size"]]     ;
        $names  = is_array($_FILES["file"]["name"])     ? $_FILES["file"]["name"]       : [$_FILES["file"]["name"]]     ;

        if ( !is_array( $files ) ) {

            return ReturnInfoConf::getReturnTemp(CodeConf::PARAMS_UNAVAILABLE);

        }
        $path       = '';
        $fileName   = '';
        $imgKeys    = [];
        $savePaths  = [];
        $saveObj    = [];
        foreach ($files as $index => $file) {

            $originFileName = $names[$index];
            // 创建文件名
            $fileName   = md5($originFileName . time() . rand(100000, 999999));
            $fileFormat = strstr( $originFileName, '.'); // 后缀
            $fileName   .= $fileFormat;

            $path       = PublicPath::getPath( 'resource_img' ) ;
            $path       = $path . $account . DIRECTORY_SEPARATOR;

            // 保存图片
            $saveCode   = CommendModel::saveFile($file, $path, $fileName);


            // 如果文件保存失败，则删除同一批次保存的文件
            if ( $saveCode != CodeConf::OPT_SUCCESS ) {

                $delCode = $this->clearPathsImg( $savePaths );

                // 如果删除失败，则返回系统异常
                if ( $delCode != CodeConf::OPT_SUCCESS ) {

                    return ReturnInfoConf::getReturnTemp(CodeConf::SYSTEM_EXCEPTION);

                }

                return ReturnInfoConf::getReturnTemp(CodeConf::FILE_SAVE_FAIL);
            }
            $savePaths[]= $path . $fileName;

            $imageBean  = \App::make('ImageBean');
            $imgKey     = md5( time() . rand(10000, 99999) . RedisHeadConf::getHead('img_key_sort') );

            $imageBean->setImgKey($imgKey);
            $imageBean->setDirId($dirId);
            $imageBean->setAccount($account);
            $imageBean->setShareLevel(1);           // 默认私有
            $imageBean->setPath($path . $fileName);
            $imageBean->setCreateTime(time());
            $imageBean->setImgName( $originFileName );

            $saveObj[] = $imageBean;
            $imgKeys[] = $imgKey;
        }

        DB::beginTransaction();

        // 全部文件保存到文件系统成功后，插入数据库
        $insertObjUtils = new InsertUpdateObjectUtils( $saveObj );
        $insertCode     = $insertObjUtils->insertObjectBatch( $this->_image_table );

        // 数据库插入失败，删除保存的图片
        if ( $insertCode != CodeConf::OPT_SUCCESS ) {

            $delCode = $this->clearPathsImg( $savePaths );

            // 如果删除失败，则返回系统异常
            if ( $delCode != CodeConf::OPT_SUCCESS ) {
                DB::rollBack();
                return ReturnInfoConf::getReturnTemp(CodeConf::SYSTEM_EXCEPTION);

            }
            DB::rollBack();
            return ReturnInfoConf::getReturnTemp(CodeConf::DB_OPT_FAIL);
        }

        // 图片标签解析
        $model  = new ImgBuildTagModel( $imgKeys );
        $code   = $model -> parseImg();

        if ( $code != CodeConf::OPT_SUCCESS ){

            DB::rollBack();
            return ReturnInfoConf::getReturnTemp( $code );

        }

        DB::commit();
        //操作成功
        return ReturnInfoConf::getReturnTemp(CodeConf::OPT_SUCCESS, ['url' => $path . '/' . $fileName]);
    }

    private function checkDirIsUser( $dirId, $account ) {

        $count = DB::table( $this->_dir_table ) -> where( [ 'id' => $dirId, 'account' => $account ] ) -> count();

        return $count > 0;
    }

    /**
     * 删除文件
     * @param $paths
     * @return int
     */
    private function clearPathsImg( $paths ) {

        foreach ($paths as $path) {

            // 删除失败
            if ( !CommendModel::delFile($path) ) {

                return CodeConf::FILE_DEL_FAIL;

            }

        }

        return CodeConf::OPT_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public function uploadPackage(array $params): string
    {
        // TODO: Implement uploadPackage() method.
    }

    /**
     * @inheritDoc
     */
    public function createDir(array $params): string
    {

        $account = $params['account'];
        $dirName = $params['dir_name'];
        $pid     = $params['pid'];

        $imageDirBean = \App::make('ImageDirBean');

        $imageDirBean->setName($dirName);
        $imageDirBean->setAccount($account);
        $imageDirBean->setPid($pid);
        $imageDirBean->setCreateTime( time() );

        $insertObj  = new InsertUpdateObjectUtils($imageDirBean);
        $insertCode = $insertObj->insertObject( $this->_dir_table );

        return  $insertCode;
    }



    /**
     * @inheritDoc
     */
    public function deleteDir(array $params): string
    {

        $dirId      = $params['dir_id'];
        $account    = $params['account'];

        if ( $this->dirExist( $dirId, $account ) ){

            $imageDirBean = \App::make('ImageDirBean');
            $imageDirBean->setIsDelete(1);

            $imageBean  = \App::make('ImageBean');
            $imageBean->setIsDelete(1);

            DB::beginTransaction();

            // 将文件夹的图片设为删除状态
            $updateObj  = new InsertUpdateObjectUtils($imageBean);
            $updateCode = $updateObj->updateObject( $this->_image_table, 'dir_id', $dirId );

            if ( $updateCode != CodeConf::OPT_SUCCESS ){
                DB::rollBack();
                return $updateCode;
            }

            $updateObj  = new InsertUpdateObjectUtils($imageDirBean);
            $updateCode = $updateObj->updateObject( $this->_dir_table, 'id', $dirId );

            if ( $updateCode != CodeConf::OPT_SUCCESS ){
                DB::rollBack();
            }

            DB::commit();
            return $updateCode;
        }

        return CodeConf::DIR_NOT_EXIST;
    }

    private function dirExist ( $dirId, $account ){

        $count = DB::table( $this->_dir_table )
                -> where('id', '=', $dirId)
                -> where( 'account', '=', $account )
                -> count();

        return $count > 0;
    }

    /**
     * @inheritDoc
     */
    public function deleteImg( array $params ): string
    {

        $imageKey   = $params['img_key'];
        $account    = $params['account'];

        if ( $this -> imageExit( $imageKey, $account ) ) {

            $imageBean = \App::make('ImageBean');
            $imageBean->setIsDelete(1);
            $imageBean->setDirId(0);

            // 将文件夹的图片设为删除状态
            $updateObj = new InsertUpdateObjectUtils($imageBean);
            $updateCode = $updateObj->updateObject($this->_image_table, 'img_key', $imageKey);

            if ($updateCode != CodeConf::OPT_SUCCESS) {
                return $updateCode;
            }

            return CodeConf::OPT_SUCCESS;
        }

        return CodeConf::IMG_NOT_EXIST;
    }

    private function imageExit( $imageKey, $account ){

        $count = DB::table( $this->_image_table )
                -> where('img_key', '=', $imageKey)
                -> where('account', '=', $account)
                -> count();

        return $count > 0;

    }
}
