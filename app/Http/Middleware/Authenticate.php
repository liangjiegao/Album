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
    // 用户信息相关操作都会经过该中间件
    public function handle($request, Closure $next, $guards = null)
    {
        // 验证用户token
        if( !$this -> checkUserToken( $request -> input('token') ) ){
            // 验证不通过，返回通知用户重新登录
            return response()->json(CodeConf::getConf(CodeConf::LOGIN_EXPIRE, []));

        }
        // 验证通过，执行业务代码
        return $next($request);
    }

    /**根据token判断用户是否已登录
     * @param $token
     * @return mixed
     */
    public function checkUserToken($token){
        // 从redis中获取用户信息
        $loginTokenHead   = RedisHeadConf::getHead('login_token');
        $tKey = $loginTokenHead . $token;
        // 判断用户信息是否存在token中
        return Redis::exists($tKey);
    }


}
