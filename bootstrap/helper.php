<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/4/12
 * Time: 15:53
 */
if (!function_exists('imgPath')) {

    /**
     * 处理图片地址
     * @param string $path
     * @param string $default
     * @return string
     */
    function imgPath($path = '', $default = '')
    {
        empty($default) && $default = 'img/default.png';
        empty($path) && $path = $default;

        if (check_url($path)){
            return $path;
        }

        $path = Storage::disk('public')->exists($path) ? $path : $default;
        return asset('storage/' . $path);
    }
}

if (!function_exists('check_url')) {
    function check_url($url){
        if(!preg_match("/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/", $url)){
            return false;
        }
        return true;
    }
}

if (!function_exists('isMobile')) {
    /**
     * 验证手机号码
     * @param null $phone
     * @return bool
     */
    function isMobile( $phone = null)
    {
        $test = "/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}\$/";
        return !empty($phone) && preg_match($test,$phone);
    }
}


