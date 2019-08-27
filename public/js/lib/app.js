define(["jquery", 'vue', 'axios', 'ELEMENT', 'layui'],function($ , Vue, axios, ELEMENT, undefined) {

    console.log('━━━━━━look at me━━━━━━');
    console.log('  　　　┏┓　　　┏┓ ');
    console.log('  　　┏┛┻━━━┛┻┓ ');
    console.log('  　　┃　　　　　　　┃');
    console.log('  　　┃　　　━　　　┃ ');
    console.log('  　　┃　┳┛　┗┳　┃ ');
    console.log('  　　┃　　　　　　　┃ ');
    console.log('  　　┃　　　┻　　　┃ ');
    console.log('  　　┃　　　　　　　┃ ');
    console.log('  　　┗━┓　　　┏━┛ ');
    console.log('  　　　　┃　　　┃神兽保佑 ');
    console.log('  　　　　┃　　　┃代码无BUG！ ');
    console.log('  　　　　┃　　　┗━━━┓ ');
    console.log('  　　　　┃　　　　　　　┣┓ ');
    console.log('  　　　　┃　　　　　　　┏┛ ');
    console.log('  　　　　┗┓┓┏━┳┓┏┛ ');
    console.log('  　　　　　┃┫┫　┃┫┫ ');
    console.log('  　　　　　┗┻┛　┗┻┛ ');
    console.log('━━━━━━神兽出没━━━━━━');

    Vue.use(ELEMENT);

    ROOT.VM =  new Vue({
        el: '#main',
        data: function() {
            return {
                activeIndex: '1',
                baseUrl: ROOT.JSROOT,
                socket: null,
                menus: {
                    chat: {
                        text: "发送消息",
                        icon: "&#xe63a;",
                        callback: function(ele) {
                            var othis = ele.parent();
                            layui.layim.chat({
                                id: othis.data('id'),
                                name: othis.data('name'),
                                avatar: othis.data('img'),
                                type: othis.data('type'),
                            });
                        }
                    },
                    info: {
                        text: "查看资料",
                        icon: "&#xe62a;",
                        callback: function(ele) {
                            var othis = ele.parent(),id = othis.data('id');
                            ROOT.VM.userInfo(id)
                        }
                    },
                    record: {
                        text: "聊天记录",
                        icon: "&#xe62a;",
                        callback: function(ele) {
                            var othis = ele.parent();
                            ROOT.VM.chatRecord(othis.data())
                        }
                    },
                    remark: {
                        text: "设定备注",
                        icon: "&#xe6b2;",
                        callback: function(ele) {
                            var othis = ele.parent(),id = othis.data('id');
                            ROOT.VM.remark(id)
                        }
                    },
                    deleteFriend: {
                        text: "删除好友",
                        icon: "&#xe640;",
                        callback: function(ele) {
                            var othis = ele.parent();
                            ROOT.VM.deleteFriend(othis.data())
                        }
                    },
                    addMyGroup: {
                        text: "添加分组",
                        icon: "&#xe654;",
                        callback: function(ele) {
                            ROOT.VM.addMyGroup();
                        }
                    },
                    delMyGroup: {
                        text: "删除该组",
                        icon: "&#x1006;",
                        callback: function(ele) {
                            var othis = ele.parent(),mygroupIdx = othis.data('id');
                            ROOT.VM.delMyGroup(mygroupIdx);

                        }
                    },
                    renameMyGroup: {
                        text: "重命名",
                        icon: "&#xe642;",
                        callback: function(ele) {
                            var othis = ele.parent(),mygroupIdx = othis.data('id');

                            ROOT.VM.renameMyGroup(mygroupIdx);
                        }
                    },
                    moveFriend: function (html) {
                        return {
                            text: "移动联系人至",
                            icon: "&#xe630;",
                            nav: "move",        //子导航的样式
                            navIcon: "&#xe602;",//子导航的图标
                            navBody: html,      //子导航html
                            callback: function(ele) {
                                var othis = ele.parent(), friend_id = othis.data('id');
                                var sign = $('.layim-list-friend').find('#layim-friend'+friend_id).find('p').html();
                                var item = ele.find("ul li");
                                item.hover(function(e) {
                                    var target = $(e.currentTarget);
                                    var group = target.data('groupid');
                                    let data = {}
                                    layui.layim.cache().friend.forEach( (v, i) => {
                                        if (v.list.length > 0){
                                            v.list.forEach( (value, index) => {
                                                if (parseInt(value.id) === parseInt(friend_id)) {
                                                    data = value;
                                                }
                                            })
                                        }
                                    })
                                    data['type']    = 'friend';
                                    data['groupid'] = group;
                                    if (data === {}){
                                        layer.msg('系统异常')
                                        return;
                                    }
                                    ROOT.VM.moveFriend(friend_id, group, data);
                                });
                            }
                        }
                    },
                    quitGroup: {
                        text: "退出该群",
                        icon: "&#xe640;",
                        callback: function(ele) {
                            var othis = ele.parent();
                            ROOT.VM.quitGroup(othis.data())
                        }
                    }
                }
            }
        },
        methods: {
            initWebsocket(){
                var _this = this;
                _this.socket = new WebSocket('ws://chat.liulianjun.top:9501/ws?sessionid=' + ROOT.SESSIONID);
                _this.socket.onopen  = function () {
                    // console.log("websocket is connected")
                    // 心跳连接
                    ping = setInterval(function () {
                        _this.sendMessage('{"type":"ping"}');
                        // console.log("ping...");
                    },1000 * 10)
                }
                _this.socket.onmessage = function (res) {
                    var data = JSON.parse(res.data);
                    switch (data.type) {
                        case 'layer':
                            if (data.code === '200') {
                                layer.msg(data.msg)
                            } else {
                                layer.msg(data.msg,function(){})
                            }
                            break;
                        case 'token_expire':
                            console.log('token_expire');
                            window.location.reload();
                            break;
                        // 强制下线
                        case 'forceOffline':
                            _this.logout(1);
                            _this.socket.close();
                            layer.alert('您的账号已在其他地方登陆，请重新登陆！', {

                            }, function(){
                                window.location.href = _this.baseUrl + 'login'
                            });
                            break;
                        // 消息盒子
                        case 'msgBox':
                            if(data.count > 0){
                                layui.layim.msgbox(data.count)
                            }
                            break;
                        // 将新好友添加到列表
                        case 'addFriendToList':
                            layui.layim.addList(data.data);
                            break;
                        // 接受消息
                        case 'getMessage':
                            layui.layim.getMessage(data.data);
                            break;
                        // 将新群组添加到列表
                        case 'addGroupToList':
                            layui.layim.addList(data.data);
                            break;
                        // 将好友从列表删除
                        case 'removeFriend':
                            layui.layim.removeList(data.data)
                    }
                }
            },
            sendMessage(data) {
                console.log(data);
                var _this = this;
                var readyState = _this.socket.readyState;
                // console.log("连接状态码："+readyState);
                _this.socket.send(data)
            },
            // 初始化layIM
            initLayui() {
                var _this = this;
                var LayuiDir = requirejs.s.contexts._.config.baseUrl + 'lib/plugs/layim/dist/';
                layui.config({
                    dir: LayuiDir
                })

                layui.use(['layim', 'contextMenu'], function (layim, contextMenu) {

                    //基础配置
                    layim.config({
                        //初始化接口
                        init: {
                            url: _this.baseUrl + 'getList'
                            // url: LayuiDir + 'json/getList.json'
                            ,data: {}
                        }

                        //查看群员接口
                        ,members: {
                            url: _this.baseUrl + 'groupMembers'
                            // url: LayuiDir + 'json/getMembers.json'
                            ,data: {}
                        }

                        //上传图片接口
                        ,uploadImage: {
                            url: _this.baseUrl + 'uploadImage' //（返回的数据格式见下文）
                            ,type: '' //默认post
                        }

                        //上传文件接口
                        ,uploadFile: {
                            url: _this.baseUrl + 'uploadFile' //（返回的数据格式见下文）
                            ,type: '' //默认post
                        }

                        ,isAudio: true //开启聊天工具栏音频
                        ,isVideo: true //开启聊天工具栏视频

                        //扩展工具栏
                        ,tool: [{
                            alias: 'code'
                            ,title: '代码'
                            ,icon: '&#xe64e;'
                        }]

                        //,brief: true //是否简约模式（若开启则不显示主面板）

                        ,title: 'WebIM' //自定义主面板最小化时的标题
                        //,right: '100px' //主面板相对浏览器右侧距离
                        //,minRight: '90px' //聊天面板最小化时相对浏览器右侧距离
                        ,initSkin: '3.jpg' //1-5 设置初始背景
                        // ,skin: ['aaa.jpg'] //新增皮肤
                        ,isfriend: true //是否开启好友
                        ,isgroup: true //是否开启群组
                        // ,min: true //是否始终最小化主面板，默认false
                        ,notice: true //是否开启桌面消息提醒，默认false
                        ,voice: false //声音提醒，默认开启，声音文件为：default.mp3
                        // ,copyright: true
                        // ,msgbox: LayuiDir + 'css/modules/layim/html/msgbox.html' //消息盒子页面地址，若不开启，剔除该项即可
                        ,msgbox: _this.baseUrl + 'message' // 消息盒子页面地址，若不开启，剔除该项即可
                        ,find: _this.baseUrl + 'find' //发现页面地址，若不开启，剔除该项即可
                        ,chatLog: _this.baseUrl + 'chatRecord' //聊天记录页面地址，若不开启，剔除该项即可

                    });
                    layim.on('online', function(data){
                        //console.log(data);

                    });

                    //监听签名修改
                    layim.on('sign', function(value){
                        axios.post(ROOT.JSROOT + 'sign', {
                            sign: value
                        }).then( (res) => {
                            let result = res.data;
                            if (!result.code) {
                                layer.msg(result.message || '修改失败', () => {});
                            }
                        })
                    });

                    //监听自定义工具栏点击，以添加代码为例
                    layim.on('tool(code)', function(insert){
                        layer.prompt({
                            title: '插入代码'
                            ,formType: 2
                            ,shade: 0
                        }, function(text, index){
                            layer.close(index);
                            insert('[pre class=layui-code]' + text + '[/pre]'); //将内容插入到编辑器
                        });
                    });


                    //监听layim建立就绪
                    layim.on('ready', function(res){
                        _this.initWebsocket();
                        _this.contextMenu();
                    });

                    $(document).on('click', '.layui-layim-user', function () {
                        _this.userInfo()
                    })

                    //监听发送消息
                    layim.on('sendMessage', function(data){
                        var To = data.to;
                        if(To.type === 'friend'){
                            _this.sendMessage(JSON.stringify({
                                type: 'chat',
                                to: 'friend',
                                friend_id: To.id,
                                content: data.mine.content
                            }))
                            // layim.setChatStatus('<span style="color:#FF5722;">对方正在输入。。。</span>');
                        }else {
                            _this.sendMessage(JSON.stringify({
                                type: 'chat',
                                to: 'group',
                                group_id: To.id,
                                content: data.mine.content
                            }))
                        }
                    });

                    //监听查看群员
                    layim.on('members', function(data){

                    });

                    //监听聊天窗口的切换
                    layim.on('chatChange', function(res){
                        var type = res.data.type;
                        if(type === 'friend'){
                            //模拟标注好友状态
                            //layim.setChatStatus('<span style="color:#FF5722;">在线</span>');
                        } else if(type === 'group'){
                            //模拟系统消息
                        }
                    });

                })
            },
            // 右键菜单
            contextMenu() {
                var _this = this;
                let my_spread = $('.layim-list-friend > li');

                // 好友右键事件
                my_spread.mousedown( (e) => {
                    var data = {
                        contextItem: "context-friend", // 添加class
                        target: function(ele) { // 当前元素
                            $(".context-friend").attr("data-id",ele[0].id.replace(/[^0-9]/ig,"")).attr("data-name",ele.find("span").html());
                            $(".context-friend").attr("data-img",ele.find("img").attr('src')).attr("data-type",'friend');
                        },
                        menu:[]
                    };
                    data.menu.push(_this.menus.chat);
                    data.menu.push(_this.menus.info);
                    data.menu.push(_this.menus.record);
                    data.menu.push(_this.menus.remark);
                    data.menu.push(_this.menus.deleteFriend);
                    //当前分组id
                    let target = e.currentTarget;
                    let group = $($(target).find('h5')[0]).data('groupid');
                    if(my_spread.length >= 2){ //当至少有两个分组时
                        var html = '<ul>';
                        for (var i = 0; i < my_spread.length; i++) {
                            var groupid = my_spread.eq(i).find('h5').data('groupid');
                            if (parseInt(group) !== parseInt(groupid)) {
                                var groupName = my_spread.eq(i).find('h5 span').html();
                                html += '<li class="ui-move-menu-item" data-groupid="'+groupid+'" data-groupName="'+groupName+'"><a href="javascript:void(0);"><span>'+groupName+'</span></a></li>'
                            }
                        }
                        html += '</ul>';
                        data.menu.push(_this.menus.moveFriend(html));
                    }
                    $(".layim-list-friend >li > ul > li").contextMenu(data);
                });

                // 好友分组右键事件
                $(".layim-list-friend >li > h5").mousedown(function(e){
                    var data = {
                        contextItem: "context-mygroup", // 添加class
                        target: function(ele) { // 当前元素
                            $(".context-mygroup").attr("data-id",ele.data('groupid')).attr("data-name",ele.find("span").html());
                        },
                        menu: []
                    };
                    data.menu.push(_this.menus.addMyGroup);
                    data.menu.push(_this.menus.renameMyGroup);

                    if ($(this).parent().find('ul li').data('index') !== 0) {data.menu.push(_this.menus.delMyGroup); };
                    $(this).contextMenu(data);
                });

                // 群组右键事件
                $(".layim-list-group > li").mousedown(function(e){
                    var data = {
                        contextItem: "context-group", // 添加class
                        target: function(ele) { // 当前元素
                            $(".context-group").attr("data-id",ele[0].id.replace(/[^0-9]/ig,"")).attr("data-name",ele.find("span").html())
                                .attr("data-img",ele.find("img").attr('src')).attr("data-type",'group')
                        },
                        menu: []
                    };
                    data.menu.push(_this.menus.chat);
                    data.menu.push(_this.menus.record);
                    data.menu.push(_this.menus.quitGroup);

                    $(this).contextMenu(data);  //面板群组右键事件
                });
            },
            // 删除好友
            deleteFriend(data) {
                layer.confirm('删除后你将从对方联系人列表中消失？', {
                    title: '删除好友'
                }, function(index){
                    // console.log(data);
                    axios.post(ROOT.JSROOT + 'deleteFriend', {
                        friend: data.id,
                    }).then( (response) => {
                        let result = response.data;
                        if (result.code) {
                            layui.layim.removeList({
                                id: data.id,
                                type: 'friend'
                            })
                        } else {
                            layer.msg(result.message || '系统繁忙!', () => {})
                        }
                    }).catch( (error) => {
                        console.log(error);
                    });
                    layer.close(index)
                });
            },
            // 添加好友分组
            addMyGroup() {
                var _this = this;
                layer.prompt({title: '输入分组名称，并确认'}, function(val, index){
                    axios.post(ROOT.JSROOT + 'addMyGroup', {
                        name: val
                    }).then( (response) => {
                        let result = response.data;
                        if (result.code){
                            layer.close(index);
                            window.location.reload();
                        } else {
                            layer.msg(result.message || '系统繁忙');
                        }
                    }).catch( (error) => {

                    })
                    // layer.close(index);
                });
            },
            // 删除好友分组
            delMyGroup(groupid) {
                layer.confirm('选定的分组将被删除，组内联系人将会移至默认分组。', {
                    title:['删除分组'],
                    shade: 0
                }, function(){
                    axios.post(ROOT.JSROOT + 'delMyGroup', {
                        group: groupid
                    }).then( (response) => {
                        let result = response.data;
                        if (result.code){
                            window.location.reload();
                        } else {
                            layer.msg(result.message || '系统繁忙');
                        }
                    }).catch( (error) => {
                        console.log(error);
                    })
                });

            },
            // 重命名好友分组
            renameMyGroup(groupid) {
                layer.prompt({title: '输入分组名称，并确认'}, function(val, index){
                    axios.post(ROOT.JSROOT + 'renameMyGroup', {
                        group: groupid,
                        name: val
                    }).then( (response) => {
                        let result = response.data;
                        if (result.code){
                            var group = $('ul.layim-list-friend li');
                            for(var j = 0; j < group.length; j++){
                                var groupidx = group.eq(j).find('h5').data('groupid');
                                //当前选择的分组
                                if(parseInt(groupidx) === parseInt(groupid)){
                                    group.eq(j).find('h5').find('span').html(val);
                                }
                            }
                            layer.close(index);
                        } else {
                            layer.msg(result.message || '系统繁忙');
                        }
                    }).catch( (error) => {
                        console.log(error);
                    })
                });
            },
            // 移动好友到指定分组
            moveFriend(friend_id, group_id, data) {
                axios.post(ROOT.JSROOT + 'moveFriend', {
                    friend_id: friend_id,
                    group_id: group_id
                }).then( (response) => {
                    let result = response.data;
                    if (result.code){
                        // 将好友从之前分组移去
                        layui.layim.removeList({
                            type: 'friend'
                            ,id: friend_id
                        })
                        // 移动到新分组
                        layui.layim.addList(data);
                    } else {
                        layer.msg(result.message || '系统繁忙');
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            // 资料
            userInfo(friend_id = 0) {
                layer.open({
                    type: 2
                    ,title: friend_id > 0 ? '好友资料' : '我的资料'
                    ,shade: false
                    ,maxmin: false
                    // ,closeBtn: 0
                    ,area: ['500px', '670px']
                    ,skin: 'layui-box layui-layer-border'
                    ,resize: true
                    ,content: ROOT.JSROOT + 'userInfo/' + friend_id
                });
            },
            // 设置备注
            remark(user_id) {
                var _this = this;
                layer.prompt({title: '设定备注'}, function(val, index){
                    axios.post(ROOT.JSROOT + 'remarkFriend', {
                        remark: val,
                        friend: user_id
                    }).then( (response) => {
                        let result = response.data;
                        if (result.code){
                            let obj = $('#layim-friend'+user_id);
                            nickname = obj.find('.nickname').html();
                            obj.find('.nickname').html(val);
                            obj.find('.remark').html('(' + nickname + ')');
                        } else {
                            layer.msg(result.message || '系统繁忙');
                        }
                    }).catch( (error) => {
                        console.log(error);
                    });
                    layer.close(index);
                });
            },
            // 聊天记录
            chatRecord(data) {
                layer.open({
                    type: 2,
                    maxmin: !0,
                    title: "与 " + data.name + " 的聊天记录",
                    area: ["450px", "100%"],
                    shade: !1,
                    offset: "rb",
                    skin: "layui-box",
                    anim: 2,
                    id: "layui-layim-chatlog",
                    content: layui.layim.cache().base.chatLog + "?id=" + data.id + "&type=" + data.type
                })
            },
            // 退出群组
            quitGroup(data) {
                layer.confirm('您真的要退出该群吗？', {
                    title: '提示'
                }, function(index){
                    // console.log(data);
                    axios.post(ROOT.JSROOT + 'quitGroup', {
                        group: data.id,
                    }).then( (response) => {
                        let result = response.data;
                        if (result.code) {
                            layui.layim.removeList({
                                id: data.id,
                                type: 'group'
                            })
                        } else {
                            layer.msg(result.message || '系统繁忙!', () => {})
                        }
                    }).catch( (error) => {
                        console.log(error);
                    });
                    layer.close(index)
                });
            },
            // 退出登录
            logout(type = 0) {
                var _this = this;
                axios.post(ROOT.URL_LOGOUT).then( (response) => {
                    let result = response.data;
                    if (parseInt(type) === 1) return false;
                    if (result.code){
                        _this.$message({
                            showClose: true,
                            message: '退出成功',
                            duration: 1000,
                            type: 'success',
                            onClose: function () {
                                window.location.href = ROOT.URL_LOGIN
                            }
                        })
                    }else {
                        _this.$message({
                            showClose: true,
                            message: result.message,
                            duration: 1000,
                            type: 'error',
                        })
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            // 修改密码
            changePwd() {
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim', //加上边框
                    area: ['420px', '240px'], //宽高
                    content: 'html内容'
                });
            }
        },
        mounted() {
            this.initLayui();
        },
    })
});