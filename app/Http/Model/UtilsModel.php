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
    /**
     * @param $table
     * @param $data
     * @param array $fields
     * @return array
     * 插入数据，已有的就修改
     */
    public static function setInsertUpdateFromData($table, $data, $fields = array()){
        Log::info($data);
        if (!empty($table) && !empty($data)){
            if (!is_array(array_first($data))){ //如果不是 二维数组，则组装成二维数组
                $data = array($data);
            }
            if (empty($fields)){ // 没有指定写入字段，默认写入全部字段
                $fields = array_keys(array_first($data));
            }
            $sqlFields = '';
            $sqlUpdate = ' on duplicate key update ';
            foreach ($fields as $val){
                $sqlFields .= '`'.$val.'`,';
                $sqlUpdate .= '`'.$val.'`=values(`'.$val.'`),';
            }
            $sqlFields = rtrim($sqlFields, ',');
            $sqlUpdate = rtrim($sqlUpdate, ',');
            $sqlValues = '';
            $params = array();
            $num = 0;
            foreach ($data as $val){
                $sqlValues .= '(';
                foreach ($val as $field=>$v){
                    if (in_array($field, $fields)){
                        $params[$field.'_'.$num] = $v;
                        $sqlValues .= ":".$field.'_'.$num.',';
                    }
                }
                $sqlValues = rtrim($sqlValues, ',');
                $sqlValues .= '),';
                $num++;
            }
            $sqlValues = rtrim($sqlValues, ',');
            $sql =  "insert into {$table}({$sqlFields}) values{$sqlValues}".$sqlUpdate;
            Log::info($sql);
            $re = DB::insert($sql, $params);
            if ($re){
                return array('code'=>10000);
            }else {
                return array('code'=>40002, 'msg'=>'程序异常，请联系技术人员!');
            }

        }else {
            return array('code'=>40001, 'msg'=>'程序异常，请联系技术人员!');
        }
    }

    static function checkMobileCode($mobile){
        if(preg_match("/^1[34578]\d{9}$/", $mobile)){
            return true;
        }else {
            return false;
        }
    }
    static function getCallbackJson($code, $other = array()){
        return json_encode(CodeConf::getConf($code, $other), JSON_UNESCAPED_UNICODE);
    }
    static function getDeptIdFromAccount($account){
        $dept = DB::select ('select dept from oa_user where account=:account', array('account'=>$account));
        $deptList = UtilsModel::changeMysqlResultToArr($dept);
        return $deptList;
    }

    /*
     *传入表名（字符串），字段（数组），值（数组）拼接sql并且返回，三个参数都不为null
     * */
    static function insertArraySql($table,$filed,$params){
        if(empty($table)||empty($filed)||empty($params)){
            return false;
        }

        $sql = "INSERT into ".$table." (".implode(",",$filed).") value ";
        $paramsSql = [];
        $index = 0;
        foreach ($params as $value){
            $sql .="(";
            foreach($value as $val){
//                $sql .= "'{$val}',";
                $sql .= ":key$index,";
                $paramsSql["key$index"] = $val;
                $index++;
            }
            $sql = rtrim($sql, ',');
            $sql .="),";
        }
        $sql = rtrim($sql, ',').";";
        return ['sql'=>$sql, 'params'=>$paramsSql];
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
    static function checkCodeStatus($code){
        $code = intval($code);
        if ($code >= 10000 && $code <= 19999){
            return true;
        }else {
            return false;
        }
    }
    static function getRedisResumeHistoryKey($token){
        $key = '';
            if (!empty($token)){
            $rHeader = RedisHeaderRulesConf::getConf()['userToken'];
            $tKey = $rHeader.$token;
            $account = Redis::get($tKey);
            $prefix = RedisHeaderRulesConf::getConf()['userResumeSearchHistory'];
            $key = $prefix.$account;
        }
        return $key;
    }
    static function getRedisUserCollectListKey($token){
        $key = '';
        if (!empty($token)){
            $rHeader = RedisHeaderRulesConf::getConf()['userToken'];
            $tKey = $rHeader.$token;
            $account = Redis::get($tKey);
            $prefix = RedisHeaderRulesConf::getConf()['userResumeCollections'];
            $key = $prefix.$account;
        }
        return $key;
    }
    static function getToken(Request $request){
        //后续加上token和登录账号的验证
        return $request->input('token');
    }
    static function getTotalPage($total_num){
        $total_page = ($total_num %10 == 0) ? $total_num/10:floor($total_num/10)+1;
        return $total_page;
    }

    /**
     * 查找路径下同名文件
     * @param $absolute_path string 绝对路径 /data/tmp
     * @param $file_name   string 文件名
     * @param $file_format string 文件格式 jpg
     * @return array
     */
    public static function getSameNameFilePath($absolute_path, $file_name, $file_format =''){
        $str = '';
        if( !empty( $file_format )){
            $str = ".{$file_format}";
        }
        $shell = "find {$absolute_path} -name {$file_name}*{$str}";
        exec($shell, $output);
        if( is_array( $output) ){
            sort($output);
        }
        return $output;
    }

    /**
     * 取得资源url
     * @param $absolute_path string 文件的绝对路径 /data/tmp
     * @param $resource_url string 文件格式 jpg
     * @param $file_format string 文件格式 jpg
     * @return array
     */
    public static function getResourceUrlByFilePath($absolute_path, $resource_url, $file_format =''){
        if( !is_file( $absolute_path ) ){
            return array();
        }

        $path_parts = pathinfo( $absolute_path );
        $path       = $path_parts['dirname'].DIRECTORY_SEPARATOR;
        $file_name  = $path_parts['filename'];
        $file_path_arr  = self::getSameNameFilePath($path, $file_name, $file_format);
        $resource_path  = ResourcePathConf::getConf('base_resource_path');
        foreach ( $file_path_arr as &$item){
            $item = str_replace( $resource_path , $resource_url, $item );
        }
        unset($item);
        return $file_path_arr;
    }


    /*
     * 数组转树
     * */
    public static function makeTree($list,$pk='store_id',$pid='father_id',$child='children',$root=0){
        $tree = array();
        foreach($list as $key=> $val){
            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(! empty($list)){
                    $child = self::makeTree($list,$pk,$pid,$child,$val[$pk]);

                    if(!empty($child)){
                        $val['children']=$child;
                    }
                    elseif (empty($child)){
                        $val['children'] = array();
                    }
                }
                $tree[]=$val;
            }
        }
        return $tree;
    }


    /**
     * @describe 数组生成正则表达式
     * @param array $words
     * @return string
     */
    public static function generateRegularExpression($words)
    {
        $regular = implode('|', array_map('preg_quote', $words));
        return "/$regular/i";
    }

    /**
     * @describe 字符串 生成正则表达式
     * @param $string
     * @return string
     */
    public static function generateRegularExpressionString($string){
        $str_arr[0]=$string;
        $str_new_arr=  array_map('preg_quote', $str_arr);
        return $str_new_arr[0];
    }
    /**
     * 检查敏感词
     * @param $banned
     * @param $string
     * @return array
     */
    public static function checkWords($banned,$string)
    {
        $match_banned = array();
        //循环查出所有敏感词

        $new_banned = strtolower($banned);
        $i = 0;
        do{
            $matches = null;
            if ( !empty($new_banned) && preg_match($new_banned, $string, $matches) ) {
                $is_empty=empty($matches[0]);
                if(!$is_empty){
                    $match_banned = array_merge($match_banned, $matches);
                    $matches_str  = strtolower(self::generateRegularExpressionString($matches[0]));
                    $new_banned   = str_replace("|".$matches_str."|","|",$new_banned);
                    $new_banned   = str_replace("/".$matches_str."|","/",$new_banned);
                    $new_banned   = str_replace("|".$matches_str."/","/",$new_banned);
                }
            }
            $i++;
            if($i>20){
                $is_empty=true;
                break;
            }
        }while(count($matches)>0 && !$is_empty);

        //查出敏感词
        if($match_banned){
            return $match_banned;
        }
        //没有查出敏感词
        return array();
    }

    /*
     * 获取游戏tag对应的name
     * 以字符串的形式传入
     * 根据tagId转tagName
     * 以数组的形式传出
     * */
    public static function getGameTagName($tagList = ""){
        $tagId = explode(",",$tagList);
        $data = array();
        //将所有tag存入数组
        $tagName = (new GameTagModel())->getLowLevelGameTag();
        foreach ($tagId as $value){
            if (array_key_exists($value,$tagName)){
                $data[$value] = $tagName[$value];
            }
        }
        return $data;
    }

    /*
     * 获取公司tag对应的name
     * 以字符串的形式传入
     * 根据tagId转tagName
     * 以数组的形式传出
     * */
    public static function getCompanyTagName($tagList = ""){
        $tagId = explode(",",$tagList);
        $data = array();
        //将所有tag存入数组
        $tagName = GameCompanyModel::getCompanyMainServer()+GameCompanyModel::getCompanyServerRounds();
        foreach ($tagId as $value){
            if (array_key_exists($value,$tagName)){
                $data[$value] = $tagName[$value];
            }
        }
        return $data;
    }

    /**
     * 获取随机数
     */
    public static function getRandomNumber(){
        return time().rand(10000,99999);
    }

    /**
     * 获取base64file加密后数据
     * @param $file_absolute_path string 文件绝对路径
     * @return string
     */
    public static function getBase64FileData($file_absolute_path){
        if( !is_file( $file_absolute_path) ){
            return '';
        }
        $file_data = file_get_contents($file_absolute_path);
        return base64_encode( $file_data );
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

    /**
     * 下滑线变驼峰
     * @param $str
     * @return string
     */
    public static function toHump($str) {
        $newStr = explode('_', $str);
        $newArr = "";
        foreach ($newStr as $key => $value) {
            $newArr .= ucfirst($value);
        }
        return $newArr;
    }


    /**
     * 压缩数据格式
     * @param $data
     * @return string
     */
    public function serialize($data) {

        return empty( $data ) ? '' : gzcompress(base64_encode(serialize($data)));
    }

    /**
     *
     * @param $data
     * @return string
     */
    public function unserialize($data) {
        return gzuncompress(base64_decode(unserialize($data)));
    }

    public static function readDir($dir,&$files,$type)
    {
        if (!is_dir($dir)) {
            echo "no dir";
            return false;
        }
        $handle = opendir($dir);
        if ($handle) {
            while (($f1 = readdir($handle)) !== false) {
                $temp = $dir . DIRECTORY_SEPARATOR . $f1;
                if ($f1 != '.' && $f1 != '..') {
                    if (is_dir($temp)) {
                        self::readDir($temp, $files, $type);
                    } else {
                        $files[] = $temp;
                    }
                }
            }
        }
    }

    /*
     * 二维数组排序 $sort 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
     * */
    public static function arraySequence($array, $field, $sort = 'SORT_DESC') {
        $arrSort = array();
        foreach ($array as $uniqId => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqId] = $value;
            }
        }
        array_multisort($arrSort[$field], constant($sort), $array);
        return $array;
    }

    public static function removeSymbol($string = ''){
        return preg_replace('#[^\x{4e00}-\x{9fa5}A-Za-z0-9]#u','',$string);
    }

    public static function removeSymbolToPhone($string = ''){
        $string = trim($string);
        if (empty($string)){
            return false;
        }
        if (preg_match_all('/\d+/',$string,$num)){
            return implode($num[0]);
        }else{
            return false;
        }
    }

    public static function removeSymbolToEmail($string = ''){
        $string = trim($string);
        if (empty($string)){
            return false;
        }
        if (preg_match_all('/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i',$string,$email)){
            return $email[0];
        }else{
            return false;
        }
    }

    /**
     * mhtml转为html
     * @param $content
     * @return bool|string
     */
    public static function mht2html($content) {
        $content_header = '/Content-Transfer-Encoding:([\s\S]*?)\n/ims';
        preg_match($content_header,$content,$item_results);
        if(!$item_results){
            return $content;
        }
        $bianma=trim($item_results[1]);
        if($bianma === 'quoted-printable'){
            $contents ='';
            $pregcont = '/content-transfer-encoding: quoted-printable(.+?)content-type: image\/gif; name=logo.gif/is';
            preg_match($pregcont, $content, $conts);
            foreach ($conts as $k=>$v){
                $contents=quoted_printable_decode($v);
            }
            //进行编码解压
            return $contents;
        }elseif($bianma==="base64"){
            $pregcont = '/Content-Transfer-Encoding:base64(\n*)(.+?)Content-Type:image\/gif/is';
            preg_match($pregcont, $content, $conts);
            preg_match('/[\s\S]+?(?<=-)/',$conts[2],$temp);
            $contents=base64_decode($temp[0]);
            if(!$contents)
                return $content;
            //$contents = preg_replace('/(?<=<\/html>).+?$/','',$contents);
            return $contents;
        }else{
            return false;
        }
    }

    /**
     * 判断是否是html格式
     * @param $absolute_path
     * @return bool|int
     */
    public static function isMhtmlFormat($absolute_path){
        if( !is_file($absolute_path) ){
            return false;
        }
        $content = file_get_contents($absolute_path);
        return strpos($content, 'boundary=');
    }

    //开始时间处理($time为表名,$default是默认天数,$start是接收日期字段名,$format是日期格式)
    public static function date_start($time = 'time',$default = 10,$start = 'date_start', $format = 'Y-m-d H:i:s'){
        $url = '';
        $where = '';
        if (isset($_GET[$start]) && !empty($_GET[$start])) {
            $date_start = trim($_GET[$start]);
            $startUnix  = strtotime($date_start);
            $url   = "&$start=" . $date_start;
            $where = " AND $time >= $startUnix";

        } elseif (!isset($_GET[$start])) {

            if ($default == '') {
                $date_start = "";
                $url = "&$start=" . $date_start;
            } else {
                //黙认近10天
                $default = $default - 1;
                $startUnix = strtotime(date('y-m-d', strtotime("-$default days")));
                $date_start = date($format, $startUnix);
                $where = " AND $time >= $startUnix";
            }
        } elseif (isset($_GET[$start]) && empty($_GET[$start])) {
            $date_start = "";
            $url = "&$start=" . $date_start;


        }
        $data['date_start'] = $date_start;
        $data['url'] = $url;
        $data['where'] = $where;
        return $data;
    }


    //结束时间处理($time为表名,$default是默认天数,$end是接收日期字段名,$format是日期格式)
    public static function date_end($time = 'time', $default = 10, $end = 'date_end', $format = 'Y-m-d H:i:s'){
        $url = '';
        $where = '';
        if (isset($_GET[$end]) && !empty($_GET[$end])) {
            $date_end = trim($_GET[$end]);
            if ($format == 'Y-m-d H:i:s') {
                $endUnix = strtotime($date_end);
            } else {
                $endUnix = strtotime($date_end) + 3600 * 24 - 1;
            }

            $url = "&$end=" . $date_end;
            $where = " AND $time <= $endUnix";

        } elseif ( !isset($_GET[$end]) ) {
            if ($default == '') {
                $date_end = "";
                $url = "&$end=" . $date_end;
            } else {
                $endUnix = strtotime(date('y-m-d')) + 86400 - 1;
                $date_end = date($format, $endUnix);
                $where = " AND $time <= $endUnix";
            }

        } elseif (isset($_GET[$end]) && empty($_GET[$end])) {
            $date_end = "";
            $url .= "&$end=" . $date_end;

        }
        $data['date_end'] = $date_end;
        $data['url'] = $url;
        $data['where'] = $where;
        return $data;
    }

    /**
     * 将Sql转变成Sql
     *
     * @param array array
     * @param $type = | LIKE
     * @param string
     * @return string
     */
    public static function arrayToSql($arr, $type = '=') {
        $type = trim($type);
        $len = count($arr);

        $where = "";
        switch ($len) {
            case 0:
                $where = " = ''";
                break;
            case 1:
                $str = addslashes($arr[0]);
                if ($type == '=') {
                    $where =  " = '{$str}'";
                } else {
                    $where = " LIKE '%{$str}%'";
                }
                break;
            default:
                foreach ($arr as &$v) {
                    $v = addslashes($v);
                    $v = "'{$v}'";
                }
                unset($v);
                $str = implode(',', $arr);
                $where = " IN (".$str.")";
                break;
        }

        return $where;
    }

    /**
     * 将换行转变为数组
     *
     * @param string string
     * @return array()
     */
    public static function pregEnterReplaceArray($string) {
        if (empty($string)) {
            return array();
        }

        if (!preg_match("/\s+/", $string)) {
            return array($string);
        }
        $str = preg_replace("/\s+/", ',', $string);
        $arr = explode(',', $str);
        $arr = array_filter($arr);
        $arr = array_unique($arr);
        return $arr;
    }

    /**
     * 通过文件名截取文件后缀
     * @param $fileName 文件名
     * @return false|string
     */
    public static function substrToFileByExtension($fileName){
        return substr($fileName,strripos($fileName,'.'));
    }


    /**
     * 魔术符号
     * @param $vars
     * @return array|string
     */
    public static function addQuotes($vars){
        return is_array($vars) ? array_map(array(__CLASS__, __FUNCTION__), $vars) : addslashes($vars);
    }

    /**
     * 去除魔术符号
     * @param unknown_type $vars
     * @return array|string
     */
    public static function stripQuotes($vars) {
        return is_array($vars) ? array_map(array(__CLASS__, __FUNCTION__), $vars) : stripslashes($vars);
    }

    /**
     * 处理信息
     * @param $data
     * @param string $columns
     * @return array
     */
    public static function processInfo($data, $columns = ''){
        if( empty($columns ) ){
            return $data;
        }
        $format_data = [];
        if ( is_array($data) ){
            foreach ($data as $item){
                $value = isset( $item[$columns] ) ? $item[$columns] : 0;
                $format_data[$value] = $item;
            }
        }
        return $format_data;
    }


    /**
     * @param $data
     * @param string $key1
     * @param string $key2
     * @return array
     */
    public static function formatSelectData($data, $key1 = '', $key2 =''){
        if( empty( $key1 ) || empty( $key2 )){
            return $data;
        }
        $info = [];
        foreach ( $data as &$row){

            $info[] = [
                'key'   => isset( $row[$key1] ) ? $row[$key1] : '',
                'value' => isset( $row[$key2] ) ? $row[$key2] : ''
            ];
        }
        unset($row);
        return $info;
    }

    /**
     * 修改编码
     * @param $data
     * @return false|string|string[]|null
     */
    public static function changeCharset($data){
        if( !empty($data) ){
            $fileType = mb_detect_encoding($data , array('UTF-8','GBK','LATIN1','BIG5')) ;
            if( $fileType != 'UTF-8'){
                $data = mb_convert_encoding($data ,'utf-8' , $fileType);
            }
        }
        return $data;
    }

    /*
    * 获取某星期的开始时间和结束时间
    * time 时间
    * first 表示每周星期一为开始日期 0表示每周日为开始日期
    */
    public static function getWeekStartAndEnd($time = '', $first = 1)
    {
        // 当前日期
        if (!$time) $time = time();
        $default_date = date("Y-m-d", $time);
        //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        // 获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w         = date('w', strtotime($default_date));
        // 获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start = date('Y-m-d', strtotime("$default_date -" . ($w ? $w - $first : 6) . ' days'));
        // 本周结束日期
        $week_end = date('Y-m-d', strtotime("$week_start +6 days"));
        return ['week_start' => $week_start, 'week_end' => $week_end];
    }

    public static function getTimeStrByTimeDimension($time_unix = '', $dimension){
        if (!$time_unix) $time_unix = time();
        $time_str = '';
        switch ($dimension){
            case 'days':
                $time_str = date('Y-m-d', $time_unix);
                break;
            case 'weeks':
                $week_str = self::getWeekStartAndEnd($time_unix);
                $time_str = $week_str['week_start'].'~'.$week_str['week_end'];
                break;
            case 'months':
                $time_str = date('Y-m', $time_unix);
                break;
        }
        return $time_str;
    }

    public static function secToTime($time, $format = 'hours'){
        if(is_numeric($time)){
            $value = array(
                "years" => 0, "days" => 0, "hours" => 0,
                "minutes" => 0, "seconds" => 0,
            );
            if($time >= 31556926){
                $value["years"] = floor($time/31556926);
                $time = ($time%31556926);
            }
            if($time >= 86400){
                $value["days"] = floor($time/86400);
                $time = ($time%86400);
            }
            if($time >= 3600){
                $value["hours"] = floor($time/3600);
                $time = ($time%3600);
            }
            if($time >= 60){
                $value["minutes"] = floor($time/60);
                $time = ($time%60);
            }
            $value["seconds"] = floor($time);
            $time = [
                'year' => $value["years"],
                'days' => $value["days"],
                'hours' => $value["hours"],
                'minutes' => $value["minutes"],
                'seconds' => $value["seconds"],
            ];
            if( !empty($format) ){
                return isset( $time[$format] ) ? $time[$format] : '';
            }else{
                return $time;
            }
        }else{
            return (bool) FALSE;
        }
    }
}
