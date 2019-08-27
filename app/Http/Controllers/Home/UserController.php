<?php

namespace App\Http\Controllers\Home;

use App\Models\User;
use App\Validates\UserValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class UserController extends CommonController
{
    /**
     * 个人资料
     * @param Request $request
     * @param User $user
     * @param $id
     * @return array
     */
    public function userInfo(Request $request, User $user, $id)
    {
        if ($id == 0) $id = Auth::id();
        $field = ['id', 'nickname', 'sex', 'birthday', 'avatar', 'province', 'city', 'district', 'country', 'sign', 'profession'];
        if ($request->isMethod('post')){
            if ($id <= 0 || !($info = $user->query()->find($id, $field))){
                return $this->error('用户不存在!');
            }
            $info->append('sexFormat');
            $info->append('professionFormat');
            $info->append('address');
            return $this->success(compact('info'));
        }
        return view('home.chat.info');
    }

    /**
     * 修改资料
     * @param Request $request
     * @param UserValidate $validate
     * @return array
     */
    public function info(Request $request, UserValidate $validate)
    {
        $data = $request->only(['nickname', 'sex', 'birthday', 'avatar', 'province', 'city', 'district', 'country', 'sign', 'profession']);

        $user = Auth::user();

        $result = $validate->updateValidate($user->id, $data);

        if ($result !== true){
            return $this->error($result ?: '修改失败');
        }
        if ($user->fill($data)->save() !== false){
            return $this->success();
        }
        return $this->error('修改失败');
    }

	/**
	 * 修改签名
	 * @param Request $request
	 * @param UserValidate $validate
	 * @return array
	 */
	public function sign (Request $request, UserValidate $validate)
	{
		$sign = $request->input('sign');
		$result = $validate->filedValidate(['sing' => $sign]);
		if ($result !== true) {
			return $this->error($result ?: '修改失败');
		}
		$user = Auth::user();
		$user->sign = $sign;
		if ($user->save() !== false) {
			return $this->success();
		}
		return $this->error('修改失败');
    }
}
