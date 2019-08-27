<?php

namespace App\Models;


class WechatUser extends Model
{

    public $timestamps = false;

    const WECHAT_FLAG   = 'WECHAT_FLAG';
    const LOGIN_WECHAT  = 'LOGIN_WECHAT';
    const QR_URL        = 'QR_URL';

    protected $fillable = [
        'subscribe', 'openid', 'nickname', 'sex', 'language', 'city', 'province', 'country', 'headimgurl', 'subscribe_time', 'remark', 'subscribe_scene'
    ];

    public function user()
    {
        return $this->hasOne('App/Models/User', 'openid', 'openid');
    }
}
