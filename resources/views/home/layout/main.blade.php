<!doctype html>
<html lang="en" style="height: 100%">
<head>
    @include('home.layout.head')
</head>
<body>
    <div id="main">
        <el-container v-cloak>
            @include('home.layout.header')
            <el-main>
                @yield('content')
            </el-main>
        </el-container>
    </div>
</body>
</html>
@include('home.layout.js')
<!-- js -->
@yield('script')
