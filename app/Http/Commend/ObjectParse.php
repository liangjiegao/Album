<?php


namespace App\Http\Model;



use Illuminate\Contracts\Logging\Log;
use ReflectionClass;
use ReflectionProperty;

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
                if ($val != null){
                    $val->setAccessible(true);
                    $this->_parse_arr[$val->getName()] = $val->getValue($this->_object);
                }
            }
        } catch (\ReflectionException $e) {
            Log::info($e->getMessage());
        }


        return $this->_parse_arr;
    }

    public function parseArrToObject($arr){



    }
}
