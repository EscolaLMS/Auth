<?php

namespace EscolaLms\Auth\Tests\API\Admin;

use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

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
            'email' => $user->email,
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

    public function testFailValidationTryingToPutUser()
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
}
