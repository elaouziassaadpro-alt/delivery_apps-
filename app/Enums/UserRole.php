<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Driver = 'driver';

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Driver => 'driver.dashboard',
            self::Admin, self::Manager => 'admin.dashboard',
        };
    }
}