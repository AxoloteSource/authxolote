<?php

namespace App\Enums;

enum MenuSize: string
{
    case Sm = 'sm';
    case Md = 'md';
    case Lg = 'lg';

    public function isSmall(): bool
    {
        return $this === self::Sm;
    }

    public function isMedium(): bool
    {
        return $this === self::Md;
    }

    public function isLarge(): bool
    {
        return $this === self::Lg;
    }
}
