define(["jquery", 'vue', 'layer', 'axios', 'ELEMENT', 'layui'],function($ , Vue, layer, axios, ELEMENT, undefined) {

    Vue.use(ELEMENT);

    new Vue({
        el: '#find',
        data: function() {
            return {
                type: 'user',
                userKeyword: '',
                groupKeyword: '',
                users: [],
                groups: []
            }
        },
        methods: {
            // 查找好友
            searchUser() {
                let _this = this;
                axios.post(ROOT.JSROOT + 'find', {
                    keyword: _this.userKeyword,
                    type: 'user'
                }).then( (response) => {
                    let result = response.data;
                    if (result.code){
                        _this.users = result.data.list.data;
                    } else {
                        layer.msg(result.message)
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            // 查找群
            searchGroup() {
                let _this = this;
                axios.post(ROOT.JSROOT + 'find', {
                    keyword: _this.groupKeyword,
                    type: 'group'
                }).then( (response) => {
                    let result = response.data;
                    if (result.code){
                        _this.groups = result.data.list.data;
                    } else {
                        layer.msg(result.message)
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            // 添加好友
            addFriend(item) {
                let _this = this;
                layui.use('layim', function(layim){
                    layim.add({
                        type: 'friend' //friend：申请加好友、group：申请加群
                        ,username: item.nickname //好友昵称，若申请加群，参数为：groupname
                        ,avatar: item.avatar //头像
                        ,group:  parent.layui.layim.cache().friend || []
                        ,submit: function(group, remark, index){ //一般在此执行Ajax和WS，以通知对方
                            let data = {
                                type: "addFriend",
                                to_user_id: item.id,
                                to_friend_group_id: group,
                                remark: remark
                            }
                            parent.ROOT.VM.sendMessage(JSON.stringify(data))
                            layer.close(index); //关闭改面板
                        }
                    });
                });

            },
            // 创建群
            createGroup() {
                parent.layer.open({
                    type: 2
                    ,title: '创建群'
                    ,shade: false
                    ,maxmin: false
                    // ,closeBtn: 0
                    ,area: ['600px', '670px']
                    ,skin: 'layui-box layui-layer-border'
                    ,resize: true
                    ,content: ROOT.JSROOT + 'createGroup'
                });
            },
            // 申请加群
            applyGroup(item) {
                // console.log(item);
                layui.use('layim', function(layim){
                    layim.add({
                        type: 'group' //friend：申请加好友、group：申请加群
                        ,groupname: item.name //好友昵称，若申请加群，参数为：groupname
                        ,avatar: item.avatar //头像
                        ,submit: function(group, remark, index){ //一般在此执行Ajax和WS，以通知对方
                            // console.log(remark);
                            let data = {
                                type: "applyGroup",
                                to_group_id: item.id,
                                remark: remark
                            };
                            parent.ROOT.VM.sendMessage(JSON.stringify(data));
                            layer.close(index); //关闭改面板
                        }
                    });
                });
            }
        },
    })
});