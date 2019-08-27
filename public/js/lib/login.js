define(["jquery", 'vue', 'layer', 'axios', 'ELEMENT'], function ($, Vue, layer, axios, ELEMENT) {

    Vue.use(ELEMENT);

    new Vue({
        el: '#app',
        data: function () {
            return {
                form: {
                    login: '',
                    password: '',
                    ticket: '',
                    randstr: '',
                    luoResponse: '',
                },
                OAuthLogin: ROOT.JSROOT + 'oauth/',
                dialogTableVisible: false,
                qrcode_url: '',
                wechat_timer: null,
                rules: {
                    login: [
                        { required: true, message: '请输入用户名', trigger: 'blur' }
                    ],
                    password: [
                        { required: true, message: '请输入密码', trigger: 'blur' }
                    ]
                }
            }
        },
        methods: {
            login(type) {
                var _this = this;
                switch (type) {
                    case 'web' :
                        _this.$refs['form'].validate( (valid) => {
                            if (_this.form.luoResponse.length <= 0){
                                layer.msg('请先完成人机验证');
                                return false;
                            }
                            if (valid){
                                axios.post(ROOT.JSROOT + 'login', _this.form).then((response) => {
                                    let result = response.data;
                                    if (result.code) {
                                        window.location.href = ROOT.URL_INDEX
                                    } else {
                                        _this.$message({
                                            showClose: true,
                                            message: result.message,
                                            duration: 2000,
                                            type: 'error',
                                            center: true
                                        })
                                        // 重置人机验证
                                        LUOCAPTCHA.reset();
                                    }
                                }).catch((error) => {
                                    console.log(error);
                                })
                            }
                        })

                        break;
                    case 'github':
                        window.open(this.OAuthLogin + 'github', 'newwindow', 'height=500, width=500, top=0, left=0, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=n o, status=no')
                        window.addEventListener('message', function (e) {
                            if (e.data === '1') {
                                _this.$message({
                                    showClose: true,
                                    message: '登录成功',
                                    duration: 1000,
                                    type: 'success',
                                    center: true,
                                    onClose: function () {
                                        window.location.href = ROOT.URL_INDEX
                                    }
                                });
                            } else {
                                _this.$message({
                                    showClose: true,
                                    message: e.data,
                                    duration: 1000,
                                    type: 'error',
                                    center: true
                                });
                            }
                        }, false)
                        break;
                    case 'wechat':
                        axios.post(_this.OAuthLogin + 'wechat-qrcode').then((response) => {
                            let result = response.data;
                            if (result.url && result.weChatFlag) {
                                _this.qrcode_url = result.url;
                                _this.dialogTableVisible = true;
                                _this.wechat_timer = setInterval(() => {
                                    axios.post(_this.OAuthLogin + 'wechat-login-check', {
                                        wechat_flag: result.weChatFlag
                                    }).then((response) => {
                                        let result = response.data
                                        if (result.code) {
                                            _this.$message({
                                                showClose: true,
                                                message: '登录成功',
                                                duration: 1000,
                                                type: 'success',
                                                center: true,
                                                onClose: function () {
                                                    window.location.href = ROOT.URL_INDEX
                                                }
                                            });
                                        }
                                    })
                                }, 2000)
                            }
                        }).catch((error) => {
                            console.log(error);
                        })
                        break;
                    default :
                        _this.$message({
                            showClose: true,
                            message: '暂不支持当前方式',
                            duration: 2000,
                            type: 'error',
                            center: true
                        })
                        break;
                }
            },
            closeWechat() {
                clearInterval(this.wechat_timer)
            },
            loadTCaptcha() {
                return new Promise( resolve => {
                    var script = document.createElement('script');
                    script.src = 'https://ssl.captcha.qq.com/TCaptcha.js'
                    script.async = true
                    script.onload = script.onreadystatechange = function() {
                        if (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') {
                            resolve()
                            script.onload = script.onreadystatechange = null
                        }
                    }
                    document.getElementsByTagName("head")[0].appendChild(script)
                    this.initVerify()
                })
            },
            loadLuoCaptcha() {
                return new Promise( resolve => {
                    var script = document.createElement('script');
                    script.src = '//captcha.luosimao.com/static/dist/api.js'
                    script.async = true
                    script.onload = script.onreadystatechange = function() {
                        if (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') {
                            resolve()
                            script.onload = script.onreadystatechange = null
                        }
                    }
                    document.getElementsByTagName("head")[0].appendChild(script)
                })
            },
            initVerify() {
                var _this = this;
                window.captcha = function(res){
                    // res（用户主动关闭验证码）= {ret: 2, ticket: null}
                    // res（验证成功） = {ret: 0, ticket: "String", randstr: "String"}
                    if(res.ret === 0){
                        _this.form.ticket = res.ticket;
                        _this.form.randstr = res.randstr;
                        // console.log(_this.form.ticket);
                        _this.login('web');
                    }
                }
            }
        },
        mounted() {
            var _this = this;
            _this.loadTCaptcha()
            _this.loadLuoCaptcha().then( () => {
                window.luoCallback = (resp)=>{
                    _this.form.luoResponse = resp;
                    // console.log(resp);
                    // console.log('get a callback');
                }
            })

        },
    })
});