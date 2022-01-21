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
                    'additional_fields' => [
                        'full_key' => 'escola_auth.additional_fields',
                        'key' => 'additional_fields',
                        'rules' => [
                            'required',
                            'array'
                        ],
                        'value' => [],
                        'readonly' => false,
                        'public' => true,
                    ],
                    'additional_fields_required' => [
                        'full_key' => 'escola_auth.additional_fields_required',
                        'key' => 'additional_fields_required',
                        'rules' => [
                            'required',
                            'array',
                            []
                        ],
                        'value' => [],
                        'readonly' => false,
                        'public' => true,
                    ],
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
                        'public' => false,
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

        $this->response = $this->json(
            'GET',
            '/api/config'
        );
        $this->response->assertJsonMissing([
            'escola_auth' => [
                'additional_fields' => [
                    'additional_field_a',
                    'additional_field_b'
                ],
                'additional_fields_required' => [
                    'additional_field_b'
                ],
            ]
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/config',
            [
                'config' => [
                    [
                        'key' => 'escola_auth.additional_fields',
                        'value' => [
                            'additional_field_a',
                        ]
                    ],
                    [
                        'key' => 'escola_auth.additional_fields_required',
                        'value' => [

                            'additional_field_b',
                        ]
                    ],
                    [
                        'key' => 'escola_auth.registration',
                        'value' => SettingStatusEnum::ENABLED,
                    ],
                    [
                        'key' => 'escola_auth.account_must_be_enabled_by_admin',
                        'value' => SettingStatusEnum::ENABLED,
                    ]
                ]
            ]
        );
        $this->response->assertStatus(422);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/config',
            [
                'config' => [
                    [
                        'key' => 'escola_auth.additional_fields',
                        'value' => [
                            'additional_field_a',
                            'additional_field_b',
                        ]
                    ],
                    [
                        'key' => 'escola_auth.additional_fields_required',
                        'value' => [

                            'additional_field_b',
                        ]
                    ],
                    [
                        'key' => 'escola_auth.registration',
                        'value' => SettingStatusEnum::DISABLED,
                    ],
                    [
                        'key' => 'escola_auth.account_must_be_enabled_by_admin',
                        'value' => SettingStatusEnum::ENABLED,
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
                'additional_fields' => ['additional_field_a', 'additional_field_b'],
                'additional_fields_required' => ['additional_field_b'],
                'registration' => SettingStatusEnum::DISABLED,
            ]
        ]);

        $this->response->assertJsonMissing([
            'account_must_be_enabled_by_admin' => SettingStatusEnum::ENABLED,
        ]);
    }
}
