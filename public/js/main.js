require.config({
    baseUrl: ROOT.JSROOT + 'js/',
    paths: {
        jquery      :   'lib/plugs/jquery-3.3.0',
        vue         :   'lib/plugs/vue.min',
        ELEMENT     :   'lib/plugs/element/element',
        axios       :   'lib/plugs/axios.min',
        layui       :   'lib/plugs/layim/dist/layui',
        layer       :   'lib/plugs/layer/layer',
        Validator   :   'lib/plugs/validator/lib/validator',
    },
    map: {
        "*": {
            "css"   :   "css"
        }
    },
    shim:{
        'layer'     :   {deps: ['css!lib/plugs/layer/theme/default/layer.css', 'jquery']},
        'layui'     :   {deps: ['css!lib/plugs/layim/dist/css/layui.css']},
    }
})

