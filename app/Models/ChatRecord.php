<?php

namespace App\Models;

class ChatRecord extends Model
{
    protected $fillable = [
    	'user_id', 'friend_id', 'group_id', 'content'
	];

	public function user ()
	{
		return $this->belongsTo(User::class, 'user_id');
    }

	public function friend ()
	{
		return $this->belongsTo(User::class, 'friend_id');
    }

	public function group ()
	{
		return $this->belongsTo(Group::class);
	}
}
