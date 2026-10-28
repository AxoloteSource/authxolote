<?php

namespace App\Enums;

enum OtpChannel: string
{
    case Email = 'email';
    case Whatsapp = 'whatsapp';

    public function isEmail(): bool
    {
        return $this === self::Email;
    }

    public function isWhatsapp(): bool
    {
        return $this === self::Whatsapp;
    }
}
