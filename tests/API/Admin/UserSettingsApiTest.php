<?php

namespace EscolaLms\Auth\Tests\API\Admin;

use EscolaLms\Auth\Tests\TestCase;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class UserSettingsApiTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;


}
