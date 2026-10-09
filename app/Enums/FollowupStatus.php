<?php

namespace App\Enums;

enum FollowupStatus: string
{
    case Pending = 'pending';
    case Done = 'done';
    case Missed = 'missed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Done => 'Done',
            self::Missed => 'Missed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Done => 'green',
            self::Missed => 'red',
        };
    }
}
