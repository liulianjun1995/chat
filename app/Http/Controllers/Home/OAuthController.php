<?php

namespace App\Http\Controllers\Home;

use App\Models\GithubUser;
use App\Models\User;
use App\Models\WechatUser;
use App\Services\LuoCaptchaService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Overtrue\Socialite\SocialiteManager;
use Ramsey\Uuid\Uuid;

class OAuthController extends CommonController
{

    public function login(Request $request)
    {
        if ($request->isMethod('post')){
            $field = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email': 'phone';

            if ($response = $request->input('luoResponse')){
                $result = (new LuoCaptchaService($response))->checkResponse();
                if ($result === false){
                    return $this->error('人机验证失败！');
                }
            }else{
                return $this->error('请先完成人机验证！');
            }

//            if ($request->has('ticket') && $request->has('randstr')){
//                // 验证
//                $client = new Client();
//                $url = 'https://ssl.captcha.qq.com/ticket/verify?aid='
//                    . env('TENCENTCAPTCHA_AID')
//                    . '&AppSecretKey=' . env('TENCENTCAPTCHA_APPSECRETKEY')
//                    . '&Ticket=' . $request->input('ticket')
//                    . '&Randstr=' . $request->input('randstr')
//                    . '&UserIP=' . $request->ip();
//                $response = $client->get($url);
//                $result = json_decode($response->getBody()->getContents());
//                if ($result->response != 1 || $result->err_msg !== 'OK'){
//                    return $this->error('验证失败！');
//                }
//            }else{
//                return $this->error('请先完成滑动验证');
//            }

            if (Auth::attempt([$field => $request->input('login'), 'password' => $request->input('password')])){
                $user = Auth::user();
                $user->last_login_ip = $request->ip();
                $user->last_login_time = date("Y-m-d H:i:s");
                $user->save();
                session(['user' => $user]);
                return $this->success();
            }

            return $this->error('用户名或密码有误');
        }

        if (auth()->check()){
            return redirect(route('chat-index'));
        }
//        Auth::loginUsingId(100002);
//        session(['user' => \auth()->user()]);

        return view('home.login.login');
    }
    
    /**
     * github
     * @return mixed
     */
    public function github(Request $request)
    {
        $socialite = new SocialiteManager(config('services'), $request);
        return $socialite->driver('github')->redirect();
    }

    /**
     * 登录
     * @param Request $request
     * @param GithubUser $githubUser
     * @param User $userInstance
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function githubLogin(Request $request, GithubUser $githubUser, User $userInstance)
    {
        $socialite = new SocialiteManager(config('services'), $request);
        $user = $socialite->driver('github')->user();
        DB::beginTransaction();
        // 注册登录用户
        try {
            if (!$githubUser->query()->where('github_id', $user['id'])->first()) {
                $data = [
                    'github_id' => $user->id,
                    'login'     => $user->username,
                    'name'      => $user->nickname,
                    'company'   => $user->company,
                    'avatar_url'=> $user->avatar,
                    'email'     => $user->email
                ];
                if ($githubUser
                        ->fill($data)
                        ->save()
                    &&
                    $userInstance->fill([
                        'github_id'     => $user->id,
                        'username'      => $user->username,
                        'password'      => Str::random(16),
                        'nickname'      => $user->nickname,
                        'avatar'        => $user->avatar,
                    ])->save()
                ) {
                    DB::commit();
                    $msg = true;
                    Auth::login($userInstance);
                    $user = Auth::user();
                    session(['user' => $user]);
                    $user->last_login_ip = $request->ip();
                    $user->last_login_time = date("Y-m-d H:i:s");
                    $user->save();
                } else {
                    throw new \Exception('保存用户信息时失败');
                }
            } else {
                DB::commit();
                $msg = true;
                $instance = $userInstance->query()->where('github_id', $user['id'])->first();
				$this->forceOffline($instance->id);
				Auth::login($instance);
                $instance->last_login_ip = $request->ip();
                $instance->last_login_time = date("Y-m-d H:i:s");
                $instance->save();
                session(['user' => $instance]);
            }
        } catch (\Exception $exception) {
//            $msg = $exception->getMessage();
            $msg = '系统异常';
            DB::rollBack();
        }
        return view('home.login.github', compact('msg'));
    }

    /**
     * 接入微信消息
     * @return mixed
     */
    public function wechat()
    {
        $app = app('wechat.official_account');

        $app->rebind('request',request());

        $app->server->push(function ($message) {

            if (array_filter($message)) {
                $method = Str::camel('handle_' . $message['MsgType']);

                if (method_exists($this, $method)) {
                    return call_user_func_array([$this, $method], [$message]);
                }

                Log::info('无此处理方法:' . $method);
            }
        });

        return $app->server->serve();
    }

