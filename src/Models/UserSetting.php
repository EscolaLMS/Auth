<?php

namespace EscolaLms\Auth\Models;

use EscolaLms\Auth\Casts\UserSettingValueCast;
use EscolaLms\Auth\Models\Traits\HasCompositePrimaryKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $key
 * @property string $value
 */
class UserSetting extends Model
{
    use HasFactory;
    use HasCompositePrimaryKeyTrait;

    protected $primaryKey = ['user_id', 'key'];
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => UserSettingValueCast::class
    ];

    public function getTraitOwner(): self
    {
        return $this;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
