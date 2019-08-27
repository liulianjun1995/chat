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

class GroupValidate extends Validate
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
            'user_id'   => ['required', 'exists:users,id'],
            'type'      => ['required', Rule::in(array_keys(config('system.group_type')))],
            'name'      => ['required', 'min:3', 'max:20'],
            'size'      => ['required', Rule::in(array_keys(config('system.group_size')))],
            'verify'    => ['required', Rule::in(array_keys(config('system.group_verify')))]
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
     * @param int $id
     * @param array $request
     * @return bool|string
     */
    public function updateValidate($id = 0, $request = [])
    {
        if ($id <= 0) return false;
        // 完整验证规则
        $rules = [
            'name'      => ['required', 'min:3', 'max:20'],
            'size'      => ['required', Rule::in(array_keys(config('system.group_size')))],
            'verify'    => ['required', Rule::in(array_keys(config('system.group_verify')))]
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
            'user_id.required'  => '用户异常',
            'user_id.exists'    => '用户不存在',
            'name.required'     => '请输入群名称',
            'type.required'     => '请选择群分类',
            'type.in'           => '群分类异常',
            'name.min'          => '群名称至少3个字符',
            'name.max'          => '群名称至多20个字符',
            'size.required'     => '请选择群规模',
            'size.in'           => '群规模异常',
            'verify.required'   => '请选择群验证规则',
            'verify.in'         => '群验证规则异常',
        ];
        // 内置验证器
        $validator = Validator::make($request, $rules, $message);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        return true;
    }
}