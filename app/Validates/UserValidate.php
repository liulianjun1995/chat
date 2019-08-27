<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/5/20
 * Time: 15:09
 */
namespace App\Validates;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserValidate extends Validate
{
    /**
     * 新增场景验证器
     * @param array $request
     * @return bool|string
     */
    public function storeValidate($request = [])
    {
        // 完整验证规则
        $rules = [

        ];
        // 验证数据
        $validate = $this->validate($request, $rules);
        if ($validate === true) {
            return true;
        }
        return $validate;
    }

    public function updateValidate($id = 0, $request = [])
    {
    	if ($id <= 0) return false;
        // 完整验证规则
        $rules = [
            'nickname'  => ['required', 'min:3', 'max:24'],
            'email'     => ['email', 'unique:users', 'nullable'],
            'phone'     => [function($attribute, $value, $fail) {
                if(!isMobile($value)){
                    return $fail("请输入正确的手机号码");
                }
            }],
            'sex'       => ['required', Rule::in(array_keys(config('system.sex')))],
            'sign'      => ['max:50', 'nullable'],
            'birthday'  => ['date', 'nullable'],
            'province'  => ['exists:areas,id', 'nullable'],
            'city'      => ['exists:areas,id', 'nullable'],
            'district'  => ['exists:areas,id', 'nullable'],
            'country'   => ['exists:areas,id', 'nullable'],
            'profession'=> [Rule::in(array_keys(config('system.profession'))), 'nullable']
        ];
        // 验证数据
        $validate = $this->validate($request, $rules);
        if ($validate === true) {
            return true;
        }
        return $validate;
    }

	public function filedValidate ($field)
	{
		$rules = [
			'sign'      => ['max:50', 'nullable'],
		];
		// 验证数据
		$validate = $this->validate($field, $rules);
		if ($validate === true) {
			return true;
		}
		return $validate;
    }

    /**
     * 验证
     * @param array $request
     * @param array $rules
     * @return bool|string
     */
    protected function validate($request = [], $rules = [])
    {
        $message = [
            'nickname.required'     => '请填写昵称！',
            'nickname.min'          => '昵称最少3个字符！',
            'nickname.max'          => '昵称最多24个字符！',
            'email.email'           => '请输入正确的邮箱地址！',
            'email.unique'          => '邮箱已存在！',
            'sex.required'          => '请选择性别！',
            'sex.in'                => '性别参数错误！',
            'sign.max'              => '签名最多50个字符！',
            'birthday.date'         => '请填写正确的日期！',
            'province.exists'       => '省份不存在！',
            'city.exists'           => '城市不存在！',
            'district.exists'       => '县区不存在！',
            'country.exists'        => '乡村不存在！',
            'profession.in'         => '职业参数错误！'
        ];
        // 内置验证器
        $validator = Validator::make($request, $rules, $message);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        return true;
    }
}