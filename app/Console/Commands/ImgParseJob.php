<?php



namespace App\Console\Commands;

use App\Http\Config\RedisHeadConf;
use App\Http\Model\ImgBuildTagModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Console\Command;

class ImgParseJob extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'parse:img_parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '图片解析';

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        DB::setDefaultConnection('mysql');
        parent::__construct();

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 获取队列中待解析的图片keys
        $imgKeys = Redis::lrange( RedisHeadConf::getHead( 'wait_parse_img_keys' ), 0, -1 );
        Redis::lrem( RedisHeadConf::getHead( 'wait_parse_img_keys' ) );
        \Log::info($imgKeys);
        // 解析
        $model = new ImgBuildTagModel( $imgKeys );
        $model -> parseImg();

    }

}
