<?php

namespace App\Console\Commands;
//use Illuminate\Support\Facades\Redis;
use App\Http\Config\QueueLogConf;
use App\Http\Config\RedisHeaderRulesConf;
use App\Http\Config\ResumeOriginConf;
use App\Http\Model\Log\LogSaveModel;
use App\Http\Model\UserModel;
use App\Http\Model\UtilsModel;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class DBTest extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'test:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试数据库';

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

        $re = DB::table('user_info')
            -> select('*')
            -> get();
        $re = UtilsModel::changeMysqlResultToArr($re);

        print_r($re);
    }


}


?>
