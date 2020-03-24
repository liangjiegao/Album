<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});


$router->post('/login/sendRegCode', ['uses' => 'LoginController@sendRegCode']);
$router->post('/login/reg', ['uses' => 'LoginController@reg']);
$router->post('/login/login', ['uses' => 'LoginController@login']);



// 关系绑定， 依赖接口而不是依赖对象
App::bind(App\Http\Model\Impl\ILoginModel::class , App\Http\Model\LoginModel::class);
App::bind(App\Http\Model\Impl\IEmailModel::class , App\Http\Model\EmailModel::class);
App::bind(App\Http\Model\Impl\IUserModel::class , App\Http\Model\UserModel::class);

App::bind("UserBean" , App\Http\Bean\UserBean::class);
App::bind("EmailBean" , App\Http\Bean\EmailBean::class);

