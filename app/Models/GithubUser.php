<?php

namespace App\Models;


class GithubUser extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'github_id', 'login', 'name', 'company', 'avatar_url', 'email'
    ];

    public function user()
    {
        return $this->hasOne('App/Models/User', 'github_id', 'github_id');
    }
}
