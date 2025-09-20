<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Roles: string implements HasLabel, HasColor
{
    case Admin = 'admin';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Admin => 'Admin',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Admin => 'success',
        };
    }
}
