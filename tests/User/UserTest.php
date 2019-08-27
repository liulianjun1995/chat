<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/6/28
 * Time: 16:03
 */

namespace Tests\User;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
	public function testHello ()
	{
		$user = User::query()->find(1);
		dump($user->id);
	}


}
