<?php

namespace EscolaLms\Auth\Models;

use Database\Factories\EscolaLms\Auth\Models\PreUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @OA\Schema(
 *      schema="pre-user",
 *      required={"email", "first_name", "last_name"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="email",
 *          description="email",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="first_name",
 *          description="first_name",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="last_name",
 *          description="last_name",
 *          type="string"
 *      ),
 * )
 *
 */

/**
 * EscolaLms\Auth\Models\PreUser
 *
 * @property string $first_name
 * @property string $last_name
 * @property string $provider
 * @property string $provider_id
 * @property string $token
 * @property ?Carbon $created_at
 */
class PreUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'provider',
        'provider_id',
        'token',
    ];

    protected $casts = [
        'first_name' => 'string',
        'last_name' => 'string',
        'provider' => 'string',
        'provider_id' => 'string',
        'token' => 'string',
    ];

    protected static function newFactory(): PreUserFactory
    {
        return PreUserFactory::new();
    }
}
