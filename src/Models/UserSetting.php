<?php

namespace EscolaLms\Auth\Models;

use EscolaLms\Auth\Models\Traits\HasCompositePrimaryKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function getTraitOwner(): self
    {
        return $this;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
