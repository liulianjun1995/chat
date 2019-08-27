<!doctype html>
<html lang="en">
<head>
    @include('home.layout.head')
</head>
<body>
<div id="find" v-cloak>
    <div class="find-main">
        <el-tabs v-model="type">
            <el-tab-pane style="text-align: center" label="找好友" name="user">
                <div class="find-view">
                    <el-input prefix-icon="el-icon-search" v-model="userKeyword" placeholder="请输入ID/昵称" style="width: 60%" v-model="keyword"></el-input>
                    <el-button size="small" type="primary" round @click="searchUser">查找</el-button>
                </div>
                <div class="item-list">
                    <div class="item" v-for="(item, index) in users">
                        <div class="img">
                            <img width="50" height="50" :src="item.avatar">
                        </div>
                        <div class="msg">
                            <p class="username" :title="item.nickname + '(' + item.id + ')'">
                                <span style="color: #F92900">@{{ item.nickname }}</span>
                                <span style="color: #000;">(@{{ item.id }})</span>
                            </p>
                            <p class="position"><i :class="item.sex == 1 ? 'el-icon-male' : 'el-icon-female'"></i>河南 信阳</p>
                            <el-button style="padding: 4px 15px" size="mini" type="primary" round @click="addFriend(item)">加好友</el-button>
                        </div>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane style="text-align: center" label="找群" name="group">
                <div class="find-view">
                    <el-input prefix-icon="el-icon-search" v-model="groupKeyword" placeholder="请输入群号码/群名称" style="width: 60%" v-model="keyword"></el-input>
                    <el-button size="small" type="primary" round @click="searchGroup">查找</el-button>
                    <el-button size="small" type="primary" round @click="createGroup">创建群</el-button>
                </div>
                <div class="item-list">
                    <template v-for="(item, index) in groups">
                        <el-col :span="1"><div class="item" style="height: 200px;width: 100%"></div></el-col>
                        <el-col :span="10">
                            <div class="item" style="height: 200px;width: 100%">
                                <div class="img">
                                    <img width="50" height="50" :src="item.avatar">
                                </div>
                                <div class="msg">
                                    <p class="username" :title="item.name + '(' + item.id + ')'">
                                        <span style="color: #F92900">@{{ item.name }}</span>
                                        <span style="color: #000;">(@{{ item.id }})</span>
                                    </p>
                                    <p class="position"><i class="el-icon-ali-friends"></i> @{{ item.members_count }}/@{{ item.size }}</p>
                                    <p class="label">品牌|产品|行业交流</p>
                                </div>
                                <div style="clear: both"></div>
                                <p class="description" style="text-align: left; margin-bottom: 5px" title="欢迎加入我们进行互相技术交流！请发布健康积极的内容，大家互相监督，发现违规内容可以通知群主或者群管理进行处理，谢谢！">
                                    欢迎加入我们进行互相技术交流！请发布健康积极的内容，大家互相监督，发现违规内容可以通知群主或者群管理进行处理，谢谢！
                                </p>
                                <el-button style="padding: 4px 15px; float: right" size="mini" type="primary" round @click="applyGroup(item)">加入群</el-button>
                            </div>
                        </el-col>
                        <el-col :span="1"><div class="item" style="height: 200px;width: 100%"></div></el-col>
                    </template>
                </div>
            </el-tab-pane>
        </el-tabs>
    </div>
</div>
</body>
</html>
@include('home.layout.js')
<script>
    require(['lib/find']);
</script>
