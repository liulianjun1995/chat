<?php

namespace App\Models;

class FriendGroup extends Model
{
    protected $guarded = [];

	public function users ()
	{
		return $this->belongsToMany(User::class, Friend::class, 'friend_group', 'friend_id');
    }
}
