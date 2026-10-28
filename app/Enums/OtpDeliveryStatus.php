<?php

namespace App\Enums;

enum OtpDeliveryStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isSent(): bool
    {
        return $this === self::Sent;
    }

    public function isFailed(): bool
    {
        return $this === self::Failed;
    }
}
