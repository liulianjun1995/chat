<?php

namespace App\Http\Controllers\Home;

use App\Enums\Constants;
use App\Models\Friend;
use App\Models\FriendGroup;
use App\Models\SystemMessage;
use App\Models\User;
use App\Validates\FriendValidate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends CommonController
{

	/**
	 * 好友列表
	 * @param Request $request
	 * @param Friend $friend
	 * @return array
	 */
    public function friends(Request $request, Friend $friend)
    {
        $list = Auth::user()->friends()->select(['users.id as key', 'nickname as label'])->get();

        return $this->success(compact('list'));
    }

    /**
     * 添加好友
     * @param Request $request
     * @param FriendValidate $validate
     * @param Friend $friend
     * @param SystemMessage $message
     * @return array
     * @throws \Exception
     */
    public function addFriend(Request $request, FriendValidate $validate, Friend $friend, SystemMessage $message)
    {
        if (!($message = $message->query()->find($request->input('id')))){
            return $this->error('系统消息不存在！');
        }

        $data_my = $request->only(['friend_group']);
        $data_my['friend_id'] = $message->from_user_id;

        if ($friend->query()->where('user_id', Auth::id())->where('friend_id', $data_my['friend_id'])->count() > 0){
            return $this->error('对方已经是你的好友，不可重复添加');
        }

        $data_my['user_id'] = Auth::id();

        $result_my      = $validate->storeValidate($data_my);

        $data_friend = [
            'user_id'   => $message['from_user_id'],
            'friend_id' => Auth::id(),
            'friend_group'  => $message->group_id
        ];

        // 好友分组是否存在
        if (!FriendGroup::query()->find($data_friend['friend_group'])){
            $default_group = FriendGroup::query()->where('user_id', $data_friend['friend_id'])->where('default', 1)->first();
            if ($default_group){
                $data_friend['friend_group'] = $default_group->id;
            }else{
                return $this->error('对方账户异常，请稍后再试！');
            }
        }

        $result_friend  = $validate->storeValidate($data_friend);

        if ($result_my !== true || $result_friend !== true){
            if (is_string($result_my)) {
                return $this->error($result_my);
            }
            return $this->error(is_string($result_friend) ? $result_friend : '添加失败');
        }

        // 更新系统消息为已同意
        $message->update(['status' => Constants::CONSTANT_MESSAGE_STATUS_02]);

        // 添加已同意申请消息
        SystemMessage::query()->create([
            'type'          => Constants::CONSTANT_MESSAGE_TYPE_02,
            'from_user_id'  => $message->to_user_id,
            'to_user_id'    => $message->from_user_id,
            'status'        => Constants::CONSTANT_MESSAGE_STATUS_02,
            'content'       => '已经同意你的好友申请',
        ]);

        DB::beginTransaction();
        if ((new Friend)->fill($data_my)->save() !== false && (new Friend)->fill($data_friend)->save()){
            DB::commit();
            return $this->success();
        }
        DB::rollBack();
        return $this->error('添加失败！');
    }

	/**
	 * 拒绝好友申请
	 * @param Request $request
	 * @param SystemMessage $systemMessage
	 * @return array
	 * @throws \Exception
	 */
    public function refuseFriend(Request $request, SystemMessage $systemMessage)
    {
        if (!($message = $systemMessage->query()->find($request->input('id')))){
            return $this->error('系统消息不存在！');
        }
        DB::beginTransaction();
        try {
            // 更新系统消息为已拒绝
            $message->update(['status' => Constants::CONSTANT_MESSAGE_STATUS_03]);

            // 添加已拒绝申请消息
            SystemMessage::query()->create([
                'type'          => Constants::CONSTANT_MESSAGE_TYPE_02,
                'from_user_id'  => $message->to_user_id,
                'to_user_id'    => $message->from_user_id,
                'status'        => Constants::CONSTANT_MESSAGE_STATUS_03,
                'content'       => '已经拒绝你的好友申请',
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->error('请求失败,请重试！');
        }
        DB::commit();
        return $this->success();
    }

	/**
	 * 删除好友
	 * @param Request $request
	 * @param Friend $friend
	 * @return array
	 */
	public function deleteFriend (Request $request, Friend $friend)
	{
		$friend_id = $request->input('friend');
		if ($friend_id <= 0 || !($my_friend = $friend->query()->where(['user_id' => Auth::id(), 'friend_id' => $friend_id])->first())){
			return $this->error('好友不存在！');
		}
		if ($friend->query()
			->where(function (Builder $query)use ($friend_id) {
				$query->where(['user_id' => Auth::id(), 'friend_id' => $friend_id]);
			})
			->orWhere(function (Builder $query)use ($friend_id) {
				$query->where(['user_id' => $friend_id, 'friend_id' => Auth::id()]);
			})->delete()) {


			$data = [
				'type'	=> 'removeFriend',
				'data'	=> [
					'id'	=> Auth::id(),
					'type'	=> 'friend'
				]
			];
			$this->sendSocketMessageByUid($friend_id, $data);
			return $this->success();
		}
		return $this->error('删除失败!');
    }

	/**
	 * 修改好友备注
	 * @param Request $request
	 * @param Friend $friend
	 * @param User $user
	 * @param FriendValidate $validate
	 * @return array
	 */
	public function remarkFriend (Request $request, Friend $friend, User $user, FriendValidate $validate)
	{
		$friend_id = $request->input('friend');
		$remark = $request->input('remark');
		$result = $validate->updateValidate(['remark' => $remark]);
		if ($result !== true) {
			return $this->error($result ?: '修改失败');
		}
		if ($friend_id <= 0 || !($my_friend = $friend->query()->where(['user_id' => Auth::id(), 'friend_id' => $friend_id])->first())){
			return $this->error('好友不存在！');
		}
		$my_friend->remark = $remark;
		if ($my_friend->save() !== false) {
			return $this->success();
		}
		return $this->error('修改失败！');
    }
}
