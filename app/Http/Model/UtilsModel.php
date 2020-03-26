<?php
/**
 * Created by PhpStorm.
 * User: LG
 * Date: 2019/3/15
 * Time: 14:30
 */

namespace App\Http\Model;
use App\Http\Config\CodeConf;
use App\Http\Config\RedisHeadConf;
use App\Http\Config\ResourcePathConf;
use App\Http\Model\Common\UserCommonModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Config\RedisHeaderRulesConf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Vinkla\Hashids\Facades\Hashids;
use function Sodium\add;


class UtilsModel
{
    public static function getMysqlFields($data){
        $fieldReplaceStr = '';
        if (is_array($data) && count($data) > 0){
            foreach ($data as $field){
                $fieldReplaceStr .= ':'.$field.',';
            }
            $fieldReplaceStr = rtrim($fieldReplaceStr, ',');
        }
        return $fieldReplaceStr;
    }

    public static function changeMysqlResultToArr($mysqlResult, $removeFields = array()){
        $arr = array();
        if ($mysqlResult){
            foreach ($mysqlResult as $key=>$val){
                $vars = is_object($val) ? get_object_vars($val) : $val;
                $arr[$key] = array();
                if( is_array($vars) ){
                    foreach ($vars as $k => $v) {
                        if (is_array($removeFields) && count($removeFields) > 0 && in_array($k, $removeFields)) continue;
                        $arr[$key][$k] = $v;
                    }
                }else if( is_string($vars) ){
                    $arr[$key] = $vars;
                }
            }
        }
        return $arr;
    }

    /**
     * 对象 转 数组
     *
     * @param object $obj 对象
     * @return array
     */
    public static function objectToArray($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)object_to_array($v);
            }
        }

        return $obj;
    }

    public static function getIp(){
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
        return $res;
    }

    public static function getSqlPassword($password){
        return md5( $password . RedisHeadConf::getHead('password_sort') );
    }

    static function getCallbackJson($code, $other = array()){
        return json_encode(CodeConf::getConf($code, $other), JSON_UNESCAPED_UNICODE);
    }


    /**
     * 写入日志
     * @param $content string 内容
     * @param $filename string 文件名
     * @param string $type
     * @param bool $isName
     * @return bool
     */
    public static function log($content, $filename, $type = 'info', $isName = true) {
        $logPath = dirname(dirname(dirname(dirname(__FILE__)))).'/Logs/';
        if(is_array($content) || is_object($content)){
            $content = json_encode($content,JSON_UNESCAPED_UNICODE);
        }

        if ($isName) {
            $log_file = $logPath . $filename . "-" . date("Ymd") . ".log";
        } else {
            $log_file = $logPath . $filename . "-" . ".log";
        }
        $date = date("Y-m-d H:i:s");
        return file_put_contents($log_file, $date . " [$type] " . $content . "\n", FILE_APPEND);
    }

    public static function getAccount($token) {

        $account = Redis::get(RedisHeadConf::getHead('login_token' ) . $token );

        return $account;
    }


    public static function clearSensitiveInfo($list, $columns) {

        foreach ($list as &$item) {

            foreach ($columns as $column) {

                unset($item[$column]);

            }

        }

        return $list;

    }



}
