<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    protected $guarded = [];

    public function friends()
    {
        return $this->belongsToMany(self::class, Friend::class, 'user_id', 'friend_id', 'id');
    }

	public function groups ()
	{
		return $this->belongsToMany(Group::class, GroupMember::class, 'user_id', 'group_id');
	}

	public function chatRecords ()
	{
		return $this->hasMany(ChatRecord::class, 'user_id');
	}

    /**
     * 处理密码
     * @param string    $value   密码
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function getSexFormatAttribute()
    {
        $sex = config('system.sex');
        return $sex[$this->sex] ?: '保密';
    }


	public function getProfessionFormatAttribute()
	{
		$professions = config('system.profession');
		if (!$this->profession){
			return '';
		}
		return $professions[$this->profession] ?: '';
    }

    public function getAddressAttribute()
    {
        $address = Area::query()->whereIn('id', [$this->province, $this->city, $this->district, $this->country])->pluck('areaname')->toArray();
        return implode(' ', $address);
    }


    public function getBirthdayAttribute($value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : null;
    }

    public function setBirthdayAttribute($value)
    {
        $this->attributes['birthday'] = $value ? date('Y-m-d', strtotime($value)) : null;
    }

	public function getAvatarAttribute ($value)
	{
		return imgPath($value);
    }
}
