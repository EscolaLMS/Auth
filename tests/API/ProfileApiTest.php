<?php

namespace EscolaLms\Auth\Tests\API;

use EscolaLms\Auth\Enums\GenderType;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    public function testMyProfile(): void
    {
        $user = $this->makeStudent([
            'phone' => '+48600600600'
        ]);
        $this->response = $this->actingAs($user)->json('GET', '/api/profile/me');

        $this->response
            ->assertOk()
            ->assertJsonFragment(['first_name' => $user->first_name])
            ->assertJsonFragment(['last_name' => $user->last_name])
            ->assertJsonFragment(['phone' => $user->phone])
            ->assertJsonFragment(['onboarding_completed' => $user->onboarding_completed]);
    }

    public function testUpdateProfile(): void
    {
        $user = $this->makeStudent();
        $this->response = $this->actingAs($user)->json('PUT', '/api/profile/me', [
            'first_name' => 'Janusz',
            'last_name' => 'Claus',
            'gender' => GenderType::Female,
            'age' => 28,
            'country' => 'Poland',
            'city' => 'Gdańsk',
            'street' => 'Strzelecka',
            'phone' => '+48600600600'
        ]);
        $this->assertApiSuccess();
        $user->refresh();
        $this->assertEquals('Janusz', $user->first_name);
        $this->assertEquals('Claus', $user->last_name);
        $this->assertEquals(GenderType::Female, $user->gender);
        $this->assertEquals(28, $user->age);
        $this->assertEquals('Poland', $user->country);
        $this->assertEquals('Gdańsk', $user->city);
        $this->assertEquals('Strzelecka', $user->street);
        $this->assertEquals('+48600600600', $user->phone);
    }

    public function testUpdateProfileAuthData(): void
    {
        $user = $this->makeStudent();
        $this->response = $this->actingAs($user)->json('PUT', '/api/profile/me-auth', [
            'email' => 'test@test.test',
        ]);
        $this->assertApiSuccess();
        $user->refresh();
        $this->assertEquals('test@test.test', $user->email);
    }

    public function testUpdateProfilePassword(): void
    {
        $user = $this->makeStudent([
            'password' => Hash::make('testowehasło'),
        ]);
        $this->response = $this->actingAs($user)->json('PUT', '/api/profile/password', [
            'current_password' => 'testowehasło',
            'new_password' => 'zmienionetestowehasło',
            'new_confirm_password' => 'zmienionetestowehasło',
        ]);
        $this->assertApiSuccess();
        $user->refresh();
        $this->assertTrue(Hash::check('zmienionetestowehasło', $user->password));
    }

    public function testUpdateInterests(): void
    {
        $user = $this->makeStudent();
        $this->assertEquals(false, $user->onboarding_completed);
        $category = Category::factory()->create();
        $category2 = Category::factory()->create();

        $this->response = $this->actingAs($user)->json('PUT', '/api/profile/interests', [
            'interests' => [
                $category->getKey(),
                $category2->getKey(),
            ],
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment(['id' => $category->getKey()])
            ->assertJsonFragment(['id' => $category2->getKey()]);

        $user->refresh();
        $this->assertEquals(true, $user->onboarding_completed);
        $this->assertEquals($category->getKey(), $user->interests[0]->getKey());
        $this->assertEquals($category2->getKey(), $user->interests[1]->getKey());
    }

    public function testSettingsIndex(): void
    {
        $user = $this->makeStudent();
        DB::table('user_settings')->insert([
            'user_id' => $user->getKey(),
            'key' => 'test-key',
            'value' => 'test-value',
        ]);
        DB::table('user_settings')->insert([
            'user_id' => $user->getKey(),
            'key' => 'key2',
            'value' => 'value2',
        ]);

        $this->response = $this->actingAs($user)->json('GET', '/api/profile/settings');
        $this->response
            ->assertOk()
            ->assertJsonFragment(['test-key' => 'test-value'])
            ->assertJsonFragment(['key2' => 'value2']);
    }

    public function testSettingsUpdate(): void
    {
        $user = $this->makeStudent();

        $this->response = $this->actingAs($user)->json('PUT', '/api/profile/settings', [
            'key-test' => 'value-test',
            'key2' => 'value2',
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment(['key-test' => 'value-test']);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->getKey(),
            'key' => 'key-test',
            'value' => 'value-test',
        ]);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->getKey(),
            'key' => 'key2',
            'value' => 'value2',
        ]);
    }

    public function testUploadAvatar(): array
    {
        $user = $this->makeStudent();

        $this->response = $this->actingAs($user)->json('POST', '/api/profile/upload-avatar', [
            'avatar' => UploadedFile::fake()->image('mj.png')
        ]);

        $this->response->assertOk();
        $this->assertNotEmpty($user->path_avatar);

        return [$user];
    }

    /**
     * @param array $payload
     * @depends testUploadAvatar
     */
    public function testDeleteAvatar(array $payload): void
    {
        [$user] = $payload;

        $this->response = $this->actingAs($user)->json('DELETE', '/api/profile/delete-avatar');
        $this->response->assertOk();
        $this->assertEmpty($user->path_avatar);
    }
}
