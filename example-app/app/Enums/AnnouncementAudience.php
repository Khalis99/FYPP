<?php

namespace App\Enums;

enum AnnouncementAudience: string
{
    case All = 'all';
    case Teachers = 'teachers';
    case Admins = 'admins';

    public function label(): string
    {
        return match ($this) {
            self::All => __('Everyone'),
            self::Teachers => __('Teachers only'),
            self::Admins => __('Administrators only'),
        };
    }

    /**
     * The audiences that a user with the given role is allowed to see.
     *
     * @return list<self>
     */
    public static function visibleTo(UserRole $role): array
    {
        return match ($role) {
            UserRole::Teacher => [self::All, self::Teachers],
            UserRole::Admin => [self::All, self::Teachers, self::Admins],
        };
    }
}
