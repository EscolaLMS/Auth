<?php

namespace EscolaLms\Auth\Tests\API;

use EscolaLms\Auth\Enums\SocialiteProvidersEnum;
use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Events\AccountRegistered;
use EscolaLms\Auth\Models\PreUser;
use EscolaLms\Auth\Models\SocialAccount;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Mockery\MockInterface;

class SocialAuthApiTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private string $provider;
    private MockInterface $socialite;
    private MockInterface $socialUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = SocialiteProvidersEnum::GOOGLE;
        $this->socialite = Mockery::mock('Laravel\Socialite\SocialiteManager');
        $this->socialite->shouldReceive('stateless', 'with')->andReturn($this->socialite);
        Socialite::shouldReceive('driver')->with($this->provider)->andReturn($this->socialite);
        $this->socialUser = Mockery::mock('Laravel\Socialite\Two\User');
        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.socialite_remember_me', false);
    }

    public function testShouldReturnValidationErrorIllegalProvider(): void
    {
        $this->getJson('api/auth/social/test')
            ->assertJsonValidationErrorFor('provider');
    }

    public function testShouldRedirectToSocialProvider(): void
    {
        $returnUrl = 'http://google.com';

        $this->socialite->shouldReceive('redirect')->andReturn(new RedirectResponse($returnUrl));

        $this->getJson('api/auth/social/' . $this->provider)
            ->assertRedirect($returnUrl);
    }

    public function testShouldFindSocialAccountAndRedirectWithUserToken(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory([
            'user_id' => $user->getKey(),
            'provider' => $this->provider,
        ])->create();


        $this->socialUser->shouldReceive('getId')->andReturn($socialAccount->provider_id);
        $this->socialite->shouldReceive('user')->andReturn($this->socialUser);

        $this->getJson('api/auth/social/' . $this->provider . '/callback')
            ->assertRedirect();
    }

    public function testShouldFindSocialAccountAndRedirectWithUserRememberMeToken(): void
    {
        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.socialite_remember_me', true);

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory([
            'user_id' => $user->getKey(),
            'provider' => $this->provider,
        ])->create();

        $this->socialUser->shouldReceive('getId')->andReturn($socialAccount->provider_id);
        $this->socialite->shouldReceive('user')->andReturn($this->socialUser);

        $this->getJson('api/auth/social/' . $this->provider . '/callback')
            ->assertRedirect();

        $token = $user->tokens->first();
        $createdAt = new Carbon($token->created_at);
        $expiresAt = new Carbon($token->expires_at);

        $this->assertEquals($expiresAt->format('Y-m-d'), $createdAt->addMonth()->format('Y-m-d'));
    }

    public function testShouldFindUserBySocialEmailAndRedirect(): void
    {
        $user = User::factory()->create();

        $this->socialUser->shouldReceive('getId')->andReturn(-123);
        $this->socialUser->shouldReceive('getEmail')->andReturn($user->email);
        $this->socialite->shouldReceive('user')->andReturn($this->socialUser);

        $this->getJson('api/auth/social/' . $this->provider . '/callback')
            ->assertRedirect();

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->getKey(),
            'provider' => $this->provider,
        ]);
    }

    public function testShouldCreateUserWithSocialEmailAndRedirect(): void
    {
        $email = $this->faker->unique()->email;
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;

        $this->socialUser->shouldReceive('getId')->andReturn($this->faker->randomNumber(3));
        $this->socialUser->shouldReceive('getEmail')->andReturn($email);
        $this->socialUser->shouldReceive('getName')->andReturn($firstName . ' ' . $lastName);
        $this->socialite->shouldReceive('user')->andReturn($this->socialUser);

        $this->getJson('api/auth/social/' . $this->provider . '/callback')
            ->assertRedirect();

        $user = User::query()->where('email', $email)->first();
        $this->assertNotEmpty($user);
        $this->assertEquals($email, $user->email);
        $this->assertEquals($firstName, $user->first_name);
        $this->assertEquals($lastName, $user->last_name);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->getKey(),
            'provider' => $this->provider,
        ]);
    }

    public function testShouldCreatePreUserWhenEmailIsEmpty(): void
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;

        $this->socialUser->shouldReceive('getId')->andReturn($this->faker->randomNumber(3));
        $this->socialUser->shouldReceive('getEmail')->andReturn(null);
        $this->socialUser->shouldReceive('getName')->andReturn($firstName . ' ' . $lastName);
        $this->socialite->shouldReceive('user')->andReturn($this->socialUser);

        $response = $this->getJson('api/auth/social/' . $this->provider . '/callback')
            ->assertRedirect();
        $this->assertStringContainsString('complete=false', $response->headers->get('Location'));

        $this->assertDatabaseHas('pre_users', [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }

    public function testShouldNotReturnTokenWhenUserIsNotVerified(): void
    {
        $user = User::factory(['email_verified_at' => null])->create();

        $socialAccount = SocialAccount::factory([
            'user_id' => $user->getKey(),
            'provider' => $this->provider,
        ])->create();

        $this->socialUser->shouldReceive('getId')->andReturn($socialAccount->provider_id);
        $this->socialite->shouldReceive('user')->andReturn($this->socialUser);

        $response = $this->getJson('api/auth/social/' . $this->provider . '/callback')
            ->assertRedirect();

        $this->assertStringContainsString('token=&complete=true&error=true', $response->headers->get('Location'));
    }

    public function testShouldCreateUserFromPreUser(): void
    {
        Event::fake([AccountRegistered::class]);

        $preUser = PreUser::factory()->create();
        $email = $this->faker->unique()->email;

        $this->postJson('api/auth/social/complete/' . $preUser->token, [
            'email' => $email,
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'first_name' => $preUser->first_name,
            'last_name' => $preUser->last_name,
            'email_verified_at' => null,
        ]);

        Event::assertDispatched(AccountRegistered::class);
    }

    public function testShouldNotCreateUserFromPreUserWhenTokenExpired(): void
    {
        Event::fake([AccountRegistered::class]);

        $preUser = PreUser::factory()->create();
        $email = $this->faker->unique()->email;

        $this->travel(12)->minutes();

        $this->postJson('api/auth/social/complete/' . $preUser->token, [
            'email' => $email,
        ])->assertStatus(400);

        Event::assertNotDispatched(AccountRegistered::class);
    }
}
