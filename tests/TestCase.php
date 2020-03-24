<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function testF(){

        $re = DB::table('user_info')
            -> select('*')
                -> get();

    }

}
