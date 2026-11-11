<?php

namespace App\Enums;

/** How a finished call went — set when a call is marked done or missed. */
enum CallOutcome: string
{
    case Talked = 'talked';
    case Interested = 'interested';
    case CallBack = 'call_back';
    case NoAnswer = 'no_answer';
    case Busy = 'busy';
    case NotInterested = 'not_interested';
    case WrongNumber = 'wrong_number';

    public function label(): string
    {
        return match ($this) {
            self::Talked => 'Talked',
            self::Interested => 'Interested',
            self::CallBack => 'Call back later',
            self::NoAnswer => 'No answer',
            self::Busy => 'Busy / switched off',
            self::NotInterested => 'Not interested',
            self::WrongNumber => 'Wrong number',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Talked, self::Interested => 'green',
            self::CallBack => 'blue',
            self::NoAnswer, self::Busy => 'yellow',
            self::NotInterested, self::WrongNumber => 'red',
        };
    }

    /** Outcomes that mean nobody was reached — the call counts as missed. */
    public function reached(): bool
    {
        return ! in_array($this, [self::NoAnswer, self::Busy, self::WrongNumber], true);
    }

    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $c) => $c->value, self::cases()),
            array_map(fn (self $c) => $c->label(), self::cases()),
        );
    }
}
