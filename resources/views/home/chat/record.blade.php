<!doctype html>
<html lang="en" style="height: 100%;">
<head>
    @include('home.layout.head')
    <link rel="stylesheet" href="{{ asset('js/lib/plugs/layim/dist/css/modules/layim/layim.css') }}">
</head>
<body style="height: 100%">
    <div id="chat_record" class="layim-chat-main" style="height: calc(100% - 5px);" v-cloak>
        <ul id="LAY_view" v-infinite-scroll="load">
            <template v-for="(item, index) in list">
                <li class="layim-chat-mine" v-if="item.mine == true">
                    <div class="layim-chat-user">
                        <img src="{{ Auth::user()->avatar }}"><cite><i>@{{ item.created_at }}</i>{{ Auth::user()->nickname }}</cite>
                    </div>
                    <div class="layim-chat-text" v-html="item.content"></div>
                </li>
                <li v-else>
                    <div class="layim-chat-user">
                        <img :src="item.friend.avatar"><cite>@{{item.friend.nickname}}<i>@{{item.created_at}}</i></cite>
                    </div>
                    <div class="layim-chat-text" v-html="item.content"></div>
                </li>
            </template>
        </ul>
    </div>
</body>
</html>
@include('home.layout.js')
<script>
    require(['lib/chatRecord']);
</script>
<body>
