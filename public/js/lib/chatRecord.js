define(["jquery", 'vue', 'layer', 'axios', 'ELEMENT', 'layui'],function($ , Vue, layer, axios, ELEMENT, undefined) {

    Vue.use(ELEMENT);

    new Vue({
        el: '#chat_record',
        data: function() {
            return {
                list: [],
                page: 1,
                pagination: {
                    current_page: 1,    // 当前页
                    last_page: 1,       // 最后一页
                    total: 1,
                    per_page: 1
                }
            }
        },
        methods: {
            fetchData() {
                let _this = this;
                axios.post('', {page: _this.page}).then( (response) => {
                    let result = response.data;
                    if (result.code) {
                        result.data.list.data.forEach( (i) => {
                            i.content = parent.layui.layim.content(i.content);
                            _this.list.push(i);
                        });
                        _this.pagination = {
                            current_page: result.data.list.current_page,
                            last_page: result.data.list.last_page,
                            total: result.data.list.total,
                            per_page: result.data.list.per_page,
                        }
                    }
                }).catch( (error) => {
                    console.log(error);
                })
            },
            load () {
                let _this = this;
                if (_this.pagination.current_page < _this.pagination.last_page) {
                    _this.page++;
                    _this.fetchData();
                }
            }
        },
        mounted() {
            this.fetchData();
        }
    })
});