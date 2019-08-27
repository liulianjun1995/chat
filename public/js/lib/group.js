define(["jquery", 'vue', 'layer', 'axios', 'ELEMENT', 'layui'],function($ , Vue, layer, axios, ELEMENT, undefined) {

    Vue.use(ELEMENT);

    new Vue({
        el: '#group',
        data () {
            return {
                form: {
                    type: 0,
                    name: '',
                    size: 100,
                    verify: 2,
                    users: []
                },
                users: [],
                rules: {
                    name: [
                        { required: true, message: '请输入群名称', trigger: 'blur' },
                        { min: 3, max: 20, message: '长度在 3 到 20 个字符', trigger: 'blur' }
                    ],
                    size: [
                        { required: true, message: '请选择群规模', trigger: 'blur' },
                    ],
                    verify: [
                        { required: true, message: '请选择加群验证', trigger: 'blur' },
                    ],
                },
                step: 1,
                disabledSubmit: false,
            }
        },
        methods: {
            fetchUsers() {
                var _this = this;
                axios.get(ROOT.JSROOT + 'friends').then( (response) => {
                    let result = response.data
                    if (result.code){
                        _this.users = result.data.list
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            groupType(type) {
                this.form.type = type;
                this.step++;
            },
            prevStep() {
                this.step--;
            },
            nextStep() {
                var _this = this
                if (_this.step === 2){
                    _this.$refs['groupForm'].validate( (valid) => {
                        if (valid){
                            _this.step++;
                        }
                    })
                }
            },
            onSubmit() {
                var _this = this
                _this.disabledSubmit = true;
                axios.post(ROOT.JSROOT + 'createGroup', _this.form).then( (response) => {
                    let result = response.data;
                    if (result.code){
                        // 增加一个群组到面板
                        let group = result.data.group;
                        console.log(result);
                        parent.parent.layui.layim.addList({
                            type: 'group',
                            avatar: group.avatar,
                            groupname: group.name,
                            id: group.id,
                            members: 0
                        })
                        let data = {
                            type: "createGroup",
                            group: group.id
                        }
                        parent.parent.ROOT.VM.sendMessage(JSON.stringify(data))
                        parent.layer.close(parent.layer.getFrameIndex(window.name));
                    } else {
                        _this.disabledSubmit = false;
                        layer.msg(result.message || '系统繁忙')
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            }
        },
        mounted() {
            var _this = this;
            _this.fetchUsers()
        }
    })
});