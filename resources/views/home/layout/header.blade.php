<el-header>
    <el-menu
            :default-active="activeIndex"
            class="el-menu-demo"
            mode="horizontal"
            background-color="#545c64"
            text-color="#FFFFFF"
            active-text-color="#FFFFFF"
    >
        <el-menu-item index="1">Chat</el-menu-item>
        <el-menu-item index="3" style="float: right">
            <el-dropdown style="color: #FFFFFF; cursor: pointer;text-align: center">
                <span>
                    {{ Auth::user()->nickname }}
                    <img src="{{ auth()->user()->avatar }}" width="40" height="40" style="border-radius: 45px">
                </span>
                <el-dropdown-menu slot="dropdown">
{{--                    <el-dropdown-item @click.native="changePwd">修改密码</el-dropdown-item>--}}
                    <el-dropdown-item @click.native="logout">退出登录</el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </el-menu-item>
    </el-menu>
</el-header>