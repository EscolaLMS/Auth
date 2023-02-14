<?php

namespace Database\Factories\EscolaLms\Auth\Models;

use App\Models\User;
use EscolaLms\Auth\Enums\SocialiteProvidersEnum;
use EscolaLms\Auth\Models\SocialAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialAccountFactory extends Factory
{
    protected $model = SocialAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => $this->faker->randomElement(SocialiteProvidersEnum::getValues()),
            'provider_id' => $this->faker->randomNumber(6),
        ];
    }
}
