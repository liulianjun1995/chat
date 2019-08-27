<!doctype html>
<html lang="en" style="height: 100%;">
<head>
    @include('home.layout.head')
</head>
<body style="background-image: url('{{ asset('storage/img/demo-1-bg.jpg') }}');height: 100%">
    <div id="app" v-cloak>
        <canvas id="canvas"></canvas>
        <div class="login-box">
            <h3>登录 lee-chat</h3>
            <div class="form">
{{--                <div class="input-outer">--}}
{{--                    <span class="user"></span>--}}
{{--                    <input type="text" placeholder="11位手机号或Email" v-model="form.login">--}}
{{--                </div>--}}
{{--                <div class="input-outer">--}}
{{--                    <span class="password"></span>--}}
{{--                    <input type="password" placeholder="请输入密码" v-model="form.password">--}}
{{--                </div>--}}
                <el-form :model="form" ref="form" status-icon :rules="rules">
                    <el-form-item prop="login">
                        <el-input class="input-outer" prefix-icon="user" name="login" placeholder="11位手机号或Email" v-model="form.login"></el-input>
                    </el-form-item>
                    <el-form-item prop="password">
                        <el-input type="password" class="input-outer" name="password" prefix-icon="password" placeholder="请输入密码" v-model="form.password"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <div class="l-captcha" data-width="100%" data-callback="luoCallback" data-site-key="58512cafde2d09cdddc0d5f522af0553"></div>
                    </el-form-item>
                </el-form>

                {{--                <div id="TCaptcha" style="width:300px;height:40px;">--}}
{{--                    <button id="TencentCaptcha"--}}
{{--                            data-appid="2018161602"--}}
{{--                            data-cbfn="captcha"--}}
{{--                            type="button"--}}
{{--                    >验证</button>--}}
{{--                </div>--}}
{{--                <el-button style="width: 100%" id="TencentCaptcha" data-appid="{{ env('TENCENTCAPTCHA_AID') }}"--}}
{{--                           data-cbfn="captcha" type="primary" round>登录</el-button>--}}
                <el-button style="width: 100%" @click="login('web')" type="primary" round>登录</el-button>
                <p style="text-align: center;margin-top: 10px; font-size: 14px;">
                    社交账号登入
                    <el-tooltip class="item" effect="dark" content="Github" placement="bottom">
                        <a class="login-icon" href="javascript:" @click="login('github')">
                            <i class="el-icon el-icon-ali-github" style="font-size: 23px; color: #fff"></i>
                        </a>
                    </el-tooltip>
                    <el-tooltip class="item" effect="dark" content="QQ" placement="bottom">
                        <a class="login-icon" href="javascript:" @click="login('qq')">
                            <i class="el-icon el-icon-ali-qq" style="font-size: 23px; color: #7CA9C9"></i>
                        </a>
                    </el-tooltip>
                    <el-tooltip class="item" effect="dark" content="微信" placement="bottom">
                        <a class="login-icon" href="javascript:" @click="login('wechat')">
                            <i class="el-icon el-icon-ali-weixin" style="font-size: 23px; color: #00BC0D"></i>
                        </a>
                    </el-tooltip>
                    <el-tooltip class="item" effect="dark" content="微博" placement="bottom">
                        <a class="login-icon" href="javascript:" @click="login('weibo')">
                            <i class="el-icon el-icon-ali-weibo" style="font-size: 23px; color: #D32024"></i>
                        </a>
                    </el-tooltip>
                </p>
                <p style="text-align: center;margin-top: 10px; font-size: 14px;">
                    体验账号：test@qq.com 密码：123456
                </p>
            </div>
        </div>
        <el-dialog :visible.sync="dialogTableVisible" @close="closeWechat" center>
            <div style="text-align: center">
                <p style="font-size: 24px">微信登录</p>
                <img :src="qrcode_url">
                <p style="font-size: 18px">请使用微信扫描二维码登录</p>
            </div>
        </el-dialog>
    </div>
</body>
</html>
@include('home.layout.js')
<script>
    ROOT.URL_INDEX = "{{ route('chat-index') }}"
    require(['lib/login'])
</script>
