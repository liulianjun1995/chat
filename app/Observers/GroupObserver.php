<?php

namespace App\Observers;

use App\Models\Group;

class GroupObserver
{
    public function created(Group $group)
    {
        $group->avatar = 'img/group_type_' . $group->type . '.png';
        $group->save();
    }
}
