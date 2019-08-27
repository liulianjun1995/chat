<?php

namespace App\Models;

class Group extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'size', 'verify'
    ];

	public function members ()
	{
		return $this->belongsToMany(User::class, GroupMember::class, 'group_id', 'user_id');
    }

	public function chatRecords ()
	{
		return $this->hasMany(ChatRecord::class, 'group_id');
    }

    public function getAvatarAttribute($value)
    {
        return imgPath($value);
    }
}
