<?php

namespace App\Models;

use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtpChallenge extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'purpose',
        'channel',
        'destination',
        'code_hash',
        'expires_at',
        'verified_at',
        'attempts',
        'max_attempts',
    ];

    protected function casts(): array
    {
        return [
            'purpose' => OtpPurpose::class,
            'channel' => OtpChannel::class,
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(OtpDelivery::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return ! is_null($this->verified_at);
    }

    public function hasReachedMaxAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    public function isActive(): bool
    {
        return ! $this->isVerified() && ! $this->isExpired() && ! $this->hasReachedMaxAttempts();
    }
}
