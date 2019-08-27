<?php

namespace App\Models;

class GroupMember extends Model
{
    protected $fillable = [
        'group_id', 'user_id', 'nickname', 'role'
    ];
}
