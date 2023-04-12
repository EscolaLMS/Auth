<?php

namespace EscolaLms\Auth\Tests\API\Admin;

use EscolaLms\Auth\Events\AccountBlocked;
use EscolaLms\Auth\Events\AccountConfirmed;
use EscolaLms\Auth\Events\AccountDeleted;
use EscolaLms\Auth\Events\AccountRegistered;
use EscolaLms\Auth\Listeners\SendEmailVerificationNotification;
use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\SocialAccount;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;


class UserApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('escola_settings.use_database', true);
        Cache::flush();
    }

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

    public function testGetUserNotFound(): void
    {
        /** @var User $user */
        $user = $this->makeStudent();
        $user->delete();

        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/' . $user->getKey());
        $this->response->assertStatus(422);
    }

    public function testGetSelf(): void
    {
        /** @var User $tutor */
        $tutor = $this->makeInstructor();

        $this->response = $this->actingAs($tutor)->json('GET', '/api/admin/users/' . $tutor->getKey());
        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $tutor->getKey(),
                'email' => $tutor->email,
                'first_name' => $tutor->first_name
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

    public function testForbiddenIfReadsNotItsOwnData(): void
    {
        /** @var User $user */
        $admin = $this->makeAdmin();
        $user = $this->makeStudent();

        $this->response = $this->actingAs($user)->json('GET', '/api/admin/users/' . $admin->id);
        $this->response
            ->assertForbidden();
    }

    public function testCreateUser()
    {
        Event::fake();
        Notification::fake();

        /** @var User $admin */
        $admin = $this->makeAdmin();

        $password = 'secret';
        $userData = User::factory()->raw([
            'roles' => [UserRole::STUDENT],
            'password' => $password,
            'phone' => '+48600600600'
        ]);
        unset($userData['email_verified_at']);
        unset($userData['remember_token']);

        $this->response = $this
            ->actingAs($admin)
            ->json('POST', '/api/admin/users/', $userData + ['return_url' => 'https://escolalms.com/email/verify']);

        unset($userData['password']);
        unset($userData['roles']);

        $this->response
            ->assertCreated()
            ->assertJsonFragment($userData);

        Event::assertDispatched(Registered::class, function (Registered $event) use ($userData) {
            return $userData['email'] === $event->user->email && is_null($event->user->email_verified_at);
        });
        Event::assertDispatched(AccountRegistered::class);

        $newUser = User::where('email', $userData['email'])->first();
        $listener = app(SendEmailVerificationNotification::class);
        $listener->handle(new AccountRegistered($newUser, 'https://escolalms.com/email/verify'));

        Notification::assertSentTo($newUser, VerifyEmail::class);
    }

    public function testCreateUserWithoutReturnUrl()
    {
        Event::fake();
        Notification::fake();

        /** @var User $admin */
        $admin = $this->makeAdmin();

        $password = 'secret';
        $userData = User::factory()->raw([
            'roles' => [UserRole::STUDENT],
            'password' => $password,
            'phone' => '+48600600600'
        ]);
        unset($userData['email_verified_at']);
        unset($userData['remember_token']);

        $this->response = $this
            ->actingAs($admin)
            ->json('POST', '/api/admin/users/', $userData)
            ->assertUnprocessable();
    }

    public function testCreateUserWithDeletedUserEmail()
    {
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $password = 'secret';
        $userData = User::factory()->raw([
            'roles' => [UserRole::STUDENT],
            'password' => $password,
            'phone' => '+48600600600',
            'email' => 'exsitingemail@example.com',
        ]);

        $userCreated = User::factory()->create([
            'email' => 'exsitingemail@example.com',
        ]);
        $userCreated->delete();

        Event::fake();
        Notification::fake();

        $this->assertSoftDeleted($userCreated);
        unset($userData['email_verified_at']);
        unset($userData['remember_token']);

        $this->response = $this
            ->actingAs($admin)
            ->json('POST', '/api/admin/users/', $userData + ['return_url' => 'https://escolalms.com/email/verify']);

        unset($userData['password']);
        unset($userData['roles']);

        $this->response
            ->assertCreated()
            ->assertJsonFragment($userData);

        Event::assertDispatched(Registered::class, function (Registered $event) use ($userData) {
            return $userData['email'] === $event->user->email && is_null($event->user->email_verified_at);
        });
        Event::assertDispatched(AccountRegistered::class);

        $newUser = User::where('email', $userData['email'])->first();
        $listener = app(SendEmailVerificationNotification::class);
        $listener->handle(new AccountRegistered($newUser, 'https://escolalms.com/email/verify'));

        Notification::assertSentTo($newUser, VerifyEmail::class);
    }

    public function testCreateUserWithSettingsAndGroup()
    {
        Event::fake();
        Notification::fake();

        /** @var User $admin */
        $admin = $this->makeAdmin();

        /** @var Group $group */
        $group = Group::factory()->create();

        $password = 'secret';
        $userData = User::factory()->raw([
            'roles' => [UserRole::STUDENT],
            'password' => $password,
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
        unset($userData['email_verified_at']);
        unset($userData['remember_token']);

        $this->response = $this
            ->actingAs($admin)
            ->json('POST', '/api/admin/users/', $userData + ['return_url' => 'https://escolalms.com/email/verify']);

        unset($userData['password']);
        unset($userData['roles']);
        unset($userData['groups']);
        unset($userData['settings']);

        $this->response
            ->assertCreated()
            ->assertJsonFragment($userData);

        Event::assertDispatched(Registered::class, function (Registered $event) use ($userData) {
            return $userData['email'] === $event->user->email && is_null($event->user->email_verified_at);
        });

        /** @var User $user */
        $user = User::where('email', $userData['email'])->first();

        $this->assertEquals($group->getKey(), $user->groups->get(0)->id);
        $this->assertEquals('test-setting-value', $user->settings->get(0)->value);
        $this->assertEquals('test-setting-key', $user->settings->get(0)->key);
    }

    public function testCreateUserWithAdditionalFields(): void
    {
        Event::fake();
        Notification::fake();

        /** @var User $admin */
        $admin = $this->makeAdmin();

        ModelFields::addOrUpdateMetadataField(
            User::class,
            'additional_field_a',
            'varchar',
            '',
            ['required', 'string', 'max:255']
        );

        ModelFields::addOrUpdateMetadataField(
            User::class,
            'additional_field_visibility_for_admin',
            'varchar',
            '',
            ['string', 'max:255'],
            MetaFieldVisibilityEnum::ADMIN,
        );

        $password = 'secret';
        $userData = User::factory()->raw([
            'email' => 'test@test.test',
            'roles' => [UserRole::STUDENT],
            'password' => $password,
            'phone' => '+48600600600'
        ]);

        unset($userData['email_verified_at']);
        unset($userData['remember_token']);

        $this->response = $this->actingAs($admin)
            ->json('POST', '/api/admin/users/', array_merge($userData, [
                'additional_field_visibility_for_admin' => 123,
                'return_url' => 'https://escolalms.com/email/verify',
            ]))
            ->assertStatus(422);

        $this->response->assertJsonValidationErrors([
            'additional_field_a',
            'additional_field_visibility_for_admin',
        ]);

        $this->response = $this->actingAs($admin)
            ->json('POST', '/api/admin/users/', array_merge($userData, [
                'additional_field_a' => 'string1',
                'additional_field_visibility_for_admin' => 'string2',
                'return_url' => 'https://escolalms.com/email/verify',
            ]))
            ->assertCreated()
            ->assertJsonFragment([
                'additional_field_a' => 'string1',
                'additional_field_visibility_for_admin' => 'string2',
            ]);

        $user = User::where('email', 'test@test.test')->first();
        $this->assertEquals('string1', $user->additional_field_a);
        $this->assertEquals('string2', $user->additional_field_visibility_for_admin);
    }

    public function testCreateVerifiedUser()
    {
        Event::fake();

        /** @var User $admin */
        $admin = $this->makeAdmin();

        $password = 'secret';
        $userData = User::factory()->raw([
            'roles' => [UserRole::STUDENT],
            'password' => $password
        ]);
        unset($userData['email_verified_at']);
        unset($userData['remember_token']);
        $userData['verified'] = true;

        $this->response = $this
            ->actingAs($admin)
            ->json('POST', '/api/admin/users/', $userData + ['return_url' => 'https://escolalms.com/email/verify']);

        unset($userData['password']);
        unset($userData['roles']);
        unset($userData['verified']);

        $this->response
            ->assertCreated()
            ->assertJsonFragment($userData);

        Event::assertDispatched(Registered::class, function (Registered $event) use ($userData) {
            return $userData['email'] === $event->user->email && !is_null($event->user->email_verified_at);
        });
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

    public function testPatchUserWithAdditionalFields(): void
    {
        ModelFields::addOrUpdateMetadataField(
            User::class,
            'additional_field_a',
            'varchar',
            '',
            ['required', 'string', 'max:255']
        );

        ModelFields::addOrUpdateMetadataField(
            User::class,
            'additional_field_visibility_for_admin',
            'varchar',
            '',
            ['string', 'max:255'],
            MetaFieldVisibilityEnum::ADMIN,
        );

        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $new_first_name = $user->first_name . ' new';

        $this->response = $this->actingAs($admin)->json('PATCH', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $new_first_name,
            'additional_field_a' => 'string1',
            'additional_field_visibility_for_admin' => 'string2',
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'first_name' => $new_first_name,
                'last_name' => $user->last_name,
                'additional_field_a' => 'string1',
                'additional_field_visibility_for_admin' => 'string2',
            ])
            ->assertJsonMissing([
                'first_name' => $user->first_name
            ]);

        $user->refresh();
        $this->assertEquals($user->first_name, $new_first_name);
        $this->assertEquals('string1', $user->additional_field_a);
        $this->assertEquals('string2', $user->additional_field_visibility_for_admin);
    }

    public function testPutUser()
    {
        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $new_first_name = $user->first_name . ' new';
        $new_phone = '+48600600600';

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $new_first_name,
            'last_name' => $user->last_name,
            'phone' => $new_phone
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'first_name' => $new_first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $new_phone
            ])
            ->assertJsonMissing([
                'first_name' => $user->first_name,
            ]);
    }

    public function testPutUserWithAdditionalFields(): void
    {
        ModelFields::addOrUpdateMetadataField(
            User::class,
            'additional_field_a',
            'varchar',
            '',
            ['required', 'string', 'max:255']
        );

        ModelFields::addOrUpdateMetadataField(
            User::class,
            'additional_field_visibility_for_admin',
            'varchar',
            '',
            ['string', 'max:255'],
            MetaFieldVisibilityEnum::ADMIN,
        );

        /** @var User $user */
        $user = $this->makeStudent([
            'additional_field_a' => 'string1',
            'additional_field_visibility_for_admin' => 'string2',
        ]);
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $new_first_name = $user->first_name . ' new';
        $new_phone = '+48600600600';
        $new_additional_field_a = 'new_string1';

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $new_first_name,
            'last_name' => $user->last_name,
            'phone' => $new_phone,
            'additional_field_a' => $new_additional_field_a,
        ]);

        $this->response
            ->assertOk()
            ->assertJsonFragment([
                'first_name' => $new_first_name,
                'last_name' => $user->last_name,
                'phone' => $new_phone,
                'additional_field_a' => $new_additional_field_a,
                'additional_field_visibility_for_admin' => 'string2',
            ])
            ->assertJsonMissing([
                'first_name' => $user->first_name,
            ]);
    }

    public function testPutUserAdditionalFieldRequiredValidation(): void
    {
        ModelFields::addOrUpdateMetadataField(
            User::class,
            'additional_field_short',
            'varchar',
            '',
            ['max:4']
        );

        $user = $this->makeStudent();
        $admin = $this->makeAdmin();

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'additional_field_short' => 'aabbcc',
        ])->assertJsonValidationErrors(['additional_field_short']);
    }

    public function testVerifyUser()
    {
        Event::fake();
        /** @var User $user */
        $user = $this->makeStudent([
            'email_verified_at' => null
        ]);
        $this->assertFalse($user->hasVerifiedEmail());
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $new_first_name = $user->first_name . ' new';

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $new_first_name,
            'last_name' => $user->last_name,
            'email_verified' => true,
        ]);

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        Event::assertDispatched(AccountConfirmed::class);
    }

    public function testPatchVerifyUser()
    {
        Event::fake();
        /** @var User $user */
        $user = $this->makeStudent([
            'email_verified_at' => null
        ]);
        $this->assertFalse($user->hasVerifiedEmail());
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->response = $this->actingAs($admin)->json('PATCH', '/api/admin/users/' . $user->getKey(), [
            'email_verified' => true,
        ]);

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        Event::assertDispatched(AccountConfirmed::class);
    }

    public function testUnverifyUser()
    {
        Event::fake();
        /** @var User $user */
        $user = $this->makeStudent([
            'email_verified_at' => Carbon::now()
        ]);
        $this->assertTrue($user->hasVerifiedEmail());

        /** @var User $admin */
        $admin = $this->makeAdmin();

        $new_first_name = $user->first_name . ' new';

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $new_first_name,
            'last_name' => $user->last_name,
            'email_verified' => false,
        ]);

        $user->refresh();
        $this->assertFalse($user->hasVerifiedEmail());
        Event::assertNotDispatched(AccountConfirmed::class);
    }

    public function testPatchUnverifyUser()
    {
        Event::fake();
        /** @var User $user */
        $user = $this->makeStudent([
            'email_verified_at' => Carbon::now()
        ]);
        $this->assertTrue($user->hasVerifiedEmail());

        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->response = $this->actingAs($admin)->json('PATCH', '/api/admin/users/' . $user->getKey(), [
            'email_verified' => false,
        ]);

        $user->refresh();
        $this->assertFalse($user->hasVerifiedEmail());
        Event::assertNotDispatched(AccountConfirmed::class);
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
        Storage::fake('avatars');

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
            ->assertStatus(422)
            ->assertJsonMissing([
                'email' => $user->email
            ]);

        $this->assertSoftDeleted('users', [
            'id' => $user->getKey(),
        ]);
    }

    public function testUploadAndDeleteAvatar(): void
    {
        Storage::fake('avatars');

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
        /** @var User $user */
        $user = $this->makeStudent([
            'first_name' => 'Uniquentin'
        ]);
        /** @var User $user */
        $user2 = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin([
            'first_name' => 'Uniquentin'
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users');

        $this->response->assertOk();
        $this->response->assertJsonStructure([
            'success',
            'data',
            'meta',
            'message',
        ]);

        $meta = $this->response->json('meta');

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users?per_page=' . $meta['total']);

        $this->response->assertOk();
        $this->response->assertJsonStructure([
            'success',
            'data',
            'meta',
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

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?search=Uniquentin');
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

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?search=Uniquentin&role=admin');
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

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?search=Uniquentin&role=student');
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

    public function testSearchUsersGetSpecificFields(): void
    {
        User::factory()->count(10)->create();

        $admin = $this->makeAdmin();
        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users?fields[]=first_name&fields[]=email');

        $this->response->assertJsonStructure([
            'data' => [[
                'first_name',
                'email',
            ]]
        ]);

        $data = $this->response->getData()->data;
        $this->assertFalse(property_exists($data[0], 'last_name'));
        $this->assertTrue(property_exists($data[0], 'first_name'));
        $this->assertTrue(property_exists($data[0], 'email'));
    }

    public function testSearchUsersGetSpecificFieldsWithRelations(): void
    {
        User::factory()
            ->has(Category::factory(), 'interests')
            ->count(10)->create();

        $admin = $this->makeAdmin();
        $this->response = $this
            ->actingAs($admin)
            ->json('GET', '/api/admin/users?fields[]=first_name&relations[]=interests');

        $this->response->assertJsonStructure([
            'data' => [[
                'first_name',
                'interests' => [[
                    'id',
                    'name',
                    'slug'
                ]]
            ]]
        ]);


        $data = $this->response->getData()->data;
        $this->assertFalse(property_exists($data[0], 'last_name'));
        $this->assertTrue(property_exists($data[0], 'first_name'));

        $this->assertTrue(property_exists($data[0], 'interests'));
        $this->assertFalse(property_exists($data[0], 'roles'));
        $this->assertFalse(property_exists($data[0], 'permissions'));
    }

    public function testSearchUsersGetSpecificFieldsWithAdditionalFields(): void
    {
        ModelFields::addOrUpdateMetadataField(
            User::class,
            'varchar_additional_field',
            'varchar',
        );

        User::factory()->count(10)->create([
            'varchar_additional_field' => 'string1'
        ]);
        $admin = $this->makeAdmin([
            'varchar_additional_field' => 'string1'
        ]);

        $this->response = $this
            ->actingAs($admin)
            ->json('GET', '/api/admin/users?fields[]=first_name&fields[]=email&fields[]=varchar_additional_field');

        $this->response->assertJsonStructure([
            'data' => [[
                'first_name',
                'email',
                'varchar_additional_field',
            ]]
        ]);

        $data = $this->response->getData()->data;

        $this->assertFalse(property_exists($data[0], 'last_name'));
        $this->assertTrue(property_exists($data[0], 'first_name'));
        $this->assertTrue(property_exists($data[0], 'varchar_additional_field'));
    }

    public function testSearchUsersWithAdditionalFields(): void
    {
        ModelFields::addOrUpdateMetadataField(
            User::class,
            'varchar_additional_field',
            'varchar',
        );

        ModelFields::addOrUpdateMetadataField(
            User::class,
            'boolean_additional_field',
            'boolean',
        );

        ModelFields::addOrUpdateMetadataField(
            User::class,
            'number_additional_field',
            'number',
        );

        $user = $this->makeStudent([
            'first_name' => 'Uniquentin',
            'varchar_additional_field' => 'string1',
            'boolean_additional_field' => true,
            'number_additional_field' => 1234,
        ]);

        $user2 = $this->makeStudent();

        $admin = $this->makeAdmin([
            'first_name' => 'Uniquentin',
            'varchar_additional_field' => 'string2',
            'boolean_additional_field' => false,
            'number_additional_field' => 12345,
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users?varchar_additional_field=string');
        $this->response->assertOk();
        $this->response->assertJsonMissing([
            'email' => $user2->email
        ]);
        $this->response->assertJsonFragment([
            'email' => $admin->email
        ]);
        $this->response->assertJsonFragment([
            'email' => $user->email
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?boolean_additional_field=1');
        $this->response->assertOk();
        $this->response->assertJsonMissing([
            'email' => $user2->email
        ]);
        $this->response->assertJsonMissing([
            'email' => $admin->email
        ]);
        $this->response->assertJsonFragment([
            'email' => $user->email
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?boolean_additional_field=0');
        $this->response->assertOk();
        $this->response->assertJsonMissing([
            'email' => $user2->email
        ]);
        $this->response->assertJsonMissing([
            'email' => $user->email
        ]);
        $this->response->assertJsonFragment([
            'email' => $admin->email
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users/?number_additional_field=1234');
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

    public function testDeleteUserDispatchEvent()
    {
        Event::fake(AccountDeleted::class);
        Notification::fake();

        Storage::fake('avatars');

        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->response = $this->actingAs($admin)->json('DELETE', '/api/admin/users/' . $user->getKey());
        $this->response
            ->assertStatus(200);

        Event::assertDispatched(AccountDeleted::class,
            function (AccountDeleted $event) use ($user) {
                $this->assertEquals($user->email, $event->getUser()->email);
                return true;
            });
    }

    public function testBlockedUserDispatchEvent()
    {
        Event::fake(AccountBlocked::class);
        Notification::fake();

        /** @var User $user */
        $user = $this->makeStudent([
            'is_active' => false,
        ]);
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'is_active' => true,
        ]);

        Event::assertNotDispatched(AccountBlocked::class);
        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'is_active' => true,
        ]);

        $this->response = $this->actingAs($admin)->json('PUT', '/api/admin/users/' . $user->getKey(), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'is_active' => false,
        ]);

        Event::assertDispatched(AccountBlocked::class,
            function (AccountBlocked $event) use ($user) {
                $this->assertEquals($user->email, $event->getUser()->email);
                return true;
            });
    }

    public function testUploadAvatarFileAsPath(): void
    {
        Storage::fake('local');

        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->assertEmpty($user->path_avatar);

        $avatarPath = "avatars/{$user->getKey()}/avatar.jpg";

        Storage::makeDirectory("avatars/{$user->getKey()}");
        copy(__DIR__ . '/../../mocks/avatar.jpg', Storage::path($avatarPath));

        $this->response = $this->actingAs($admin)->postJson('/api/admin/users/' . $user->getKey() . '/avatar', [
            'avatar' => $avatarPath
        ])->assertOk();

        $user->refresh();
        $this->assertNotEmpty($user->path_avatar);
        Storage::exists($user->path_avatar);
        $this->assertEquals($avatarPath, $user->path_avatar);
    }

    public function testUploadAvatarFileAsWrongPath(): void
    {
        Storage::fake('local');

        /** @var User $user */
        $user = $this->makeStudent();
        /** @var User $admin */
        $admin = $this->makeAdmin();

        $this->assertEmpty($user->path_avatar);

        $avatarPath = "avatars/{$admin->getKey()}/avatar.jpg";

        Storage::makeDirectory("avatars/{$admin->getKey()}");
        copy(__DIR__ . '/../../mocks/avatar.jpg', Storage::path($avatarPath));

        $this->response = $this->actingAs($admin)->postJson('/api/admin/users/' . $user->getKey() . '/avatar', [
            'avatar' => $avatarPath
        ])->assertStatus(422);

        $user->refresh();
        $this->assertEmpty($user->path_avatar);
        Storage::exists($avatarPath);
    }

    public function testSearchByLastLoginUsers(): void
    {
        /** @var User $admin */
        $admin = $this->makeAdmin([
            'first_name' => 'Uniquentin'
        ]);

        $this->response = $this->actingAs($admin)->json('GET', '/api/admin/users?gt_last_login_day=' . 7);

        $this->response->assertOk();
        $this->response->assertJsonStructure([
            'success',
            'data',
            'meta',
            'message',
        ]);
    }

    public function testUserAccountDeletionTriggersSocialAccountsRemoval(): void
    {
        Notification::fake();

        /** @var User $user */
        $user = $this->makeStudent();

        SocialAccount::factory()->state(['user_id' => $user->getKey()])->create();

        $this->response = $this->actingAs($this->makeAdmin())
            ->deleteJson('/api/admin/users/' . $user->getKey())
            ->assertStatus(200);

        $this->assertSoftDeleted('users', [
            'id' => $user->getKey(),
        ]);

        $this->assertDatabaseMissing('social_accounts', [
            'user_id' => $user->getKey(),
        ]);
    }
}
