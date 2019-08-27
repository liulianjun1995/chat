<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/5/20
 * Time: 15:09
 */
namespace App\Validates;

use Illuminate\Support\Facades\Validator;

class FriendGroupValidate extends Validate
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
            'name'          =>  ['required', 'min:2', 'max:20'],
        ];
        // 验证数据
        $validate = $this->validate($request, $rules);
        if ($validate === true) {
            return true;
        }
        return $validate;
    }

    /**
     * 更新场景验证器
     * @param array $request
     * @return bool|string
     */
    public function updateValidate($request = [])
    {
        $rules = [
            'name'  =>  ['required', 'min:2', 'max:20'],
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
            'name.required'         => '请输入分组名称！',
            'name.min'              => '分组名称至少2个字符！',
            'name.max'              => '分组名称至多20个字符！',
        ];
        // 内置验证器
        $validator = Validator::make($request, $rules, $message);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        return true;
    }
}