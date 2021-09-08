<?php

namespace EscolaLms\Auth\Tests\API\Admin;

use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use EscolaLms\Auth\Models\Group;
use Illuminate\Testing\TestResponse;

class UserGroupApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    public function testGetGroup(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();
        /** @var Group $group */
        $group = Group::factory()->create();
        $group->users()->attach($user);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $group->getKey());
        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $group->getKey(),
                'name' => $group->name,
            ])
            ->assertJsonFragment([
                'id' => $user->getKey(),
                'first_name' => $user->first_name
            ]);
    }

    public function testListGroups(): void
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();
        /** @var Group $group */
        $group = Group::factory()->count(5)->create();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/');
        $this->response->assertOk();
        $this->assertGreaterThanOrEqual(count($this->response->getData()->data), 5);
        // $this->response->assertJsonCount(5, 'data');
    }

    public function testCreateGroup(): void
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();
        /** @var Group $group */
        $group = Group::factory()->make();

        $this->response = $this->actingAs($admin)->json('POST', '/api/admin/user-groups/', $group->toArray());
        $this->response->assertStatus(201);

        $id = $this->response->json('data.id');
        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $id);
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'name' => $group->name,
        ]);
    }

    public function testUpdateGroup(): void
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();
        /** @var Group $group */
        $group = Group::factory()->create();

        $newName = 'asdf asdf';
        $this->assertNotEquals($newName, $group->name);

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/user-groups/' . $group->getKey(), [
            'name' => $newName
        ]);
        $this->response->assertOk();

        $group->refresh();
        $this->assertEquals($newName, $group->name);
    }

    public function testDeleteGroup(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();
        /** @var Group $group */
        $group = Group::factory()->create();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $group->getKey());
        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $group->getKey(),
                'name' => $group->name,
            ]);

        $this->response = $this->actingAs($admin)->json('DELETE', '/api/admin/user-groups/' . $group->getKey());
        $this->response->assertOk();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $group->getKey());
        $this->response->assertStatus(404);
    }

    public function testAddMember(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();
        /** @var Group $group */
        $group = Group::factory()->create();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $group->getKey());
        $this->response
            ->assertOk()
            ->assertJsonMissing([
                'id' => $user->getKey(),
                'first_name' => $user->first_name
            ]);

        $this->response = $this->actingAs($admin)->json('POST', '/api/admin/user-groups/' . $group->getKey() . '/members', [
            'user_id' => $user->getKey()
        ]);
        $this->response->assertOk();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $group->getKey());
        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $user->getKey(),
                'first_name' => $user->first_name
            ]);
    }

    public function testRemoveMember(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();
        /** @var Group $group */
        $group = Group::factory()->create();
        $group->users()->attach($user);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $group->getKey());
        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $user->getKey(),
                'first_name' => $user->first_name
            ]);

        $this->response = $this->actingAs($admin)->json('DELETE', '/api/admin/user-groups/' . $group->getKey() . '/members/' . $user->getKey());
        $this->response->assertOk();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $group->getKey());
        $this->response
            ->assertOk()
            ->assertJsonMissing([
                'id' => $user->getKey(),
                'first_name' => $user->first_name
            ]);
    }
}
