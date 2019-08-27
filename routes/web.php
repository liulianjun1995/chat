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

Route::get('/', function (){
    return redirect()->route('chat-index');
});
Route::group(['namespace' => 'Home'], function (){

    // 登录页面
    Route::any('login', 'OAuthController@login')->name('chat-login');

    // 已登录
    Route::group(['middleware' => 'checkLogin'], function (){
		Route::any('test', 'ChatController@test');
        // 首页
        Route::get('/', 'ChatController@index')->name('chat-index');
        // 好友列表
        Route::get('/getList', 'ChatController@getList')->name('chat-list');
        // 查找
        Route::any('/find', 'ChatController@find')->name('chat-find');
        // 聊天记录
        Route::any('/chatRecord', 'ChatController@chatRecord');
        // 消息盒子
        Route::any('/message', 'ChatController@message')->name('chat-message');
        // 城市列表
        Route::any('/area', 'ChatController@area')->name('chat-area');

        // 我的好友
        Route::get('/friends', 'FriendController@friends');
        // 添加好友
        Route::post('/addFriend', 'FriendController@addFriend');
        // 拒绝好友
        Route::post('/refuseFriend', 'FriendController@refuseFriend');
        // 删除好友
        Route::post('/deleteFriend', 'FriendController@deleteFriend');
        // 设置备注
		Route::post('/remarkFriend', 'FriendController@remarkFriend');

		// 添加好友分组
        Route::post('/addMyGroup', 'GroupController@addMyGroup');
        // 删除好友分组
        Route::post('/delMyGroup', 'GroupController@delMyGroup');
        // 重命名好友分组
        Route::post('/renameMyGroup', 'GroupController@renameMyGroup');
        // 移动好友
        Route::post('/moveFriend', 'GroupController@moveFriend');
        // 创建群组
        Route::any('/createGroup', 'GroupController@createGroup');
        // 同意邀请群
        Route::post('/agreeGroup', 'GroupController@agreeGroup');
        // 同意群申请
        Route::post('/agreeApplyGroup', 'GroupController@agreeApplyGroup');
        // 退出群组
		Route::post('/quitGroup', 'GroupController@quitGroup');

        // 群组成员
		Route::get('/groupMembers', 'GroupController@groupMembers');

		// 发送图片
		Route::post('/uploadImage', 'ChatController@uploadImage');
		// 发送文件
		Route::post('/uploadFile', 'ChatController@uploadFile');

        // 个人资料
        Route::any('/userInfo/{id}', 'UserController@userInfo')->where('id' , '[0-9]+')->name('chat-userInfo');
        // 修改个人资料
        Route::post('/info', 'UserController@info');
        // 修改签名
        Route::post('/sign', 'UserController@sign');

        // 退出登录
        Route::post('/logout', 'OAuthController@logout')->name('chat-logout');
    });

});

// oauth登录
Route::group(['prefix' => 'oauth', 'namespace' => 'Home'], function (){
    Route::get('github', 'OAuthController@github');
    Route::get('github/login', 'OAuthController@githubLogin');
    Route::any('wechat', 'OAuthController@wechat');
    Route::any('wechat-qrcode', 'OAuthController@wechatQrcode')->name('wechat-qrcode');
    Route::any('wechat-login-check', 'OAuthController@loginCheck')->name('wechat-login-check');
});