<?php

namespace EscolaLms\Auth\Listeners;

use EscolaLms\Auth\Events\AccountDeleted;
use Faker\Generator;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MaskUserData
{
    private const SKIPPED_COLUMNS = [
        'created_at',
        'updated_at',
        'deleted_at',
        'email_verified_at',
        'gender',
        'delete_user_token',
        'current_timezone',
    ];

    public function __construct(private Generator $faker)
    {

    }

    public function handle(AccountDeleted $event): void
    {
        $user = $event->getUser();
        
        $columns = $this->getProcessableColumns($user);

        foreach ($columns as $column) {
            if (empty($user->{$column})) {
                continue;
            }
            if (method_exists($this, $method = 'generate' . Str::studly($column))) {
                $user->{$column} = $this->{$method}();
            } else {
                $user->{$column} = $this->generateDefault();
            }
        }

        $user->save();
    }

    private function getProcessableColumns(Authenticatable $user): array
    {
        $allColumns = Schema::getColumnListing($user->getTable());
        $guarded = $user->getGuarded();

        return array_diff($allColumns, $guarded, array_merge(self::SKIPPED_COLUMNS, [$user->getKey()]));
    }


    private function generateDefault(): string
    {
        return $this->faker->unique()->word;
    }

    private function generateEmail(): string
    {
        return $this->faker->unique()->safeEmail;
    }

    private function generateAge(): int
    {
        return $this->faker->numberBetween(18, 99);
    }

    private function generatePoints(): int
    {
        return $this->faker->numberBetween(0, 1000);
    }

    private function generateNotificationChannels(): array
    {
        return [];
    }

    private function generateAccessToDirectories(): array
    {
        return [];
    }

    private function generateFirstName(): string
    {
        return $this->faker->firstName;
    }

    private function generateLastName(): string
    {
        return $this->faker->lastName;
    }

    private function generatePhone(): string
    {
        return $this->faker->phoneNumber;
    }

    private function generateCountry(): string
    {
        return $this->faker->country;
    }

    private function generateCity(): string
    {
        return $this->faker->city;
    }

    private function generateStreet(): string
    {
        return $this->faker->streetAddress;
    }

    private function generatePostcode(): string
    {
        return $this->faker->postcode;
    }
}
