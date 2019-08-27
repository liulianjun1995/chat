<?php

namespace App\Models;

class Friend extends Model
{
    protected $fillable = [
        'user_id', 'friend_id', 'friend_group'
    ];
}
