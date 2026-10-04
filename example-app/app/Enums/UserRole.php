<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Teacher => 'Teacher',
        };
    }

    /**
     * The named route a user with this role lands on after logging in.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Teacher => 'teacher.dashboard',
        };
    }
}
