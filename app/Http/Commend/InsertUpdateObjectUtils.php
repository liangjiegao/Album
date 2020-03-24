<?php


namespace App\Http\Model;


use App\Http\Config\CodeConf;
use Illuminate\Support\Facades\DB;

class InsertUpdateObjectUtils
{
    private $_object;

    public function __construct($object)
    {
        $this->_object = $object;
    }

    public function insertObject($table){

        $insertArr = (new ObjectParse($this->_object))->parseObjectToArr();

        $re = DB::table($table) -> insert ($insertArr);

        if ($re === false){
            return CodeConf::DB_OPT_FAIL;
        }
        return CodeConf::OPT_SUCCESS;
    }

    public function updateObject($table, $column, $val){

        $updateArr = (new ObjectParse($this->_object)) -> parseObjectToArr();

        $re = DB::table($table) -> where($column, '=', $val) -> update ( $updateArr ) ;

        if ($re === false){
            return CodeConf::DB_OPT_FAIL;
        }
        return CodeConf::OPT_SUCCESS;
    }

}
