<?php

namespace EscolaLms\Auth\Tests\API\Admin;

use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class UserInterestsApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    public function testUpdateInterests(): void
    {
        $user = $this->makeStudent();
        $admin = $this->makeAdmin();

        $this->assertEquals(false, $user->onboarding_completed);
        $category = Category::factory()->create();
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
}
