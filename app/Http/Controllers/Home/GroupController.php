<?php

namespace App\Http\Controllers\Home;

use App\Enums\Constants;
use App\Models\Friend;
use App\Models\FriendGroup;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\SystemMessage;
use App\Validates\FriendGroupValidate;
use App\Validates\GroupValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends CommonController
{

	/**
	 * 群成员
	 * @param Request $request
	 * @param GroupMember $groupMember
	 * @param Group $group
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function groupMembers (Request $request, GroupMember $groupMember, Group $group)
	{
		$int_id = $request->input('id', 0);
		if ($int_id <= 0 || !($group_info = $group->query()->find($int_id))) {
			$this->error('群组不存在！');
		}
		$members = $group_info->members()->where('users.id', '<>', Auth::id())->select(['users.id', 'users.nickname as username', 'avatar', 'sign'])->get();

		$user = Auth::user();

		$data = [
			'owner'   => [
				'id'       => $user->id,
				'username' => $user->nickname,
				'sign'     => $user->sign,
				'avatar'   => $user->avatar,
			],
			'members' => count($members),
			'list'    => $members,
		];

		return response()->json(['code' => 0, 'msg' => '', 'data' => $data]);
	}

	/**
	 * 添加好友分组
	 * @param Request $request
	 * @param FriendGroup $friendGroup
	 * @param FriendGroupValidate $validate
	 * @return array
	 */
	public function addMyGroup (Request $request, FriendGroup $friendGroup, FriendGroupValidate $validate)
	{
		$data = [
			'user_id' => Auth::id(),
			'name'    => $request->input('name'),
		];
		$result = $validate->storeValidate($data);
		if ($result !== true) {
			return $this->error($result ?: '添加失败');
		}
		if ($friendGroup->fill($data)->save() !== false) {
			return $this->success();
		}
		return $this->error('添加失败');
	}

	/**
	 * 删除好友分组
	 * @param Request $request
	 * @param FriendGroup $friendGroup
	 * @return array
	 * @throws \Exception
	 */
	public function delMyGroup (Request $request, FriendGroup $friendGroup, Friend $friend)
	{
		$group_id = $request->input('group');
		if ($group_id <= 0 || !($group = $friendGroup->query()->where('user_id', Auth::id())->find($group_id))) {
			return $this->error('分组不存在！');
		}
		DB::beginTransaction();
		try {
			$default_group = $friendGroup->query()->where('user_id', Auth::id())->where('default', 1)->first();
			if (!$default_group) {
				DB::rollBack();
				return $this->error('账户异常！');
			}
			// 将分组下的所有好友都放到默认分组
			if ($group->users()->count() > 0) {
				if ($friend->query()->where('user_id', Auth::id())->where('friend_group', $group_id)->update(['friend_group' => $default_group->id]) <= 0) {
					DB::rollBack();
					return $this->error('删除失败！');
				}
			}
			if ($group->delete() === true) {
				DB::commit();
				return $this->success();
			}
			DB::rollBack();
			return $this->error('删除失败！');
		} catch (\Exception $exception) {
			DB::rollBack();
			return $this->error('删除失败！');
		}
	}

	/**
	 * 重命名好友分组
	 * @param Request $request
	 * @param FriendGroup $friendGroup
	 * @param FriendGroupValidate $validate
	 * @return array
	 */
	public function renameMyGroup (Request $request, FriendGroup $friendGroup, FriendGroupValidate $validate)
	{
		$group_id = $request->input('group');
		if ($group_id <= 0 || !($group = $friendGroup->query()->where('user_id', Auth::id())->find($group_id))) {
			return $this->error('分组不存在！');
		}
		$data = [
			'name' => $request->input('name'),
		];
		$result = $validate->updateValidate($data);
		if ($result !== true) {
			return $this->error($result ?: '修改失败');
		}
		if ($group->fill($data)->save() !== false) {
			return $this->success();
		}
		return $this->error('添加失败');
	}

	/**
	 * 移动好友
	 * @param Request $request
	 * @param Friend $friend
	 * @param FriendGroup $friendGroup
	 * @return array
	 */
	public function moveFriend (Request $request, Friend $friend, FriendGroup $friendGroup)
	{
		$group_id = $request->input('group_id');
		if ($group_id <= 0 || !($group = $friendGroup->query()->where('user_id', Auth::id())->find($group_id))) {
			return $this->error('分组不存在！');
		}
		$friend_id = $request->input('friend_id');
		if ($friend_id <= 0 || !($my_friend = $friend->query()->where('user_id', Auth::id())->where('friend_id', $friend_id)->first())) {
			return $this->error('好友不存在！');
		}
		if ($my_friend->update(['friend_group' => $group_id]) <= 0) {
			return $this->error('系统异常！');
		}
		return $this->success();
	}

	/**
	 * 创建群组
	 * @param Request $request
	 * @param Group $group
	 * @param GroupMember $groupMember
	 * @param GroupValidate $validate
	 * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 * @throws \Exception
	 */
	public function createGroup (Request $request, Group $group, GroupMember $groupMember, GroupValidate $validate)
	{
		if ($request->isMethod('post')) {
			$data = $request->only(['name', 'size', 'verify', 'type']);
			$data['user_id'] = \auth()->id();
			$result = $validate->storeValidate($data);
			if ($result !== true) {
				return $this->error($result ?: '创建失败');
			}
			DB::beginTransaction();
			try {
				if (($group->fill($data)->save() !== false)
					&&
					($groupMember->fill(['group_id' => $group->id, 'user_id' => Auth::id(), 'role' => 1])->save() !== false)) {
					$user_ids = $request->input('users', []);
					if (is_array($user_ids) && !empty($user_ids)) {
						// 系统信息 邀请成员
						$insert_data = [];
						foreach ($user_ids as $user_id) {
							$insert_data[] = [
								'from_user_id' => \auth()->id(),
								'to_user_id'   => $user_id,
								'content'      => '邀请你加入群聊',
								'type'         => Constants::CONSTANT_MESSAGE_TYPE_06,
								'group_id'     => $group->id,
								'created_at'   => Carbon::now(),
								'updated_at'   => Carbon::now(),
							];
						}
						SystemMessage::query()->insert($insert_data);
					}
					DB::commit();
					return $this->success(compact('group'));
				}

				DB::rollBack();
				return $this->error('创建失败');
			} catch (\Exception $e) {
				DB::rollBack();
				return $this->error('创建失败');
			}

		}
		return view('home.chat.group');
	}

	/**
	 * 同意群邀请
	 * @param Request $request
	 * @param Group $group
	 * @param GroupMember $groupMember
	 * @param SystemMessage $systemMessage
	 * @return array
	 */
	public function agreeGroup (Request $request, Group $group, GroupMember $groupMember, SystemMessage $systemMessage)
	{
		if (!($message = $systemMessage->query()->find($request->input('id')))) {
			return $this->error('系统消息不存在！');
		}
		if ((int)$message->type !== Constants::CONSTANT_MESSAGE_TYPE_06 || (int)$message->status !== Constants::CONSTANT_MESSAGE_STATUS_01) {
			return $this->error('消息异常！');
		}
		$group = $group->query()->find($message->group_id, ['id', 'avatar', 'name']);
		if (!$group) {
			return $this->error('群不存在！');
		}
		// 是否已加入群
		if ($groupMember->query()->where('group_id', $message->group_id)->where('user_id', Auth::id())->first()) {
			return $this->error('你已加入本群！');
		}
		// 加入群
		$data = [
			'group_id' => $message->group_id,
			'user_id'  => Auth::id(),
		];
		if ($groupMember->fill($data)->save() !== false) {
			// 更新系统消息为已同意
			$message->status = Constants::CONSTANT_MESSAGE_STATUS_02;
			$message->save();
			return $this->success(compact('group'));
		}
		return $this->error('操作失败');
	}

	/**
	 * 拒绝群邀请
	 * @param Request $request
	 * @param Group $group
	 * @param GroupMember $groupMember
	 * @param SystemMessage $systemMessage
	 * @return array
	 */
	public function refuseGroup (Request $request, Group $group, GroupMember $groupMember, SystemMessage $systemMessage)
	{
		if (!($message = $systemMessage->query()->find($request->input('id')))) {
			return $this->error('系统消息不存在！');
		}
		if ($systemMessage->type !== Constants::CONSTANT_MESSAGE_TYPE_06) {
			return $this->error('消息异常！');
		}
		$group_info = $group->query()->find($message->group_id, ['id', 'avatar', 'name']);
		if (!$group_info) {
			return $this->error('群不存在！');
		}
		// 是否已加入群
		if ($groupMember->query()->where('group_id', $message->group_id)->where('user_id', Auth::id())->first()) {
			return $this->error('你已加入本群！');
		}
		// 更新系统消息为已拒绝
		$systemMessage->status = Constants::CONSTANT_MESSAGE_STATUS_03;
		$systemMessage->save();
		return $this->success();
	}

	/**
	 * 同意群申请
	 * @param Request $request
	 * @param Group $group
	 * @param GroupMember $groupMember
	 * @param SystemMessage $systemMessage
	 * @return array
	 */
	public function agreeApplyGroup(Request $request, Group $group, GroupMember $groupMember, SystemMessage $systemMessage)
	{
		if (!($message = $systemMessage->query()->find($request->input('id')))) {
			return $this->error('系统消息不存在！');
		}
		if ((int)$message->type !== Constants::CONSTANT_MESSAGE_TYPE_03 || (int)$message->status !== Constants::CONSTANT_MESSAGE_STATUS_01) {
			return $this->error('消息异常！');
		}
		$group_info = $group->query()->find($message->group_id);
		if (!$group_info) {
			return $this->error('群不存在！');
		}
		// 是否已加入群
		if ($groupMember->query()->where('group_id', $message->group_id)->where('user_id', $message->from_user_id)->count() > 0) {
			return $this->error('对方已加入群！');
		}
		// 加入群
		$data = [
			'group_id' => $message->group_id,
			'user_id'  => $message->from_user_id,
		];
		if ($groupMember->fill($data)->save() !== false) {
			// 更新系统消息为已同意
			$message->status = Constants::CONSTANT_MESSAGE_STATUS_02;
			$message->save();
			$data = [
				'type'	=>	'addGroupToList',
				'data'	=>	[
					'type'		=> 'group',
					'avatar'	=> $group_info->avatar,
					'groupname'	=> $group_info->name,
					'id'		=> $group_info->id
				]
			];
			$this->sendSocketMessageByUid($message->from_user_id, $data);
			// 推送系统通知
			SystemMessage::query()->create([
				'from_user_id' => $group_info->user_id,
				'to_user_id'   => $message->from_user_id,
				'content'      => '已同意你加入群',
				'type'         => Constants::CONSTANT_MESSAGE_TYPE_04,
				'group_id'     => $group_info->id,
			]);
			$this->updateMsgBox($message->from_user_id);
			return $this->success();
		}
		return $this->error('操作失败');
	}

	/**
	 * 退出群组
	 * @param Request $request
	 * @param Group $group
	 * @param GroupMember $groupMember
	 * @return array
	 */
	public function quitGroup (Request $request, Group $group, GroupMember $groupMember)
	{
		$group_id = $request->input('group');
		if ($group_id <=0 || !($group_info = $group->query()->find($group_id))) {
			return $this->error('群组不存在');
		}
		if ($group_info->members()->where('user_id', \auth()->id())->doesntExist()) {
			return $this->error('你不是群成员！');
		}
		if ((int)$group_info->user_id === \auth()->id()){
			return $this->error('群主不能退出群！');
		}
		if ($groupMember->query()->where(['user_id' => \auth()->id(), 'group_id' => $group_id])->delete()) {
			// 给群主推送系统消息 群员退群
			$system_message = [
				'from_user_id' => \auth()->id(),
				'to_user_id'   => $group_info->user_id,
				'content'      => '退出群',
				'type'         => Constants::CONSTANT_MESSAGE_TYPE_08,
				'group_id'     => $group_id,
			];
			SystemMessage::query()->create($system_message);
			$this->updateMsgBox($group_info->user_id);
			return $this->success();
		}
		return $this->error('操作失败!');
	}
}
