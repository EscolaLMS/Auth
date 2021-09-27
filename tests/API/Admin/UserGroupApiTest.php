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
        $group = Group::factory()->count(5)->create();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/');
        $this->response->assertOk();
        $this->assertGreaterThanOrEqual(5, count($this->response->getData()->data));
    }

    public function testSearchGroups(): void
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();
        Group::factory()->count(4)->create();
        /** @var Group $group */
        $group = Group::factory()->create(['name' => 'asdfasdf']);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/', ['search' => 'asdfasdf']);
        $this->response->assertOk();
        $this->response->assertJsonCount(1, 'data');
        $this->response->assertJsonFragment([
            'id' => $group->getKey(),
            'name' => $group->name,
        ]);
    }

    public function testListTree()
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $groups = Group::factory()->count(4)->create();
        /** @var Group $group */
        $group = Group::factory()->create(['name' => 'Parent']);
        $group->children()->saveMany($groups);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/tree/');
        $this->response->assertOk();

        $count = Group::where('parent_id', null)->count();

        $this->response->assertJsonCount($count, 'data');

        $data = $this->response->json('data');
        foreach ($data as $parent_group) {
            if ($parent_group['name'] === 'Parent') {
                $this->assertEquals(4, count($parent_group['subgroups']));
                $this->assertEquals('Parent. ' . ucfirst($groups->get(0)->name), $parent_group['subgroups'][0]['name_with_breadcrumbs']);
            }
        }
    }

    public function testSearchTree()
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $groups = Group::factory()->count(4)->create();
        /** @var Group $group */
        $group = Group::factory()->create(['name' => 'Parent']);
        $group->children()->saveMany($groups);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/tree/', [
            'parent_id' => $group->getKey()
        ]);
        $this->response->assertOk();
        $this->response->assertJsonCount(4, 'data');
    }

    public function testCreateGroup(): void
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();
        /** @var Group $group */
        $group = Group::factory()->make([
            'registerable' => true,
        ]);

        $this->response = $this->actingAs($admin)->json('POST', '/api/admin/user-groups/', $group->toArray());
        $this->response->assertStatus(201);

        $id = $this->response->json('data.id');
        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $id);
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'name' => $group->name,
            'registerable' => $group->registerable,
            'parent_id' => null,
        ]);
    }

    public function testCreateGroupWithParent(): void
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();
        /** @var Group $group */
        $group = Group::factory()->make();

        $this->response = $this->actingAs($admin)->json('POST', '/api/admin/user-groups/', $group->toArray());
        $this->response->assertStatus(201);

        $id = $this->response->json('data.id');

        $group2 = Group::factory()->make(['parent_id' => $id]);

        $this->response = $this->actingAs($admin)->json('POST', '/api/admin/user-groups/', $group2->toArray());
        $this->response->assertStatus(201);

        $id2 = $this->response->json('data.id');

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $id);
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'name' => $group->name,
            'registerable' => $group->registerable,
            'parent_id' => null,
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $id2);
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'name' => $group2->name,
            'registerable' => $group2->registerable,
            'parent_id' => $id,
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
