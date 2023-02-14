<?php

namespace Database\Factories\EscolaLms\Auth\Models;

use EscolaLms\Auth\Enums\SocialiteProvidersEnum;
use EscolaLms\Auth\Models\PreUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PreUserFactory extends Factory
{
    protected $model = PreUser::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'provider' => $this->faker->randomElement(SocialiteProvidersEnum::getValues()),
            'provider_id' => $this->faker->randomNumber(6),
            'token' => Str::random(32),
        ];
    }
}
