<?php

namespace EscolaLms\Auth\Models;

use Database\Factories\EscolaLms\Auth\Models\SocialAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EscolaLms\Auth\Models\SocialAccount
 *
 * @property string $user_id
 * @property string $provider
 * @property string $provider_id
 * @property-read User $user
 */
class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): SocialAccountFactory
    {
        return SocialAccountFactory::new();
    }
}
