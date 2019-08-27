define(["jquery", 'vue', 'layer', 'axios', 'ELEMENT', 'layui'],function($ , Vue, layer, axios, ELEMENT, undefined) {

    Vue.use(ELEMENT);

    new Vue({
        el: '#msgbox',
        data: function() {
            return {
                list: []
            }
        },
        methods: {
            fetchData() {
                var _this = this;
                axios.post(ROOT.JSROOT + 'message').then( (response) => {
                    let result = response.data
                    if (result.code){
                        _this.list = result.data.list.data
                    } else {
                        layer.msg(result.message)
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            // 同意好友
            agree(item) {
                layui.use('layim', function(layim){
                    layim.setFriendGroup({
                        type: 'friend'
                        ,username: item.nickname
                        ,avatar: item.avatar
                        ,group: parent.layui.layim.cache().friend //获取好友列表数据
                        ,submit: function(group, index){
                            //同意后，将好友追加到主面板
                            axios.post(ROOT.JSROOT + 'addFriend', {
                                id: item.id,
                                friend_group: group
                            }).then( (response) => {
                                let result = response.data;
                                if (result.code){
                                    // 添加到好友列表
                                    parent.layui.layim.addList({
                                        type: 'friend'
                                        ,username: item.nickname
                                        ,avatar: item.avatar
                                        ,groupid: group //所在的分组id
                                        ,id: item.from_user_id //好友ID
                                        ,sign: item.sign //好友签名
                                    });
                                    // 通知对方已经同意申请
                                    let data = {
                                        type: "addFriendToList",
                                        id: item.id,
                                    }
                                    item.status = 2;
                                    parent.ROOT.VM.sendMessage(JSON.stringify(data))
                                } else {
                                    layer.msg(result.message || '请重试！', () => {});
                                }
                            }).catch( (error) => {
                                console.log(error);
                            })
                            layer.close(index);
                        }
                    });
                })
            },
            // 拒绝好友
            refuse(item) {
                axios.post(ROOT.JSROOT + 'refuseFriend', {
                    id: item.id,
                }).then( (response) => {
                    let result = response.data;
                    if (result.code){
                        item.status = 3;
                        // 通知已拒绝申请
                        parent.ROOT.VM.sendMessage(JSON.stringify({type:"refuseFriend",id: item.id}))
                    } else {
                        layer.msg(result.msg || '系统繁忙！')
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            // 同意群邀请
            agreeGroup(item) {
                axios.post(ROOT.JSROOT + 'agreeGroup', {
                    id: item.id,
                }).then( (response) => {
                    let result = response.data;
                    if (result.code){
                        let group = result.data.group;
                        item.status = 2;
                        // 通知已同意邀请
                        parent.ROOT.VM.sendMessage(JSON.stringify({type:"agreeGroup",id: item.id}))
                        // 添加群组到面板
                        parent.layui.layim.addList({
                            type: 'group',
                            avatar: group.avatar,
                            groupname: group.name,
                            id: group.id,
                            members: 0
                        });
                    } else {
                        layer.msg(result.msg || '系统繁忙！')
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            // 拒绝群邀请
            refuseGroup(item) {
                axios.post(ROOT.JSROOT + 'refuseGroup', {
                    id: item.id,
                }).then( (response) => {
                    let result = response.data;
                    if (result.code){
                        item.status = 3;
                        // 通知已拒绝邀请
                        parent.ROOT.VM.sendMessage(JSON.stringify({type:"refuseGroup",id: item.id}))
                    } else {
                        layer.msg(result.msg || '系统繁忙！', () => {})
                    }
                }).catch( (error) => {
                    layer.msg('系统繁忙', () => {})
                    console.log(error);
                })
            },
            // 同意群申请
            agreeApplyGroup(item) {
                axios.post(ROOT.JSROOT + 'agreeApplyGroup', {
                    id: item.id,
                }).then( (response) => {
                    let result = response.data;
                    if (result.code){
                        item.status = 2;
                    } else {
                        layer.msg(result.message || '系统繁忙！', () => {})
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            // 发起会话
            chat(item) {
                parent.layui.layim.chat({
                    name: item.nickname
                    ,type: 'friend'
                    ,avatar: item.avatar
                    ,id: item.from_user_id
                });
            },
            // 发起会话
            chatGroup(item) {
                parent.layui.layim.chat({
                    name: item.group_name
                    ,type: 'group'
                    ,avatar: item.group_avatar
                    ,id: item.group_id
                });
            }
        },
        mounted() {
            this.fetchData();
        }
    })
});