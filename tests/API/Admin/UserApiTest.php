<?php

namespace EscolaLms\Auth\Tests\API\Admin;

use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    public function testGetUser(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/' . $user->getKey());
        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $user->getKey(),
                'email' => $user->email,
                'first_name' => $user->first_name
            ]);
    }

    public function testUnauthorizedIfNotUser(): void
    {
        $this->withMiddleware();

        /** @var User $user */
        $user = $this->makeStudent();

        $this->response = $this->json('GET', '/api/admin/users/' . $user->id);
        $this->response
            ->assertUnauthorized();
    }

    public function testForbiddenIfNotAdmin(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();

        $this->response = $this->actingAs($user)->json('GET', '/api/admin/users/' . $user->id);
        $this->response
            ->assertForbidden();
    }

    public function testCreateUser()
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $password = 'secret';
        $userData = User::factory()->raw([
            'roles' => [UserRole::STUDENT],
            'password' => $password
        ]);
        unset($userData['email_verified_at']);
        unset($userData['remember_token']);

        $this->response = $this->actingAs($admin)->json('POST', '/api/admin/users/', $userData);

        unset($userData['password']);
        unset($userData['roles']);

        $this->response
            ->assertCreated()
            ->assertJsonFragment($userData);
    }

    public function testPatchUser()
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $new_first_name = $user->first_name . ' new';

        $this->response = $this->actingAs($admin)->json('PATCH', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $new_first_name
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'first_name' => $new_first_name,
                'last_name' => $user->last_name
            ])
            ->assertJsonMissing([
                'first_name' => $user->first_name
            ]);

        $user->refresh();
        $this->assertEquals($user->first_name, $new_first_name);
    }

    public function testPutUser()
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $new_first_name = $user->first_name . ' new';

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $new_first_name,
            'last_name' => $user->last_name,
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'first_name' => $new_first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ])
            ->assertJsonMissing([
                'first_name' => $user->first_name,
            ]);
    }

    public function testFailValidationTryingToPutUserWithoutAllRequiredData()
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $new_first_name = $user->first_name . ' new';

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $new_first_name
        ]);

        $this->response
            ->assertStatus(422);
    }

    public function testCanNotSetEmailToNull()
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => null
        ]);

        $this->response
            ->assertStatus(422);

        $this->response = $this->actingAs($admin)->json('PATCH', '/api/admin/users/' . $user->getKey(), [
            'email' => null
        ]);

        $this->response
            ->assertStatus(422);
    }

    public function testUpdatePassword()
    {
        $password = 'password';

        /** @var User $user */
        $user = $this->makeStudent([
            'password' => Hash::make($password)
        ]);
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->assertTrue(Hash::check($password, $user->password));

        $newpassword = 'newpassword';
        $this->response = $this->actingAs($admin)->json('PATCH', '/api/admin/users/' . $user->getKey(), [
            'password' => $newpassword
        ]);

        $this->response
            ->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check($newpassword, $user->password));
    }

    public function testDeleteUser()
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/' . $user->getKey());
        $this->response
            ->assertStatus(200)
            ->assertJsonFragment([
                'email' => $user->email
            ]);

        $this->response = $this->actingAs($admin)->json('DELETE', '/api/admin/users/' . $user->getKey());
        $this->response
            ->assertStatus(200);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/' . $user->getKey());
        $this->response
            ->assertStatus(404)
            ->assertJsonMissing([
                'email' => $user->email
            ]);
    }

    public function testUploadAndDeleteAvatar(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->assertEmpty($user->path_avatar);

        $this->response = $this->actingAs($admin)->json('POST', '/api/admin/users/' . $user->getKey() . '/avatar', [
            'avatar' => UploadedFile::fake()->image('mj.png')
        ]);

        $this->response->assertOk();

        $user->refresh();
        $this->assertNotEmpty($user->path_avatar);

        $this->response = $this->actingAs($admin)->json('DELETE', '/api/admin/users/' . $user->getKey() . '/avatar');
        $this->response->assertOk();

        $user->refresh();
        $this->assertEmpty($user->path_avatar);
    }

    public function testSearchUsers(): void
    {
        $date = now();
        //dd(new Carbon($date));

        /** @var User $user */
        $user = $this->makeStudent([
            'first_name' => 'Jan'
        ]);
        /** @var User $user */
        $user2 = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin([
            'first_name' => 'Jan'
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users');

        $this->response->assertOk();
        $this->response->assertJsonStructure([
            'success',
            'data' => [
                'data',
                'meta'
            ],
            'message',
        ]);

        $meta = $this->response->json('data.meta');

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users?per_page=' . $meta['total']);

        $this->response->assertOk();
        $this->response->assertJsonStructure([
            'success',
            'data' => [
                'data',
                'meta'
            ],
            'message',
        ]);

        $this->response->assertJsonFragment([
            'email' => $user->email
        ]);
        $this->response->assertJsonFragment([
            'email' => $user2->email
        ]);
        $this->response->assertJsonFragment([
            'email' => $admin->email
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?role=admin');
        $this->response->assertOk();
        $this->response->assertJsonMissing([
            'email' => $user->email
        ]);
        $this->response->assertJsonMissing([
            'email' => $user2->email
        ]);
        $this->response->assertJsonFragment([
            'email' => $admin->email
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?search=' . $user->email);
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'email' => $user->email
        ]);
        $this->response->assertJsonMissing([
            'email' => $user2->email
        ]);
        $this->response->assertJsonMissing([
            'email' => $admin->email
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?search=Jan');
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'email' => $user->email
        ]);
        $this->response->assertJsonMissing([
            'email' => $user2->email
        ]);
        $this->response->assertJsonFragment([
            'email' => $admin->email
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?search=Jan&role=admin');
        $this->response->assertOk();
        $this->response->assertJsonMissing([
            'email' => $user->email
        ]);
        $this->response->assertJsonMissing([
            'email' => $user2->email
        ]);
        $this->response->assertJsonFragment([
            'email' => $admin->email
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?search=Jan&role=student');
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'email' => $user->email
        ]);
        $this->response->assertJsonMissing([
            'email' => $user2->email
        ]);
        $this->response->assertJsonMissing([
            'email' => $admin->email
        ]);
    }
}
