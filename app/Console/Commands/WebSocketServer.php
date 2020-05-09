<?php

namespace App\Console\Commands;
//use Illuminate\Support\Facades\Redis;
use App\Http\Config\RedisHeadConf;
use App\Http\Model\UserModel;
use App\Http\Model\UtilsModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class WebSocketServer extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'websocket:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '启动WebSocket';


    private $_accountsAndFds = [];

//    private $_live_time = 3600 * 24;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        DB::setDefaultConnection('mysql');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ob = $this;
        $server = new \swoole_websocket_server("0.0.0.0", 9200);

        $server->set([
            'daemonize' => true,//进程守护 可以后台运行
//            'ssl_key_file'  => '/usr/local/ssl/3227293_hrdesk.cn.key',
//            'ssl_cert_file' => '/usr/local/ssl/3227293_hrdesk.cn.pem',
        ]);

        $server->on('WorkerStart', function ($server, $worker_id) use ($ob) {
            $server->tick(3000, function () use ($server, $ob) {
                $ob->pushMailToClient($server);
            });
        });

        $server->on('open', function (\swoole_websocket_server $server, $request) {
            Log::info("连接");
        });

        $server->on('message', function (\swoole_websocket_server $server, $frame) use ($ob) {

            $account = $frame->data;
            Log::info("连接 "  . $account);

            if ($account != 'ping'){
                $fd = $frame->fd;
                Redis::hset(RedisHeadConf::getHead('websock_account_fd'), $account, $fd);
            }
        });

        $server->on('close', function ($ser, $close_fd) {
            // 删除关闭窗口的账号对应的通讯记录
            $accountAndFds = Redis::hgetall(RedisHeadConf::getHead('websock_account_fd'));
            foreach ($accountAndFds as $account => $fd) {
                if ($close_fd == $fd){
                    Redis::hdel(RedisHeadConf::getHead('websock_account_fd'), $account);
                    break;
                }
            }

        });

        $server->start();


    }

    /**
     * 在Redis中获取用户的总未读数
     * @param $uid
     * @return int
     */
    public function getTotalNoReadMessage($uid)
    {
        $list = Redis::lrange(RedisHeaderRulesConf::getConf('message_container') . $uid, 0, -1);
        $totalNoRead = 0;
        foreach ($list as $messageItem) {
            $messageItem = json_decode($messageItem, true);
            foreach ($messageItem as $item) {
                if (isset($item['is_read']) && $item['is_read'] == 0) {
                    $totalNoRead++;
                }
            }
        }
        return $totalNoRead;
    }

    public function pushMailToClient($server)
    {
        $uidAndFds = Redis::hgetall(RedisHeadConf::getHead('websock_account_fd'));
        foreach ($uidAndFds as $uid => $fd) {
            $server->push($fd, UtilsModel::getCallbackJson(10000, array("data" => ['parse_success']))); //服务端主动给客户端推送消息
        }
    }



}


?>
