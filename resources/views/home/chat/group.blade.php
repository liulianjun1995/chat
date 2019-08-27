<!doctype html>
<html lang="en" style="height: 100%">
<head>
    @include('home.layout.head')
</head>
<body>
<div id="group" v-cloak>
    <el-steps :active="step" simple>
        <el-step title="选择类别" icon="el-icon-ali-1"></el-step>
        <el-step title="填写信息" icon="el-icon-ali-2"></el-step>
        <el-step title="邀请好友" icon="el-icon-ali-3"></el-step>
    </el-steps>
    <div class="type" v-show="step == 1">
        <el-row>
            <el-col :span="11" class="box" @click.native="groupType(1)">
                <div class="item">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_1.png') }}">
                    </div>
                    <div class="msg">
                        <p class="username">
                            <span style="color: #F92900">同学同事</span><br>
                            <span style="color: #000;">同学、亲友、办公</span>
                        </p>
                    </div>
                </div>
            </el-col>
            <el-col :span="2"><div class="item"></div></el-col>
            <el-col :span="11" class="box" @click.native="groupType(2)">
                <div class="item">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_2.png') }}">
                    </div>
                    <div class="msg">
                        <p class="username">
                            <span style="color: #F92900">家校师生</span><br>
                            <span style="color: #000;">班主任、老师、家长</span>
                        </p>
                    </div>
                </div>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="5" class="box" @click.native="groupType(3)">
                <div class="item-2" style="text-align: center">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_3.png') }}">
                    </div>
                    <div class="msg">
                        <span style="color: #F92900">兴趣爱好</span><br>
                    </div>
                </div>
            </el-col>
            <el-col :span="1"><div class="item"></div></el-col>
            <el-col :span="5" class="box" @click.native="groupType(4)">
                <div class="item-2" style="text-align: center">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_4.png') }}">
                    </div>
                    <div class="msg">
                        <span style="color: #F92900">行业交流</span><br>
                    </div>
                </div>
            </el-col>
            <el-col :span="2"><div class="item"></div></el-col>
            <el-col :span="5" class="box" @click.native="groupType(5)">
                <div class="item-2" style="text-align: center">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_5.png') }}">
                    </div>
                    <div class="msg">
                        <span style="color: #F92900">生活休闲</span><br>
                    </div>
                </div>
            </el-col>
            <el-col :span="1"><div class="item"></div></el-col>
            <el-col :span="5" class="box" @click.native="groupType(6)">
                <div class="item-2" style="text-align: center">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_6.png') }}">
                    </div>
                    <div class="msg">
                        <span style="color: #F92900">学习考试</span><br>
                    </div>
                </div>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="5" class="box" @click.native="groupType(7)">
                <div class="item-2" style="text-align: center">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_7.png') }}">
                    </div>
                    <div class="msg">
                        <span style="color: #F92900">游戏</span><br>
                    </div>
                </div>
            </el-col>
            <el-col :span="1"><div class="item"></div></el-col>
            <el-col :span="5" class="box" @click.native="groupType(8)">
                <div class="item-2" style="text-align: center">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_8.png') }}">
                    </div>
                    <div class="msg">
                        <span style="color: #F92900">置业安家</span><br>
                    </div>
                </div>
            </el-col>
            <el-col :span="2"><div class="item"></div></el-col>
            <el-col :span="5" class="box" @click.native="groupType(9)">
                <div class="item-2" style="text-align: center">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_9.png') }}">
                    </div>
                    <div class="msg">
                        <span style="color: #F92900">品牌产品</span><br>
                    </div>
                </div>
            </el-col>
            <el-col :span="1"><div class="item"></div></el-col>
            <el-col :span="5" class="box" @click.native="groupType(10)">
                <div class="item-2" style="text-align: center">
                    <div class="img">
                        <img width="50" height="50" src="{{ asset('storage/img/group_type_10.png') }}">
                    </div>
                    <div class="msg">
                        <span style="color: #F92900">粉丝</span><br>
                    </div>
                </div>
            </el-col>
        </el-row>
    </div>
    <div class="info" v-show="step == 2">
        <el-form ref="groupForm" :model="form" :rules="rules" label-width="80px" style="height: 100%">
            <el-form-item label="群名称" prop="name">
                <el-input style="width: 350px" placeholder="为你们的群起个给力的名字吧！" maxlength="20" v-model="form.name"></el-input>
            </el-form-item>
            <el-form-item label="群规模" prop="size">
                <el-radio-group v-model="form.size">
                    <el-radio :label="100">100人</el-radio>
                    <el-radio :label="200">200人</el-radio>
                    <el-radio :label="500">500人</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item label="加群验证" prop="verify">
                <el-radio-group v-model="form.verify">
                    <el-radio :label="1">允许任何人</el-radio>
                    <el-radio :label="2">需身份验证</el-radio>
                    <el-radio :label="3">不允许任何人</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-col align="right">
                <el-button size="mini" @click="prevStep">上一步</el-button>
                <el-button size="mini" @click="nextStep">下一步</el-button>
            </el-col>
        </el-form>
    </div>
    <div class="member" v-show="step == 3">
        <el-transfer
{{--                filterable--}}
{{--                :filter-method="filterMethod"--}}
{{--                filter-placeholder="请输入城市拼音"--}}
                :titles="['我的好友', '已选成员']"
                v-model="form.users"
                :data="users">
        </el-transfer>
        <el-col align="right" style="margin-top: 20px">
            <el-button size="mini" @click="prevStep">上一步</el-button>
            <el-button size="mini" @click="onSubmit" :disabled="disabledSubmit">完成创建</el-button>
        </el-col>
    </div>
</div>
</body>
</html>
@include('home.layout.js')
<script>
    require(['lib/group']);
</script>
