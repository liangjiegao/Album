<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $arr = [ 'a' => 'a' ];
        var_dump(isset( $arr['a'] ));
        var_dump(isset( $arr['b'] ));
    }
}
