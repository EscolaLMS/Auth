<?php

namespace EscolaLms\Auth\Tests\API;

use Carbon\Carbon;
use EscolaLms\Auth\Events\PasswordForgotten;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;

class AuthApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    public function testRegister(): void
    {
        Notification::fake();

        $this->response = $this->json('POST', '/api/auth/register', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
            'password' => 'testtest',
            'password_confirmation' => 'testtest',
        ]);

        $this->assertApiSuccess();
        $this->assertDatabaseHas('users', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
        ]);

        Notification::assertSentTo(User::where('email', 'test@test.test')->first(), VerifyEmail::class);
    }

    public function testLogin(): void
    {
        $this->makeStudent([
            'email' => 'test@test.test',
            'password' => Hash::make('testtest'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->response = $this->json('POST', '/api/auth/login', [
            'email' => 'test@test.test',
            'password' => 'testtest',
        ]);

        $this->assertApiSuccess();
        $this->response->assertJsonStructure([
            'data' => [
                'token'
            ]
        ]);

        $responseContent = $this->response->json();
        $this->assertGreaterThan(0, strlen($responseContent['data']['token']));
    }

    public function testCantLoginWithoutEmailVerified(): void
    {
        $this->makeStudent([
            'email' => 'test@test.test',
            'password' => Hash::make('testtest'),
            'email_verified_at' => null,
        ]);

        $this->response = $this->json('POST', '/api/auth/login', [
            'email' => 'test@test.test',
            'password' => 'testtest',
        ]);

        $this->response->assertStatus(422);
    }

    public function testCantLoginWithInvalidCredentials(): void
    {
        $this->makeStudent([
            'email' => 'test@test.test',
            'password' => Hash::make('testtest')
        ]);

        $this->response = $this->json('POST', '/api/auth/login', [
            'email' => 'test@test.test',
            'password' => 'test',
        ]);

        $this->response->assertStatus(422);

        $this->response = $this->json('POST', '/api/auth/login', [
            'email' => 'test@te.test',
            'password' => 'testtest',
        ]);

        $this->response->assertStatus(422);
    }

    public function testLogout(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        Passport::actingAs($user);
        $tokenConfig = config('passport.personal_access_client.secret');
        $token = $user->createToken($tokenConfig)->accessToken;
        $this->response = $this->json('POST', '/api/auth/logout', [], [
            'Authorization' => "Bearer $token",
        ]);
        $this->assertApiSuccess();
    }

    public function testForgotPassword(): void
    {
        Event::fake();

        $user = $this->makeStudent();

        $this->response = $this->json('POST', '/api/auth/password/forgot', [
            'email' => $user->email,
            'return_url' => 'http://localhost/password-forgot',
        ]);

        $this->assertApiSuccess();
        Event::assertDispatched(PasswordForgotten::class);
    }

    public function testForgotPasswordWithoutUser(): void
    {
        Event::fake();

        $this->response = $this->json('POST', '/api/auth/password/forgot', [
            'email' => 'not-valid-email@example.com',
            'return_url' => 'http://localhost/password-forgot',
        ]);

        $this->response->assertStatus(422);
        Event::assertNotDispatched(PasswordForgotten::class);
    }

    public function testResetPassword(): void
    {
        $user = $this->makeStudent([
            'password_reset_token' => 'test',
        ]);

        $this->response = $this->json('POST', '/api/auth/password/reset', [
            'email' => $user->email,
            'token' => 'test',
            'password' => 'zaq1@WSX',
        ]);

        $this->assertApiSuccess();
        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'password_reset_token' => null,
        ]);

        $user->refresh();

        $this->assertTrue(Hash::check('zaq1@WSX', $user->password));
    }

    public function testRefreshToken(): void
    {
        $user = $this->makeStudent();
        $this->response = $this->actingAs($user)->json('GET', '/api/auth/refresh');
        $this->assertApiSuccess();

        $this->response->assertJsonStructure([
            'data' => [
                'token'
            ]
        ]);

        $responseContent = $this->response->json();
        $this->assertGreaterThan(0, strlen($responseContent['data']['token']));
    }

    public function testResendEmailVerification(): void
    {
        Notification::fake();

        $user = $this->makeStudent();
        $user->email_verified_at = null;
        $user->save();

        $this->response = $this->json('POST', '/api/auth/email/resend', [
            'email' => $user->email,
        ]);
        $this->assertApiSuccess();

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
