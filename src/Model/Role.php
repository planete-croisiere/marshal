<?php

declare(strict_types=1);

namespace App\Model;

enum Role: string {
    case ROLE_SUPER_ADMIN = 'Super Administrator';
    case ROLE_ADMIN = 'Administrator';
}
