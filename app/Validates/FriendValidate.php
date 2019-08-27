<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/5/20
 * Time: 15:09
 */
namespace App\Validates;

use Illuminate\Support\Facades\Validator;

class FriendValidate extends Validate
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
            'user_id'       =>  ['required', 'exists:users,id'],
            'friend_id'     =>  ['required', 'exists:users,id', 'different:user_id'],
//            'friend_group'  =>  ['required', 'exists:friend_groups,id']
        ];
        // 验证数据
        $validate = $this->validate($request, $rules);
        if ($validate === true) {
            return true;
        }
        return $validate;
    }

	public function updateValidate ($request = [])
	{
		// 完整验证规则
		$rules = [
			'remark'	=>	'min:2|max:10'
		];
		// 验证数据
		$validate = $this->validate($request, $rules);
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
            'user_id.required'      => '请先登录！',
            'user_id.exists'        => '登录用户不存在！',
            'friend_id.required'    => '请选择用户！',
            'friend_id.exists'      => '用户不存在',
			'remark.min'			=> '备注至少2个字符',
			'remark.max'			=> '备注最多10和字符'
        ];
        // 内置验证器
        $validator = Validator::make($request, $rules, $message);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        return true;
    }
}