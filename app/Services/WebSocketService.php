<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/4/10
 * Time: 13:04
 */

namespace App\Services;

use App\Enums\Constants;
use App\Models\ChatRecord;
use App\Models\Friend;
use App\Models\Group;
use App\Models\SystemMessage;
use App\Models\User;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebSocketService implements WebSocketHandlerInterface
{
	/**
	 * @var User|null
	 */
	protected $user = null;

	public function __construct()
	{
	}

	public function onOpen(Server $server, Request $request)
	{
		if (!isset($request->get['sessionid'])) {
			$data = [
				"type" => "token expire",
			];
			$server->push($request->fd, json_encode($data));
			return;
		}
		$sessionid = $request->get['sessionid'];
		session()->setId($sessionid);
		session()->start();
		$this->user = is_object(session()->get('user')) ? session()->get('user') : null;
		if (!$this->user) {
			$data = [
				"type" => "token expire",
			];
			$server->push($request->fd, json_encode($data));
			return;
		}
		//绑定fd变更状态
		app('swoole')->wsTable->set('uid:' . $this->user->id, ["value" => $request->fd]);// 绑定uid到fd的映射
		app('swoole')->wsTable->set('fd:' . $request->fd, ["value" => $this->user->id]);// 绑定fd到uid的映射
		// 标记在线
		User::query()->where('id', $this->user->id)->update(['online' => 'online']);
		// 获取未读消息数量
		$count = SystemMessage::query()->where('to_user_id', $this->user->id)->where('read', 0)->count();
		$data = [
			"type"  => "msgBox",
			"count" => $count,
		];
		$server->push($request->fd, json_encode($data));
	}

	public function onMessage(Server $server, Frame $frame)
	{
		$info = json_decode($frame->data);

		if ($this->user === null) {
			$data = [
				"type" => "token_expire",
			];
			$server->push($frame->fd, json_encode($data));
			return;
		}

		$type = $info->type ?? '';

//		var_dump($this->user->id);

		switch ($type) {
			// 心跳连接
			case 'ping':
				break;
			// 聊天信息
			case 'chat':
				switch ($info->to) {
					case 'friend':
						$record = [
							'friend_id' => $info->friend_id,
							'content'   => $info->content,
						];
						$this->user->chatRecords()->save(new ChatRecord($record));
						$data = [
							'type' => 'getMessage',
							'data' => [
								'username'  => $this->user->nickname,
								'avatar'    => $this->user->avatar,
								'id'        => $this->user->id,
								'type'      => 'friend',
								'content'   => $info->content,
								'timestamp' => Carbon::now(),
							],
						];
						$this->sendByUid($server, $info->friend_id, $data);
						break;
					case 'group':
						$record = [
							'user_id'  => $this->user->id,
							'content'  => $info->content,
						];
						$group = Group::query()->find($info->group_id);
						$group->chatRecords()->save(new ChatRecord($record));
						$members = $group->members;
						foreach ($members as $member){
							if ($member->id != $this->user->id){
								$data = [
									'type'	=> 'getMessage',
									'data' => [
										'username'  => $this->user->nickname,
										'avatar'    => $this->user->avatar,
										'fromid'	=> $this->user->id,
										'id'        => $info->group_id,
										'type'      => 'group',
										'content'   => $info->content,
										'timestamp' => time()*1000,
									],
								];
								$this->sendByUid($server, $member->id, $data);
							}
						}
						break;
				}
				break;
			// 发送好友申请
			case 'addFriend':
				$system_message = [
					'from_user_id' => $this->user->id,
					'to_user_id'   => $info->to_user_id,
					'remark'       => $info->remark,
					'content'      => '申请添加你为好友',
					'type'         => Constants::CONSTANT_MESSAGE_TYPE_01,
					'group_id'     => (int)$info->to_friend_group_id,
				];
				if ($system_message['from_user_id'] === $system_message['to_user_id']) {
					$data = [
						'type' => 'layer',
						'code' => 500,
						'msg'  => '不能添加自己为好友',
					];
					$this->sendByUid($server, $system_message['from_user_id'], $data);
					return;
				}
				// 已是好友
				if (Friend::query()->where('user_id', $this->user->id)->where('friend_id', $system_message['to_user_id'])->first()) {
					$data = [
						'type' => 'layer',
						'code' => 500,
						'msg'  => '对方已经是你的好友，不可重复添加',
					];
					$this->sendByUid($server, $system_message['from_user_id'], $data);
					return;
				}
				// 是否有未处理的相同的系统消息
				if ($message = SystemMessage::query()
					->where('from_user_id', $this->user->id)
					->where('to_user_id', $info->to_user_id)
					->where('type', Constants::CONSTANT_MESSAGE_TYPE_01)
					->where('status', Constants::CONSTANT_MESSAGE_STATUS_01)
					->first()) {
					$message->read = 0;
					$message->created_at = Carbon::now();
					$message->updated_at = Carbon::now();
					$message->remark = $info->remark;
					$message->save();
				} else {
					// 生成系统消息
					SystemMessage::query()->create($system_message);
				}
				// 更新接受者未读消息数量
				$count = SystemMessage::query()->where('to_user_id', $system_message['to_user_id'])->where('read', 0)->count();
				$data = [
					'type'  => 'msgBox',
					'count' => $count,
				];
				$this->sendByUid($server, $system_message['to_user_id'], $data);
				break;
			// 添加好友到好友列表
			case 'addFriendToList':
				$message = SystemMessage::query()->find($info->id);
				if ($message) {
					$data = [
						'type' => 'addFriendToList',
						'data' => [
							'type'     => 'friend',
							'avatar'   => $this->user->avatar,
							'username' => $this->user->nickname,
							'groupid'  => $message->group_id,
							'id'       => $this->user->id,
							'sign'     => $this->user->sign,
						],
					];
					$count = SystemMessage::query()->where('to_user_id', $message->from_user_id)->where('read', 0)->count();
					$data_msg = [
						'type'  => 'msgBox',
						'count' => $count,
					];
					// 消息盒子
					$this->sendByUid($server, $message->from_user_id, $data_msg);
					// 消息
					$this->sendByUid($server, $message->from_user_id, $data);
				}
				break;
			// 拒绝好友申请
			case 'refuseFriend':
				$message = SystemMessage::query()->find($info->id);
				if ($message) {
					$count = SystemMessage::query()->where('to_user_id', $message->from_user_id)->where('read', 0)->count();
					$data = [
						'type'  => 'msgBox',
						'count' => $count,
					];
					// 消息盒子
					$this->sendByUid($server, $message->from_user_id, $data);
				}
				break;
			// 创建群聊
			case 'createGroup':
				// 推送系统消息给邀请的好友
				$user_ids = SystemMessage::query()
					->where('type', Constants::CONSTANT_MESSAGE_TYPE_06)
					->where('group_id', $info->group)
					->pluck('to_user_id');
				if ($user_ids !== null) {
					foreach ($user_ids as $user_id) {
						$data = [
							'type'  => 'msgBox',
							'count' => SystemMessage::query()->where('to_user_id', $user_id)->where('read', 0)->count(),
						];
						$this->sendByUid($server, $user_id, $data);
					}
				}
				break;
			// 申请加群
			case 'applyGroup':
				$group = Group::query()->find($info->to_group_id);
				if (!$group) {
					$data = [
						'type' => 'layer',
						'code' => 500,
						'msg'  => '群组不存在',
					];
					$this->sendByUid($server, $this->user->id, $data);
					return;
				}
				if ($group->user_id == $this->user->id) {
					$data = [
						'type' => 'layer',
						'code' => 500,
						'msg'  => '不能申请加入自己的群',
					];
					$this->sendByUid($server, $this->user->id, $data);
					return;
				}
				if ($group->members()->where('user_id', $this->user->id)->exists()){
					$data = [
						'type' => 'layer',
						'code' => 500,
						'msg'  => '你已在群中，请勿重复申请！',
					];
					$this->sendByUid($server, $this->user->id, $data);
					return;
				}
				$system_message = [
					'from_user_id'	=> $this->user->id,
					'to_user_id'	=> $group->user_id,
					'remark'		=> $info->remark,
					'content'		=> '申请加入群',
					'type'			=> Constants::CONSTANT_MESSAGE_TYPE_03,
					'group_id'		=> $group->id
				];
				// 是否有未处理的相同的系统消息
				if ($message = SystemMessage::query()
					->where('from_user_id', $this->user->id)
					->where('to_user_id', $group->user_id)
					->where('type', Constants::CONSTANT_MESSAGE_TYPE_03)
					->where('status', Constants::CONSTANT_MESSAGE_STATUS_01)
					->first()) {
					$message->read = 0;
					$message->created_at = Carbon::now();
					$message->updated_at = Carbon::now();
					$message->remark = $info->remark;
					$message->save();
				} else {
					// 生成系统消息
					SystemMessage::query()->create($system_message);
				}
				// 更新接受者未读消息数量
				$count = SystemMessage::query()->where('to_user_id', $system_message['to_user_id'])->where('read', 0)->count();
				$data = [
					'type'  => 'msgBox',
					'count' => $count,
				];
				$this->sendByUid($server, $system_message['to_user_id'], $data);
				break;
			// 新人加入群聊通知
			default:
				break;
		}
	}

	public function onClose(Server $server, $fd, $reactorId)
	{
		$uid = app('swoole')->wsTable->get('fd:' . $fd);
		if ($uid !== false) {
			app('swoole')->wsTable->del('uid:' . $uid['value']);// 解绑uid映射
		}
		app('swoole')->wsTable->del('fd:' . $fd);// 解绑fd映射
		// 标记离线
		User::query()->where('id', $uid)->update(['online' => 'offline']);
	}

	/**
	 * 根据id推送消息
	 * @param Server $server
	 * @param $uid
	 * @param $data
	 * @param bool $offline_msg 离线消息
	 * @return bool
	 */
	protected function sendByUid($server, $uid, $data, $offline_msg = false)
	{
		$fd = app('swoole')->wsTable->get('uid:' . $uid); //获取接受者fd

		if ($fd == false) {
			//这里说明该用户已下线，日后做离线消息用
			if ($offline_msg) {
				$data = [
					'user_id' => $uid,
					'data'    => json_encode($data),
				];
				//插入离线消息
			}
			return false;
		}
		return $server->push($fd['value'], json_encode($data));//发送消息
	}


}
