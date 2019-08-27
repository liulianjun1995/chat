<!doctype html>
<html lang="en">
<head>
    @include('home.layout.head')
</head>
<body>
<div id="msgbox" v-cloak>
    <ul class="layim-msgbox">
        <template v-for="(item, index) in list">
            <li v-if="item.type == 1">
                <a href="#">
                    <img :src="item.avatar" class="layui-circle layim-msgbox-avatar">
                </a>
                <p class="layim-msgbox-user">
                    <a>@{{ item.nickname }}</a>
                    <span>@{{ item.time }}</span>
                </p>
                <p class="layim-msgbox-content">
                    @{{ item.content }}
                    <span>@{{ item.remark ? '附言: '+item.remark : '' }}</span>
                </p>
                <p class="layim-msgbox-btn">
                    <template v-if="item.status == 1">
                        <button class="layui-btn layui-btn-small" @click="agree(item)">同意</button>
                        <button class="layui-btn layui-btn-small layui-btn-primary" @click="refuse(item)">拒绝</button>
                    </template>
                    <template v-else-if="item.status == 2">
                        已同意
                    </template>
                    <template v-else-if="item.status == 3">
                        已拒绝
                    </template>
                </p>
            </li>
            <li v-else-if="item.type == 2" class="layim-msgbox-system">
                <p class="layim-msgbox-content" style="overflow: hidden;line-height: 38px">
                    <em>系统：</em><a href="javascript:" class="fly-link">@{{ item.nickname }}</a> @{{ item.content }}
                    <span>@{{ item.time }}</span>
                    <button v-if="item.status == 2" class="layui-btn layui-btn-small" style="float: right" @click="chat(item)">发起会话</button>
                </p>
            </li>
            <li v-else-if="item.type == 3" class="layim-msgbox-system">
                <p class="layim-msgbox-content" style="overflow: hidden;line-height: 38px">
                    <em>系统：</em><a href="javascript:" class="fly-link">@{{ item.nickname }}</a> @{{ item.content }} <a href="javascript:" class="fly-link">@{{ item.group_name }}</a>
                    <span>@{{ item.time }}</span>
                    <template v-if="item.status == 1">
                        <button class="layui-btn layui-btn-small" style="float: right" >拒绝</button>
                        <button class="layui-btn layui-btn-small" style="float: right" @click="agreeApplyGroup(item)">同意</button>
                    </template>
                    <template v-else-if="item.status == 2">
                        <span style="float: right">已同意</span>
                    </template>
                    <template v-else-if="item.status == 3">
                        <span style="float: right">已拒绝</span>
                    </template>
                </p>
            </li>
            <li v-else-if="item.type == 4" class="layim-msgbox-system">
                <p class="layim-msgbox-content" style="overflow: hidden;line-height: 38px">
                    <em>系统：</em><a href="javascript:" class="fly-link">@{{ item.nickname }}</a> @{{ item.content }} <a href="javascript:" class="fly-link">@{{ item.group_name }}</a>
                    <span>@{{ item.time }}</span>
                    <button v-if="item.status == 2" class="layui-btn layui-btn-small" style="float: right" @click="chatGroup(item)">发起会话</button>
                </p>
            </li>
            <li v-else-if="item.type == 6" class="layim-msgbox-system">
                <p class="layim-msgbox-content" style="overflow: hidden;line-height: 38px">
                    <em>系统：</em><a href="javascript:" class="fly-link">@{{ item.nickname }}</a> @{{ item.content }} <a href="javascript:" class="fly-link">@{{ item.group_name }}</a>
                    <span>@{{ item.time }}</span>
                    <template v-if="item.status == 1">
                        <button class="layui-btn layui-btn-small" style="float: right" >拒绝</button>
                        <button class="layui-btn layui-btn-small" style="float: right" @click="agreeGroup(item)">同意</button>
                    </template>
                    <template v-else-if="item.status == 2">
                        <span style="float: right">已同意</span>
                    </template>
                    <template v-else-if="item.status == 3">
                        <span style="float: right">已拒绝</span>
                    </template>
                </p>
            </li>
            <li v-else-if="item.type == 8" class="layim-msgbox-system">
                <p class="layim-msgbox-content" style="overflow: hidden;line-height: 38px">
                    <em>系统：</em><a href="javascript:" class="fly-link">@{{ item.nickname }}</a> @{{ item.content }} <a href="javascript:" class="fly-link">@{{ item.group_name }}</a>
                    <span>@{{ item.time }}</span>
                </p>
            </li>
        </template>
    </ul>
</div>
</body>
</html>
@include('home.layout.js')
<script>
    require(['lib/msgbox']);
</script>
