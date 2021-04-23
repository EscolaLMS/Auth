<?php

namespace EscolaLms\Auth\Tests\API\Admin;

use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Models\UserSetting;

class UserSettingsApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    public function testGetSettings(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $setting = UserSetting::factory()->createOne([
            'user_id' => $user->getKey(),
            'key' => 'test key',
            'value' => 'test value'
        ]);
        $setting2 = UserSetting::factory()->createOne([
            'user_id' => $user->getKey(),
            'key' => 'test key 2',
            'value' => 'test value 2'
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/' . $user->id . '/settings');

        $this->response
            ->assertOk()
            ->assertJsonFragment([
                $setting->key => $setting->value,
                $setting2->key => $setting2->value
            ]);
    }

    public function testPatchSettings(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $setting = UserSetting::factory()->createOne([
            'user_id' => $user->getKey(),
            'key' => 'test key',
            'value' => 'test value'
        ]);
        $setting_to_be_patched = UserSetting::factory()->createOne([
            'user_id' => $user->getKey(),
            'key' => 'test key 2',
            'value' => 'test value 2'
        ]);

        $setting_patch_value = 'test changed value';

        $this->response = $this->actingAs($admin)->json('PATCH', '/api/admin/users/' . $user->id . '/settings', [
            'settings' => [
                [
                    'key' => $setting_to_be_patched->key,
                    'value' => $setting_patch_value
                ]
            ]
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment([
                $setting->key => $setting->value,
                $setting_to_be_patched->key => $setting_patch_value
            ])
            ->assertJsonMissing([
                $setting_to_be_patched->key => $setting_to_be_patched->value
            ]);

        $setting_to_be_patched->refresh();
        $this->assertEquals($setting_patch_value, $setting_to_be_patched->value);
    }

    public function testPutSettings(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $setting = UserSetting::factory()->createOne([
            'user_id' => $user->getKey(),
            'key' => 'test key',
            'value' => 'test value'
        ]);
        $setting2 = UserSetting::factory()->createOne([
            'user_id' => $user->getKey(),
            'key' => 'test key 2',
            'value' => 'test value 2'
        ]);

        $new_setting = UserSetting::factory()->makeOne(
            [
                'key' => 'test key',
                'value' => 'test value new'
            ]
        );
        $new_setting2 = UserSetting::factory()->makeOne(
            [
                'key' => 'test key 3',
                'value' => 'test value 3'
            ]
        );

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->id . '/settings', [
            'settings' => [
                $new_setting->attributesToArray(),
                $new_setting2->attributesToArray()
            ]
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment([
                $new_setting->key => $new_setting->value,
                $new_setting2->key => $new_setting2->value
            ])
            ->assertJsonMissing([
                $setting->key => $setting->value,
            ])
            ->assertJsonMissing([
                $setting2->key => $setting2->value,
            ]);
    }
}
