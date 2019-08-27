<?php

namespace App\Observers;

use App\Models\FriendGroup;
use App\Models\User;

class UserObserver
{
    /**
     * 处理 User 「新建」事件。
     * @param User $user
     * @param FriendGroup $group
     */
    public function created(User $user)
    {
        // 预置好友分组
        $data = [
            [
                'user_id'   => $user->id,
                'name'      => '我的好友',
                'default'   => 1
            ],
            [
                'user_id'   => $user->id,
                'name'      => '朋友',
            ],
            [
                'user_id'   => $user->id,
                'name'      => '家人',
            ],
            [
                'user_id'   => $user->id,
                'name'      => '同学'
            ],
        ];
        foreach ($data as $value){
            FriendGroup::query()->create($value);
        }
    }
}