    /**
     * 微信用户登录检查
     * @param Request $request
     * @return array
     */
    public function loginCheck(Request $request)
    {
        if (!$flag = $request->wechat_flag) {
            return $this->error();
        }

        $uid  = Cache::get(WechatUser::LOGIN_WECHAT . $flag);

        $user = User::query()->find($uid);

        if (empty($user)) {
            return $this->error('用户不存在');
        }
        // 登录用户、并清空缓存
		$this->forceOffline($user->id);
        Auth::loginUsingId($user->id);
        $user->last_login_ip = $request->ip();
        $user->last_login_time = date("Y-m-d H:i:s");
        $user->save();
        session(['user' => $user]);
        Cache::forget(WechatUser::LOGIN_WECHAT . $flag);
        Cache::forget(WechatUser::QR_URL . $flag);
        Cookie::forget(WechatUser::WECHAT_FLAG);
        return $this->success();
    }

    /**
     * 生成二维码
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function wechatQrcode(Request $request)
    {
        $app = app('wechat.official_account');

        // 查询 cookie，如果没有就重新生成一次
        if (!$weChatFlag = $request->cookie(WechatUser::WECHAT_FLAG)) {
            $weChatFlag = Uuid::uuid4()->getHex();
        }
        // 缓存微信带参二维码
        if (!$url = Cache::get(WechatUser::QR_URL . $weChatFlag)) {
            // 有效期 1 天的二维码
            $result = $app->qrcode->temporary($weChatFlag, 3600 * 24);
            $url    = $app->qrcode->url($result['ticket']);

            Cache::put(WechatUser::QR_URL . $weChatFlag, $url, now()->addDay());
        }
        return response(compact('url', 'weChatFlag'))->cookie(WechatUser::WECHAT_FLAG, $weChatFlag, 24 * 60);
    }

    /**
     * 处理事件
     * @param $event
     * @return mixed
     */
    protected function handleEvent($event)
    {
        Log::info('事件参数：', [$event]);

        $method = Str::camel('event_' . $event['Event']);
        Log::info('处理方法:' . $method);

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], [$event]);
        }

        Log::info('无此事件处理方法:' . $method);
    }

    protected function handleText($event)
    {
        return '哈哈';
    }

    /**
     * 关注事件
     * @param $event
     * @return void
     * @throws \Throwable
     */
    protected function eventSubscribe($event)
    {
        $openId = $event['FromUserName'];
        if ($wxUser = User::query()->where('openid', $openId)->first()) {
            Auth::loginUsingId($wxUser->id);
        } else {
            $app = app('wechat.official_account');

            $userInfo = $app->user->get($openId);
            $wechat_user = new WechatUser;
            $user = new User();
            // 微信用户是否存在
            DB::beginTransaction();
            try {
                if (!WechatUser::query()->where('openid', $openId)->first()) {
                    $data = Arr::only($userInfo, ['subscribe', 'openid', 'nickname', 'sex', 'language', 'city', 'province', 'country', 'headimgurl', 'subscribe_time', 'remark', 'subscribe_scene']);
                    if ($wechat_user
                            ->fill($data)
                            ->save()
                        &&
                        $user->fill([
                            'openid'        => $openId,
                            'github_id'     => $user['id'],
                            'username'      => $userInfo['openid'],
                            'password'      => Str::random(16),
                            'nickname'      => $userInfo['nickname'],
                            'avatar'        => $userInfo['headimgurl']
                        ])->save()
                    ) {
                        $this->markTheLogin($event, $user->id);
                        DB::commit();
                    }else{
                        DB::rollBack();
                    }
                }
            }catch (\Exception $e){
                DB::rollBack();
            }
        }
    }

    /**
     * 微信扫码
     * @param $event
     */
    protected function eventSCAN($event)
    {
        if ($wxUser = User::query()->where('openid', $event['FromUserName'])->first()) {
            // 标记前端可登陆
            $this->markTheLogin($event, $wxUser->id);
            return;
        }
    }

    /**
     * 取消关注
     * @param $event
     */
    protected function eventUnsubscribe($event)
    {
        if ($wechatUser = WechatUser::query()->where('openid', $event['FromUserName'])->first()) {
            $wechatUser->subscribe      = 0;
            $wechatUser->subscribe_time = null;
            $wechatUser->save();
        }
    }

    /**
     * 标记可登陆
     * @param $event
     * @param $uid
     */
    protected function markTheLogin($event, $uid)
    {
        if (empty($event['EventKey'])) {
            return;
        }

        $eventKey = $event['EventKey'];

        // 关注事件的场景值会带一个前缀需要去掉
        if ($event['Event'] === 'subscribe') {
            $eventKey = Str::after($event['EventKey'], 'qrscene_');
        }

        Log::info('EventKey:' . $eventKey, [$event['EventKey']]);
        // 标记前端可登陆
        Cache::put(WechatUser::LOGIN_WECHAT . $eventKey, $uid, now()->addMinute(30));
    }

    /**
     * 退出登录
     * @return array
     */
    public function logout()
    {
        Auth::logout();
        session()->flush();
        return $this->success();
    }
}
