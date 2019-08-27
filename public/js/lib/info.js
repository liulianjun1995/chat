define(["jquery", 'vue', 'layer', 'axios', 'ELEMENT'],function($ , Vue, layer, axios, ELEMENT) {

    Vue.use(ELEMENT);

    new Vue({
        el: '#info',
        data: function() {
            return {
                form: {
                    nickname: '',
                    sign: '',
                    sex: 1,
                    birthday: '',
                    province: '',
                    city: '',
                    district: '',
                    country: '',
                    profession: ''
                },
                rules: {
                    nickname: [
                        { required: true, message: '请输入昵称', trigger: 'blur' },
                        { min: 3, max: 24, message: '长度在 3 到 24 个字符', trigger: 'blur' }
                    ],
                },
                show: true,
                sex: [
                    {
                        value: 1,
                        label: '男'
                    },
                    {
                        value: 2,
                        label: '女'
                    }
                ],
                professions: [
                    {
                        value: 1,
                        label: '计算机/互联网/通信',
                    },
                    {
                        value: 2,
                        label: '生产/工艺/制造',
                    },
                    {
                        value: 3,
                        label: '医疗/护理/制药',
                    },
                    {
                        value: 4,
                        label: '金融/银行/投资/保险',
                    },
                    {
                        value: 5,
                        label: '商业/服务业/个体经营',
                    },
                    {
                        value: 6,
                        label: '文化/广告/传媒',
                    },
                    {
                        value: 7,
                        label: '娱乐/艺术/表演',
                    },
                    {
                        value: 8,
                        label: '律师/法务',
                    },
                    {
                        value: 9,
                        label: '教育/培训',
                    },
                    {
                        value: 10,
                        label: '公务员/行政/事业单位',
                    },
                    {
                        value: 11,
                        label: '模特',
                    },
                    {
                        value: 12,
                        label: '空姐',
                    },
                    {
                        value: 13,
                        label: '学生',
                    },
                    {
                        value: 14,
                        label: '其他',
                    },
                ],
                provinces: [],
                cities: [],
                districts: [],
                countries: []
            }
        },
        methods: {
            fetchArea(type = 'province', pid = 0) {
                let _this = this;
                return new Promise(((resolve, reject) => {
                    switch (type) {
                        case 'province':
                            pid = 1;
                            break;
                        case 'city':
                            pid = pid ? pid : _this.form.province
                            break;
                        case 'district':
                            pid = pid ? pid : _this.form.city
                            break;
                        case 'country':
                            pid = pid ? pid : _this.form.district
                            break;
                    }

                    if (!pid){
                        switch (type) {
                            case 'city':
                                _this.form.city = ''
                                _this.form.district = ''
                                _this.form.country = ''
                                _this.form.cities = []
                                _this.districts = []
                                _this.countries = []
                                break;
                            case 'district':
                                _this.form.country = ''
                                _this.form.district = ''
                                _this.countries = []
                                _this.districts = []
                                break;
                            case 'country':
                                _this.form.country = ''
                                _this.countries = [];
                                break;
                        }
                        return false;
                    }

                    axios.get(ROOT.JSROOT + 'area', {
                        params: {
                            type,
                            pid
                        }
                    }).then( (response) => {
                        let result = response.data;
                        if (result.code){
                            switch (type) {
                                case 'province':
                                    _this.provinces = result.data.list;
                                    break;
                                case 'city':
                                    _this.cities = result.data.list;
                                    _this.form.city = ''
                                    _this.form.district = ''
                                    _this.form.country = ''
                                    _this.districts = []
                                    _this.countries = []
                                    break;
                                case 'district':
                                    _this.districts = result.data.list;
                                    _this.form.country = ''
                                    _this.form.district = ''
                                    _this.countries = []
                                    break;
                                case 'country':
                                    _this.countries = result.data.list;
                                    break;
                            }
                            resolve(result.code);
                        }else {
                            layer.msg(result.message || '系统繁忙')
                        }
                    }).catch( (error) => {
                        reject(error)
                    })
                }))
            },
            fetchUserInfo() {
                let _this = this;
                axios.post(URL_USERINFO).then( (response) => {
                    let result = response.data;
                    if (result.code) {
                        _this.form = result.data.info
                        if (_this.form.province == 0) _this.form.province = '';
                        if (_this.form.city == 0) _this.form.city = '';
                        if (_this.form.district == 0) _this.form.district = '';
                        if (_this.form.country == 0) _this.form.country = '';
                        if (_this.form.country == 0) _this.form.country = '';
                        if (_this.form.profession == 0) _this.form.profession = '';
                        if (~~user_id){
                            _this.form = result.data.info
                        } else {
                            if (result.data.info.province){
                                _this.fetchArea('city', result.data.info.province).then( () => {
                                    if (result.data.info.city) _this.fetchArea('district', result.data.info.city).then( ()=> {
                                        if (result.data.info.district) _this.fetchArea('country', result.data.info.district).then( () => {
                                        })
                                    })
                                })
                            }
                        }
                    }else {
                        layer.msg(result.message || '系统繁忙')
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            cancel() {
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            },
            onSubmit() {
                var _this = this;
                axios.post(ROOT.JSROOT + 'info', _this.form).then( (response) => {
                    let result = response.data;
                    if (result.code){
                        layer.msg('修改成功', {time: 1000}, () => _this.cancel());
                    }else {
                        layer.msg(result.message || '修改失败')
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
        },
        mounted() {
            let _this = this;
            _this.fetchArea()
            _this.fetchUserInfo()
        },
    })
});