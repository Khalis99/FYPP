<?php

namespace App\Enums;

use Carbon\CarbonInterface;

enum Weekday: int
{
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;

    public function label(): string
    {
        return __($this->name);
    }

    /**
     * The school weekday for a date, or null on weekends.
     */
    public static function fromDate(CarbonInterface $date): ?self
    {
        return self::tryFrom($date->dayOfWeekIso);
    }
}
