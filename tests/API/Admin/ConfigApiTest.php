<?php

namespace Tests\API\Admin;

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
                        'rules' => [
                            'required',
                            'array'
                        ],
                        'value' => [],
                        'readonly' => false,
                        'public' => true,
                    ],
                    'additional_fields_required' => [
                        'rules' => [
                            'required',
                            'array',
                            []
                        ],
                        'value' => [],
                        'readonly' => false,
                        'public' => true,
                    ],
                    'registration_enabled' => [
                        'rules' => [
                            'required',
                            'boolean',
                        ],
                        'value' => true,
                        'readonly' => false,
                        'public' => false,
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
                        'key' => 'escola_auth.registration_enabled',
                        'value' => false,
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
                'additional_fields_required' => ['additional_field_b']
            ]
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/config'
        );
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'registration_enabled' => [
                'rules' => [
                    'required',
                    'boolean',
                ],
                'value' => false,
                'readonly' => false,
                'public' => false,
            ],
        ]);
    }
}
