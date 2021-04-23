<?php

namespace EscolaLms\Auth\Tests\API\Admin;

use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class UserInterestsApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    public function testGetInterests(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->create();

        $user->interests()->attach($category->getKey());
        $user->interests()->attach($category2->getKey());

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/' . $user->id . '/interests');

        $this->response
            ->assertOk()
            ->assertJsonFragment(['id' => $category->getKey()])
            ->assertJsonFragment(['id' => $category2->getKey()]);
    }

    public function testUpdateInterests(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->assertEquals(false, $user->onboarding_completed);
        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->create();

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->id . '/interests', [
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

    public function testAddInterest(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->create();

        $user->interests()->attach($category->getKey());

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/' . $user->id . '/interests');

        $this->response
            ->assertOk()
            ->assertJsonFragment(['id' => $category->getKey()])
            ->assertJsonMissing(['id' => $category2->getKey()]);

        $this->response = $this->actingAs($admin)->json('POST', '/api/admin/users/' . $user->id . '/interests', [
            'interest_id' => $category2->getKey()
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment(['id' => $category->getKey()])
            ->assertJsonFragment(['id' => $category2->getKey()]);

        $user->refresh();
        $this->assertEquals($category->getKey(), $user->interests[0]->getKey());
        $this->assertEquals($category2->getKey(), $user->interests[1]->getKey());
    }

    public function testRemoveInterest(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->create();

        $user->interests()->attach($category->getKey());
        $user->interests()->attach($category2->getKey());

        $user->refresh();
        $this->assertEquals(2, count($user->interests));

        $this->response = $this->actingAs($admin)->json('DELETE', '/api/admin/users/' . $user->id . '/interests/' . $category2->getKey());

        $this->response
            ->assertOk()
            ->assertJsonFragment(['id' => $category->getKey()])
            ->assertJsonMissing(['id' => $category2->getKey()]);

        $user->refresh();
        $this->assertEquals(1, count($user->interests));
    }
}
