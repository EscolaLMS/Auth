<?php

namespace Database\Factories\EscolaLms\Auth\Models;

use EscolaLms\Auth\Models\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserSettingFactory extends Factory
{
    protected $model = UserSetting::class;

    public function definition(): array
    {
        return [
            'key' => Str::random(10),
            'value' => Str::random(10),
        ];
    }
}
