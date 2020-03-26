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

/** --------------------------------------- 登录模块 ------------------------------------- **/
$router->post('/login/sendRegCode',         ['uses' => 'LoginController@sendRegCode']);
$router->post('/login/reg',                 ['uses' => 'LoginController@reg']);
$router->post('/login/login',               ['uses' => 'LoginController@login']);

/** --------------------------------------- 用户模块 -------------------------------------  **/
// 获取用户信息
$router->get('/user/getUserInfo',                   ['middleware' => 'auth', 'uses' => 'UserController@getUserInfo']);
// 编辑用户信息
$router->get('/user/editUser',                      ['middleware' => 'auth', 'uses' => 'UserController@editUser']);
// 修改用户密码
$router->post('/user/changePassword',               ['middleware' => 'auth', 'uses' => 'UserController@changePassword']);
// 修改用户头像
$router->post('/user/changeHeadIcon',               ['middleware' => 'auth', 'uses' => 'UserController@changeHeadIcon']);
// 发送修改密码验证码
$router->post('/user/sendChangePasswordCheckCode',  ['middleware' => 'auth', 'uses' => 'UserController@sendChangePasswordCheckCode']);
// 添加好友申请
$router->post('/user/applyFriend',                  ['middleware' => 'auth', 'uses' => 'UserController@applyFriend']);
// 获取自己申请添加好友的列表
$router->post('/user/getMyApplyList',               ['middleware' => 'auth', 'uses' => 'UserController@getMyApplyList']);
// 获取别人申请添加自己为好友的列表
$router->post('/user/getOtherApplyList',            ['middleware' => 'auth', 'uses' => 'UserController@getOtherApplyList']);
// 通过申请
$router->post('/user/optFriendApply',               ['middleware' => 'auth', 'uses' => 'UserController@optFriendApply']);

/** ----------------------------------- 文件上传 -----------------------------------------------**/
// 上传照片
$router->post('/upload/uploadImg',                    ['middleware' => 'auth', 'uses' => 'UploadController@uploadImg']);
$router->post('/upload/createDir',                    ['middleware' => 'auth', 'uses' => 'UploadController@createDir']);
$router->post('/upload/deleteDir',                    ['middleware' => 'auth', 'uses' => 'UploadController@deleteDir']);
$router->post('/upload/deleteImg',                    ['middleware' => 'auth', 'uses' => 'UploadController@deleteImg']);

/** ------------------------------------ 工作空间 ------------------------------------------------ **/
$router->post('/workspace/getWorkspace',                    ['middleware' => 'auth', 'uses' => 'WorkspaceController@getWorkspace']);


// 关系绑定， 依赖接口而不是依赖对象
App::bind(App\Http\Model\Impl\ILoginModel::class ,      App\Http\Model\LoginModel::class);
App::bind(App\Http\Model\Impl\IEmailModel::class ,      App\Http\Model\EmailModel::class);
App::bind(App\Http\Model\Impl\IUserModel::class ,       App\Http\Model\UserModel::class);
App::bind(App\Http\Model\Impl\IUploadModel::class ,     App\Http\Model\UploadModel::class);
App::bind(App\Http\Model\Impl\IWorkspaceModel::class ,  App\Http\Model\WorkspaceModel::class);

App::bind("UserBean" ,                                  App\Http\Bean\UserBean::class);
App::bind("EmailBean" ,                                 App\Http\Bean\EmailBean::class);
App::bind("ImageBean" ,                                 App\Http\Bean\ImageBean::class);
App::bind("ImageDirBean" ,                              App\Http\Bean\ImageDirBean::class);
App::bind("UserRelationBean" ,                          App\Http\Bean\UserRelationBean::class);

