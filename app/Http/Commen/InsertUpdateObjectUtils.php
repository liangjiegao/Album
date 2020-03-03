<?php


namespace App\Http\Model;


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

        $re = DB::table($table) -> insert($insertArr);

        if ($re === false){
            return '';
        }
    }

}
