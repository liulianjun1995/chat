<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/4/17
 * Time: 16:00
 */

namespace App\Http\Controllers\Home;

use App\Enums\Constants;
use App\Models\Area;
use App\Models\ChatRecord;
use App\Models\Friend;
use App\Models\FriendGroup;
use App\Models\Group;
use App\Models\SystemMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends CommonController
{
	public function index()
	{
		return view('home.chat.index');
	}

	/**
	 * 获取列表
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getList()
	{
		$user = Auth::user();

		// 好友分组
		$friend_groups = FriendGroup::query()->where('user_id', Auth::id())->get(['id', 'name as groupname']);

		$friend_groups->each(function ($item, $key) {
			$list = Friend::query()->from('friends as f')
				->leftJoin('users as u', 'f.friend_id', '=', 'u.id')
				->where('f.friend_group', $item->id)
				->get(['u.nickname as username', 'u.id', 'u.avatar', 'u.sign', 'u.online as status', 'f.remark']);
			$item->list = $list;
			$item->online = $list->where('status', 'online')->count();
		});

		// 群组
		$groups = $user->groups()->get(['groups.id', 'groups.name as groupname', 'groups.avatar']);

		$data = [
			'mine'   => [
				'id'       => $user->id,
				'username' => $user->nickname,
				'status'   => 'online',
				'sign'     => $user->sign,
				'avatar'   => $user->avatar,
			],
			'friend' => $friend_groups,
			'group'  => $groups,
		];
		return response()->json(['code' => 0, 'msg' => '', 'data' => $data]);
	}

	/**
	 * 查找
	 * @param Request $request
	 * @param User $user
	 * @param Group $group
	 * @return array|Factory|\Illuminate\View\View
	 */
	public function find(Request $request, User $user, Group $group)
	{
		$type = $request->input('type', 'user');

		if ($request->isMethod('post')) {
			$keyword = trim($request->input('keyword', ''));
			if ($type === 'user') {
				$builder = $user->query()
					->from('users as u');
				if ($keyword) {
					$builder->where(function (Builder $query) use ($keyword) {
						$query->where('u.nickname', 'like', '%' . $keyword . '%')
							->orWhere('u.id', $keyword);
					});
				}
				$friends = Friend::query()->where('user_id', Auth::id())->pluck('friend_id');
				$builder->select(['u.nickname', 'u.avatar', 'u.id', 'u.sex'])
					->whereNotIn('u.id', $friends)
					->where('u.id', '<>', Auth::id());
//                \Log::info($builder->toSql());
			} else {
				$builder = $group->query();
				if ($keyword) {
					$builder->where(function (Builder $query) use ($keyword) {
						$query->where('name', 'like', '%' . $keyword . '%')
							->orWhere('id', $keyword);
					});
				}
				$builder->select(['id', 'name', 'avatar', 'size'])->withCount('members');

				// 我加入的群
			}
			$list = $builder
				->paginate(15);

			return $this->success(compact('list'));
		}

		return view('home.chat.find', compact('type'));
	}


	/**
	 * 消息记录
	 * @param Request $request
	 * @return array|Factory|\Illuminate\View\View
	 */
	public function message(Request $request)
	{
		if ($request->isMethod('post')) {
			SystemMessage::query()->where('to_user_id', Auth::id())->where('read', 0)->update(['read_at' => Carbon::now(), 'read' => 1]);
			$field = ['m.id', 'u.nickname', 'u.avatar', 'm.type', 'm.created_at', 'm.remark', 'm.content', 'm.from_user_id', 'm.group_id', 'm.type', 'm.status', 'u.sign'];
			$list = SystemMessage::query()
				->from('system_messages as m')
				->leftJoin('users as u', 'm.from_user_id', '=', 'u.id')
				->select($field)
				->where('to_user_id', Auth::id())
				->orderBy('created_at', 'desc')
				->paginate(10);
			$list->each(function ($item, $key) {
				if (in_array($item->type, [Constants::CONSTANT_MESSAGE_TYPE_03, Constants::CONSTANT_MESSAGE_TYPE_04, Constants::CONSTANT_MESSAGE_TYPE_06, Constants::CONSTANT_MESSAGE_TYPE_07, Constants::CONSTANT_MESSAGE_TYPE_08], true)) {
					$group = Group::query()->find($item->group_id);
					$item->group_name = $group ? $group->name : '';
					$item->group_avatar = $group ? $group->avatar : '';
				}
				$item->time = $item->created_at->diffForHumans();
			});
			return $this->success(compact('list'));
		}
		return view('home.chat.msgbox');
	}

	/**
	 * 聊天记录
	 * @param Request $request
	 * @return array|Factory|\Illuminate\View\View
	 */
	public function chatRecord(Request $request, ChatRecord $chatRecord)
	{
		if ($request->isMethod('post')) {
			$type = $request->input('type');
			$id = $request->input('id');
			$builder = $chatRecord->query();
			if ('friend' === $type){
				$builder->where(function (Builder $query)use ($id) {
					$query->where('user_id', Auth::id())->where('friend_id', $id);
				})->orWhere(function (Builder $query)use ($id) {
					$query->where('user_id', $id)->where('friend_id', Auth::id());
				});
			}else{
				$builder->where(function (Builder $query)use ($id) {
					$query->orWhere('group_id', $id)
						->orWhere('friend_id', Auth::id());
				});
			}

			$list = $builder->paginate(10);

			$list->each(function ($item, $key) {
				$item->mine = (int)$item->user_id === Auth::id();
				if (!$item->mine) {
					$item->load('friend:id,nickname,avatar');
				}
			});

			return $this->success(compact('list'));
		}
		return view('home.chat.record');
	}


	/**
	 * 城市列表
	 * @param Request $request
	 * @param Area $area
	 * @return array
	 */
	public function area(Request $request, Area $area)
	{
		$type = $request->input('type', 'province');

		$field = ['id as value', 'areaname as label'];

		$pid = $request->input('pid', 1);

		switch ($type) {
			case 'province':
				$builder = $area->query()->where('level', 1);
				break;
			case 'city':

				if ($pid <= 0 || !$area->query()->where('level', 1)->find($pid)) {
					return $this->error('省份不存在不存在！');
				}
				$builder = $area->query()->where('level', 2);
				break;
			case 'district':
				if ($pid <= 0 || !$area->query()->where('level', 2)->find($pid)) {
					return $this->error('城市不存在！');
				}
				$builder = $area->query()->where('level', 3);
				break;
			case 'country':
				if ($pid <= 0 || !$area->query()->where('level', 3)->find($pid)) {
					return $this->error('城市不存在！');
				}
				$builder = $area->query()->where('level', 4);
				break;
			default :
				return $this->error('地区类别不存在！');
				break;
		}

		$list = $builder->where('parentid', $pid)->orderBy('sort')->get($field);;

		return $this->success(compact('list'));
	}

	/**
	 * 发送图片
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function uploadImage (Request $request)
	{
		$file = $this->uploadOneFile('file', 'chat/', 1024*1024*5, ['jpeg', 'png', 'gif']);
		$data = [
			'code'	=> 0,
			'msg'	=> '',
			'data'	=> [
				'src'	=> ''
			]
		];
		if (!is_array($file)) {
			$data['code'] = 1;
			$data['msg'] = $file;
		}else{
			$data['data']['src'] = asset('storage/' . $file['path']);
		}
		return response()->json($data);
	}

	public function uploadFile ()
	{
		$file = $this->uploadOneFile('file', 'chat/', 1024*1024*10);
		$data = [
			'code'	=> 0,
			'msg'	=> '',
			'data'	=> [
				'src'	=> '',
				'name'	=> ''
			]
		];
		if (!is_array($file)) {
			$data['code'] = 1;
			$data['msg'] = $file;
		}else{
			$data['data']['src'] = asset('storage/' . $file['path']);
			$data['data']['name'] = $file['originalName'];
		}
		return response()->json($data);
	}

	public function test()
	{
//		$group = Group::query()->find(2);
//		var_dump($group->members()->where('user_id', 100001)->exists());
	}
}
