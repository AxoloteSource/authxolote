<?php

namespace App\Models;

use App\Enums\OtpChannel;
use App\Enums\OtpDeliveryStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OtpDelivery extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'otp_challenge_id',
        'channel',
        'destination',
        'status',
        'provider',
        'sent_at',
        'failed_at',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'channel' => OtpChannel::class,
            'status' => OtpDeliveryStatus::class,
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(OtpChallenge::class, 'otp_challenge_id');
    }

    public function markAsSent(?string $provider = null): void
    {
        $this->status = OtpDeliveryStatus::Sent;
        $this->provider = $provider;
        $this->sent_at = now();
        $this->failed_at = null;
        $this->error = null;
        $this->save();
    }

    public function markAsFailed(string $error): void
    {
        $this->status = OtpDeliveryStatus::Failed;
        $this->failed_at = now();
        $this->error = Str::limit($error, 500, '');
        $this->save();
    }
}
