<?php

namespace Database\Factories\EscolaLms\Auth\Models;

use EscolaLms\Auth\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word
        ];
    }
}
