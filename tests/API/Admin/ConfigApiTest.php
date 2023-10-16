<?php

namespace Tests\API\Admin;

use EscolaLms\Auth\Enums\SettingStatusEnum;
use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Settings\Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;

class ConfigApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(\EscolaLms\Settings\EscolaLmsSettingsServiceProvider::class)) {
            $this->markTestSkipped();
        }

        $this->seed(PermissionTableSeeder::class);

        Config::set('escola_settings.use_database', true);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('admin');
    }

    public function testAdministrableConfigApi()
    {
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/config'
        );

        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'data' => [
                'escola_auth' => [
                    'registration' => [
                        'full_key' => 'escola_auth.registration',
                        'key' => 'registration',
                        'rules' => [
                            'required',
                            'string',
                            'in:' . implode(',', SettingStatusEnum::getValues())],
                        'value' => SettingStatusEnum::ENABLED,
                        'readonly' => false,
                        'public' => true,
                    ],
                    'account_must_be_enabled_by_admin' => [
                        'full_key' => 'escola_auth.account_must_be_enabled_by_admin',
                        'key' => 'account_must_be_enabled_by_admin',
                        'rules' => [
                            'required',
                            'string',
                            'in:' . implode(',', SettingStatusEnum::getValues()),
                        ],
                        'value' => SettingStatusEnum::DISABLED,
                        'readonly' => false,
                        'public' => true,
                    ],
                    'auto_verified_email' => [
                        'full_key' => 'escola_auth.auto_verified_email',
                        'key' => 'auto_verified_email',
                        'rules' => [
                            'required',
                            'string',
                            'in:' . implode(',', SettingStatusEnum::getValues()),
                        ],
                        'value' => SettingStatusEnum::DISABLED,
                        'readonly' => false,
                        'public' => true,
                    ],
                    'return_url' => [
                        'full_key' => 'escola_auth.return_url',
                        'key' => 'return_url',
                        'rules' => [
                            'required',
                            'url',
                        ],
                        'value' => null,
                        'readonly' => false,
                        'public' => true,
                    ],
                    'socialite_remember_me' => [
                        'full_key' => 'escola_auth.socialite_remember_me',
                        'key' => 'socialite_remember_me',
                        'rules' => [
                            'required',
                            'boolean',
                        ],
                        'value' => false,
                        'readonly' => false,
                        'public' => true,
                    ],
                ],
                'services' => [
                    'facebook' => [
                        'client_id' => [
                            'full_key' => 'services.facebook.client_id',
                            'key' => 'facebook.client_id',
                            'rules' => [
                                'required',
                                'string',
                            ],
                            'value' => null,
                            'readonly' => false,
                            'public' => false,
                        ], 'client_secret' => [
                            'full_key' => 'services.facebook.client_secret',
                            'key' => 'facebook.client_secret',
                            'rules' => [
                                'required',
                                'string',
                            ],
                            'value' => null,
                            'readonly' => false,
                            'public' => false,
                        ],
                        'redirect' => [
                            'full_key' => 'services.facebook.redirect',
                            'key' => 'facebook.redirect',
                            'rules' => [
                                'required',
                                'url',
                            ],
                            'value' => null,
                            'readonly' => false,
                            'public' => false,
                        ]
                    ],
                    'google' => [
                        'client_id' => [
                            'full_key' => 'services.google.client_id',
                            'key' => 'google.client_id',
                            'rules' => [
                                'required',
                                'string',
                            ],
                            'value' => null,
                            'readonly' => false,
                            'public' => false,
                        ], 'client_secret' => [
                            'full_key' => 'services.google.client_secret',
                            'key' => 'google.client_secret',
                            'rules' => [
                                'required',
                                'string',
                            ],
                            'value' => null,
                            'readonly' => false,
                            'public' => false,
                        ],
                        'redirect' => [
                            'full_key' => 'services.google.redirect',
                            'key' => 'google.redirect',
                            'rules' => [
                                'required',
                                'url',
                            ],
                            'value' => null,
                            'readonly' => false,
                            'public' => false,
                        ]
                    ],
                ]
            ]
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/config',
            [
                'config' => [
                    [
                        'key' => 'escola_auth.registration',
                        'value' => SettingStatusEnum::DISABLED,
                    ],
                    [
                        'key' => 'escola_auth.account_must_be_enabled_by_admin',
                        'value' => SettingStatusEnum::ENABLED,
                    ],
                    [
                        'key' => 'escola_auth.auto_verified_email',
                        'value' => SettingStatusEnum::ENABLED,
                    ],
                    [
                        'key' => 'escola_auth.return_url',
                        'value' => 'https://example.com',
                    ],
                    [
                        'key' => 'escola_auth.socialite_remember_me',
                        'value' => true,
                    ],
                ]
            ]
        );
        $this->response->assertOk();

        $this->response = $this->json(
            'GET',
            '/api/config'
        );
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'escola_auth' => [
                'registration' => SettingStatusEnum::DISABLED,
                'account_must_be_enabled_by_admin' => SettingStatusEnum::ENABLED,
                'auto_verified_email' => SettingStatusEnum::ENABLED,
                'return_url' => 'https://example.com',
                'socialite_remember_me' => true,
            ]
        ]);
    }
}
