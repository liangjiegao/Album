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

    /**
     * 单条插入
     * @param $table
     * @return int
     */
    public function insertObject($table){

        $insertArr = (new ObjectParse($this->_object))->parseObjectToArr();

        $re = DB::table($table) -> insert ($insertArr);

        if ($re === false){
            return CodeConf::DB_OPT_FAIL;
        }
        return CodeConf::OPT_SUCCESS;
    }

    /**
     * 批量插入
     * @param $table
     * @return int
     */
    public function insertObjectBatch($table){
        // 保证数组
        $this->_object = is_array($this->_object) ? $this -> _object : [$this->_object];
        $batchData = [];
        foreach ($this->_object as $obj) {
            $insertArr = (new ObjectParse($obj))->parseObjectToArr();
            $batchData[] = $insertArr;
        }

        // 批量插入
        $re = DB::table($table) -> insert ($batchData);

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
