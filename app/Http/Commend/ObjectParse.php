<?php


namespace App\Http\Model;



use ReflectionClass;
use ReflectionProperty;
use Illuminate\Support\Facades\Log;

class ObjectParse
{

    private $_object;
    private $_parse_arr = [];

    public function __construct($object)
    {
        $this->_object = $object;
    }

    public function parseObjectToArr(){

        try {

            $r = new ReflectionClass($this->_object);
            $properties  = $r->getProperties(ReflectionProperty::IS_PRIVATE);

            foreach ($properties as $key => $val){
                $val->setAccessible(true);

                if ($val->getValue($this->_object) != null){
                    $this->_parse_arr[$val->getName()] = $val->getValue($this->_object);
                }
            }
        } catch (\ReflectionException $e) {
            Log::info($e->getMessage());
        }


        return $this->_parse_arr;
    }

    public function parseArrToObject($arr){

        try {
            $r = new ReflectionClass($this->_object);
            $properties  = $r->getProperties(ReflectionProperty::IS_PRIVATE);

            foreach ($properties as $key => $val){
                $val->setAccessible(true);
                $val->setValue( $this->_object, $arr[$val->getName()] );
            }
        } catch (\ReflectionException $e) {
            Log::info($e->getMessage());
        }

        return $this->_object;
    }
}
