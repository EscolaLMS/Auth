<?php

namespace EscolaLms\Auth\Tests\API\Admin;

use EscolaLms\Auth\Events\UserAddedToGroup;
use EscolaLms\Auth\Events\UserRemovedFromGroup;
use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\GroupUser;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

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

    public function testListSelfGroups(): void
    {
        /** @var User $user */
        $user = $this->makeInstructor();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $group3 = Group::factory()->create();
        $group2->users()->sync($user);
        $group3->users()->sync($user);

        $this
            ->actingAs($user, 'web')
            ->getJson('/api/admin/user-groups')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJson(['data' => [
                ['id' => $group2->getKey(), 'name' => $group2->name],
                ['id' => $group3->getKey(), 'name' => $group3->name]
            ]])
            ->assertJsonMissing(['data' => [
                ['id' => $group1->getKey(), 'name' => $group1->name]
            ]]);
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

        $groups = Group::factory()->count(100)->create();
        /** @var Group $group */
        $group = Group::factory()->create(['name' => 'Parent']);
        $group->children()->saveMany($groups);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/tree?per_page=-1');
        $this->response->assertOk();

        $count = Group::where('parent_id', null)->count();

        $this->response->assertJsonCount($count, 'data');

        $data = $this->response->json('data');
        foreach ($data as $parent_group) {
            if ($parent_group['name'] === 'Parent') {
                $this->assertEquals(100, count($parent_group['subgroups']));
                $this->assertEquals('Parent. ' . ucfirst($groups->get(0)->name), $parent_group['subgroups'][0]['name_with_breadcrumbs']);
            }
        }
    }

    public function testListSelfGroupTree(): void
    {
        /** @var User $user */
        $user = $this->makeInstructor();

        $groups1 = Group::factory()->count(4)->create()->each(fn($group) => $group->users()->sync($user));
        $group1 = Group::factory()->create(['name' => 'Parent1']);
        $group1->children()->saveMany($groups1);
        $group1->users()->sync($user);

        $groups2 = Group::factory()->count(2)->create();
        $group2 = Group::factory()->create(['name' => 'Parent2']);
        $group2->children()->saveMany($groups2);

        $this->response = $this
            ->actingAs($user, 'web')
            ->getJson('/api/admin/user-groups/tree')
            ->assertOk()
            ->assertJsonCount(4);

        $data = $this->response->json('data');
        $responseGroup1 = collect($data)->filter(fn($group) => $group['id'] === $group1->getKey())->first();
        $responseGroup2 = collect($data)->filter(fn($group) => $group['id'] === $group2->getKey())->first();

        $this->assertCount(4, $responseGroup1['subgroups']);
        $this->assertNull($responseGroup2);
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
        Event::fake();
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
        Event::assertDispatched(UserAddedToGroup::class);
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
        Event::fake();
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
        Event::assertDispatched(UserRemovedFromGroup::class);
        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/user-groups/' . $group->getKey());
        $this->response
            ->assertOk()
            ->assertJsonMissing([
                'id' => $user->getKey(),
                'first_name' => $user->first_name
            ]);
    }
}
