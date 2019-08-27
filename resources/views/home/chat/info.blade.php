<!doctype html>
<html lang="en" style="height: 100%">
<head>
    @include('home.layout.head')
</head>
<body>
<div id="info" v-cloak>
    @if (request()->route('id') == 0)
        <el-form v-show="show" ref="info" :model="form" :rules="rules" label-width="55px" style="height: 100%">
            <el-col>
                <el-form-item label="头  像">
                    <img :src="form.avatar" width='80' height="80">
                </el-form-item>
            </el-col>
            <el-col>
                <el-form-item label="昵  称">
                    <el-input style="width: 350px" v-model="form.nickname"></el-input>
                </el-form-item>
            </el-col>
            <el-col>
                <el-form-item label="签  名">
                    <el-input type="textarea" resize="none" maxlength="50" :rows="3" style="width: 350px" v-model="form.sign">@{{form.township}}</el-input>
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item label="性  别">
                    <el-select v-model="form.sex" placeholder="请选择" style="width: 140px">
                        <el-option
                                v-for="item in sex"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item label="生  日">
                    <el-date-picker type="date" v-model="form.birthday" style="width: 140px;"></el-date-picker>
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item label="所在地">
                    <el-select v-model="form.province" @change="fetchArea('city')" clearable filterable placeholder="请选择" style="width: 140px">
                        <el-option
                                v-for="item in provinces"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item>
                    <el-select v-model="form.city" @change="fetchArea('district')" clearable filterable  style="width: 140px">
                        <el-option
                                v-for="item in cities"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item>
                    <el-select v-model="form.district" @change="fetchArea('country')" clearable filterable style="width: 140px">
                        <el-option
                                v-for="item in districts"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item>
                    <el-select v-model="form.country" clearable filterable style="width: 140px">
                        <el-option
                                v-for="item in countries"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col>
                <el-form-item label="职业">
                    <el-select v-model="form.profession" clearable placeholder="请选择" style="width: 350px">
                        <el-option
                                v-for="item in professions"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col align="center">
                <el-button type="primary" @click="onSubmit">保存</el-button>
                <el-button type="primary" @click="cancel">取消</el-button>
            </el-col>
        </el-form>
    @else
        <el-form v-show="show" label-width="55px" style="height: 100%">
            <el-col>
                <el-form-item label="头  像">
                    <img :src="form.avatar" width='80' height="80">
                </el-form-item>
            </el-col>
            <el-col>
                <el-form-item label="昵  称">
                    <el-input style="width: 350px" readonly v-model="form.nickname"></el-input>
                </el-form-item>
            </el-col>
            <el-col>
                <el-form-item label="签  名">
                    <el-input type="textarea" readonly resize="none" maxlength="50" :rows="3" style="width: 350px" v-model="form.sign">@{{form.township}}</el-input>
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item label="性  别">
                    <el-input style="width: 140px" readonly v-model="form.sexFormat"></el-input>
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item label="生  日">
                    <el-date-picker type="date" disabled v-model="form.birthday" style="width: 140px;"></el-date-picker>
                </el-form-item>
            </el-col>
            <el-col>
                <el-form-item label="所在地">
                    <el-input style="width: 350px" readonly v-model="form.provinceFormat"></el-input>
                </el-form-item>
            </el-col>
            <el-col>
                <el-form-item label="职业">
                    <el-input style="width: 350px" readonly v-model="form.professionFormat"></el-input>
                </el-form-item>
            </el-col>
        </el-form>
    @endif
</div>
</body>
</html>
@include('home.layout.js')
<script>
    let error = '{{ $error ?? '' }}';
    let user_id = '{{ request()->route('id') }}';
    let URL_USERINFO = '{{ route('chat-userInfo', ['id' => request()->route('id')]) }}'
    require(['lib/info']);
</script>
