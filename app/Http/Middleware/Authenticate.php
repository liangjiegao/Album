<?php


namespace App\Http\Middleware;



use App\Http\Config\CodeConf;
use App\Http\Config\RedisHeadConf;

use App\Http\Model\UtilsModel;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Authenticate
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, $guards = null)
    {
        // 验证用户token
        if( !$this -> checkUserToken( $request -> input('token') ) ){

            return response()->json(CodeConf::getConf(CodeConf::LOGIN_EXPIRE, []));

        }
        return $next($request);
    }

    public function checkUserToken($token){
        $loginTokenHead   = RedisHeadConf::getHead('login_token');

        $tKey = $loginTokenHead . $token;
        return Redis::exists($tKey);
    }


}
