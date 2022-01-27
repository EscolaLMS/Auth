<?php

namespace EscolaLms\Auth\Tests\API;

use EscolaLms\Auth\Enums\AuthPermissionsEnum;
use EscolaLms\Auth\Enums\SettingStatusEnum;
use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Events\AccountMustBeEnableByAdmin;
use EscolaLms\Auth\Events\AccountRegistered;
use EscolaLms\Auth\Events\ForgotPassword;
use EscolaLms\Auth\Events\Login;
use EscolaLms\Auth\Events\Logout;
use EscolaLms\Auth\Events\ResetPassword as ResetPasswordEvent;
use EscolaLms\Auth\Listeners\CreatePasswordResetToken;
use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Notifications\ResetPassword;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;

class AuthApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('escola_settings.use_database', true);
        Config::set('escola_auth.additional_fields_required', []);
    }

    public function testRegister(): void
    {
        Event::fake();
        Notification::fake();
        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.account_must_be_enabled_by_admin', SettingStatusEnum::DISABLED);

        $this->response = $this->json('POST', '/api/auth/register', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
            'password' => 'testtest',
            'password_confirmation' => 'testtest',
        ]);

        $this->assertApiSuccess();
        Event::assertDispatched(AccountRegistered::class);

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
        ]);
        $newUser = User::where('email', 'test@test.test')->first();
        $listener = app(SendEmailVerificationNotification::class);
        $listener->handle(new AccountRegistered($newUser));
        Notification::assertSentTo($newUser, VerifyEmail::class);
    }

    public function testRegisterWithSettingsAndGroup(): void
    {
        Event::fake();
        Notification::fake();
        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.account_must_be_enabled_by_admin', SettingStatusEnum::DISABLED);

        /** @var Group $group */
        $group = Group::factory()->create(['registerable' => true]);

        $this->response = $this->json('POST', '/api/auth/register', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
            'password' => 'testtest',
            'password_confirmation' => 'testtest',
            'groups' => [
                $group->getKey(),
            ],
            'settings' => [
                [
                    'key' => 'test-setting-key',
                    'value' => 'test-setting-value',
                ]
            ]
        ]);

        $this->assertApiSuccess();
        Event::assertDispatched(AccountRegistered::class);
        $this->assertDatabaseHas('users', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
        ]);

        $newUser = User::where('email', 'test@test.test')->first();
        $listener = app(SendEmailVerificationNotification::class);
        $listener->handle(new AccountRegistered($newUser));
        Notification::assertSentTo($newUser, VerifyEmail::class);

        /** @var User $user */
        $user = User::where('email', 'test@test.test')->first();

        $this->assertEquals($group->getKey(), $user->groups->get(0)->id);
        $this->assertEquals('test-setting-value', $user->settings->get(0)->value);
        $this->assertEquals('test-setting-key', $user->settings->get(0)->key);
    }

    public function testRegisterWithAdditionalFields(): void
    {
        Notification::fake();
        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY  . '.additional_fields', [
            'additional_field_a',
            'additional_field_b',
        ]);
        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY  . '.additional_fields_required', [
            'additional_field_a',
        ]);
        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.account_must_be_enabled_by_admin', SettingStatusEnum::DISABLED);

        $this->response = $this->json('POST', '/api/auth/register', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
            'password' => 'testtest',
            'password_confirmation' => 'testtest',
            'additional_field_b' => 123
        ]);

        $this->response->assertStatus(422);
        $this->response->assertJsonValidationErrors([
            'additional_field_b',
            'additional_field_a',
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
        ]);

        $this->response = $this->json('POST', '/api/auth/register', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
            'password' => 'testtest',
            'password_confirmation' => 'testtest',
            'additional_field_a' => 'string1',
            'additional_field_b' => 'string2'
        ]);

        $this->assertApiSuccess();
        $this->assertDatabaseHas('users', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
        ]);

        Notification::assertSentTo(User::where('email', 'test@test.test')->first(), VerifyEmail::class);

        $user = User::where('email', 'test@test.test')->first();

        $this->assertEquals('string1', $user->settings->where('key', 'additional_field:additional_field_a')->first()->value);
        $this->assertEquals('string2', $user->settings->where('key', 'additional_field:additional_field_b')->first()->value);
    }

    public function testLogin(): void
    {
        Event::fake();
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
        Event::assertDispatched(Login::class);
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

    public function testCanLoginWithoutEmailVerifiedIfSuperadmin(): void
    {
        $this->makeStudent([
            'email' => 'test@test.test',
            'password' => Hash::make('testtest'),
            'email_verified_at' => null,
        ]);

        Config::set('escola_auth.superadmins', ['test@test.test']);

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
        Event::fake();
        /** @var User $user */
        $user = $this->makeStudent();
        Passport::actingAs($user);
        $tokenConfig = config('passport.personal_access_client.secret');
        $token = $user->createToken($tokenConfig)->accessToken;
        $this->response = $this->json('POST', '/api/auth/logout', [], [
            'Authorization' => "Bearer $token",
        ]);
        $this->assertApiSuccess();
        Event::assertDispatched(Logout::class);
    }

    public function testForgotPassword(): void
    {
        Event::fake();
        Notification::fake();

        $user = $this->makeStudent();

        $this->response = $this->json('POST', '/api/auth/password/forgot', [
            'email' => $user->email,
            'return_url' => 'http://localhost/password-forgot',
        ]);

        $this->assertApiSuccess();
        Event::assertDispatched(ForgotPassword::class);

        $event = new ForgotPassword($user, 'http://localhost/password-forgot');
        $listener = app(CreatePasswordResetToken::class);
        $listener->handle($event);

        Notification::assertSentTo($user, ResetPassword::class);

        $notification = new ResetPassword($user->password_reset_token, $event->getReturnUrl());
        $mail = $notification->toMail($user);
        $this->assertTrue($mail instanceof \Illuminate\Notifications\Messages\MailMessage);
    }

    public function testForgotPasswordWithoutUser(): void
    {
        Event::fake();

        $this->response = $this->json('POST', '/api/auth/password/forgot', [
            'email' => 'not-valid-email@example.com',
            'return_url' => 'http://localhost/password-forgot',
        ]);

        $this->response->assertStatus(200);
        Event::assertNotDispatched(ForgotPassword::class);
    }

    public function testForgotPasswordTurnOffEvent(): void
    {
        Event::fake();
        Notification::fake();
        CreatePasswordResetToken::setRunEventForgotPassword(
            fn () => false
        );
        $user = $this->makeStudent();

        $event = new ForgotPassword($user, 'http://localhost/password-forgot');
        $listener = app(CreatePasswordResetToken::class);
        $listener->handle($event);

        Notification::assertNotSentTo($user, ResetPassword::class);
        CreatePasswordResetToken::setRunEventForgotPassword(
            fn () => true
        );
    }

    public function testResetPassword(): void
    {
        Event::fake();
        $user = $this->makeStudent([
            'password_reset_token' => 'test',
        ]);

        $this->response = $this->json('POST', '/api/auth/password/reset', [
            'email' => $user->email,
            'token' => 'test',
            'password' => 'zaq1@WSX',
        ]);

        $this->assertApiSuccess();
        Event::assertDispatched(ResetPasswordEvent::class);
        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'password_reset_token' => null,
        ]);

        $user->refresh();

        $this->assertTrue(Hash::check('zaq1@WSX', $user->password));
    }

    public function testForgotAndResetPassword(): void
    {
        Event::fake();
        Notification::fake();

        $user = $this->makeStudent();

        $this->response = $this->json('POST', '/api/auth/password/forgot', [
            'email' => $user->email,
            'return_url' => 'http://localhost/password-forgot',
        ]);

        $this->assertApiSuccess();
        $user->refresh();
        Event::assertDispatched(ForgotPassword::class);
        $listener = app(CreatePasswordResetToken::class);
        $listener->handle(new ForgotPassword($user, 'http://localhost/password-forgot'));
        $newPassword = 'zaq1@WSX';
        $this->response = $this->json('POST', '/api/auth/password/reset', [
            'email' => $user->email,
            'token' => $user->password_reset_token,
            'password' => $newPassword,
        ]);

        $this->assertApiSuccess();
        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'password_reset_token' => null,
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
        Event::assertDispatched(ResetPasswordEvent::class);
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

    public function testRegisterableGroups(): void
    {
        Group::factory()->count(2)->create([
            'registerable' => true
        ]);
        Group::factory()->count(3)->create([
            'registerable' => false
        ]);
        $groupsCount = Group::count();

        $this->response = $this->json('GET', '/api/auth/registerable-groups/');
        $this->response->assertOk();
        $this->assertGreaterThanOrEqual(2, count($this->response->getData()->data));
        $this->assertLessThanOrEqual($groupsCount - 3, count($this->response->getData()->data));
    }

    public function testRegistrationFeatureDisabledOrEnabled(): void
    {
        Event::fake();
        Notification::fake();
        $this->withMiddleware();

        $userData = [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
            'password' => 'testtest',
            'password_confirmation' => 'testtest',
        ];

        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY  . '.registration', SettingStatusEnum::DISABLED);
        $this->response = $this->json('POST', '/api/auth/register', $userData);
        $this->response->assertStatus(403);

        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY  . '.registration', SettingStatusEnum::ENABLED);
        $this->response = $this->json('POST', '/api/auth/register', $userData);
        $this->assertApiSuccess();
    }

    public function testRegisterWhenAccountMustBeEnabledByAdmin(): void
    {
        Event::fake();
        Notification::fake();
        Config::set(EscolaLmsAuthServiceProvider::CONFIG_KEY  . '.account_must_be_enabled_by_admin', SettingStatusEnum::ENABLED);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('admin');

        $this->response = $this->json('POST', '/api/auth/register', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
            'password' => 'testtest',
            'password_confirmation' => 'testtest',
        ]);

        $this->assertApiSuccess();
        Event::assertDispatched(AccountMustBeEnableByAdmin::class);

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.test',
            'first_name' => 'tester',
            'last_name' => 'tester',
        ]);

        $newUser = User::where('email', 'test@test.test')->first();
        $this->assertFalse($newUser->is_active);

        Event::assertDispatched(
            AccountMustBeEnableByAdmin::class,
            function (AccountMustBeEnableByAdmin $event) use ($newUser) {
                return $event->getRegisteredUser()->getKey() === $newUser->getKey()
                    && $event->getUser()->hasPermissionTo(AuthPermissionsEnum::USER_VERIFY_ACCOUNT);
            }
        );
    }
}
