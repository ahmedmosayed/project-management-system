<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planning = 'planning';
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planning => __('Planning'),
            self::Active => __('Active'),
            self::OnHold => __('On hold'),
            self::Completed => __('Completed'),
            self::Closed => __('Closed'),
            self::Cancelled => __('Cancelled'),
        };
    }
}
