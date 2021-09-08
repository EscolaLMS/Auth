<?php

namespace EscolaLms\Auth\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use EscolaLms\Auth\Models\Group;

class UserGroupsSeeder extends Seeder
{
    public function run()
    {
        $users = User::factory()->count(15)->create();

        Group::factory()->count(5)->create()->each(function ($group) use ($users) {
            $rnd_users = $users->random(5);
            $group->users()->saveMany($rnd_users);
        });
    }
}
