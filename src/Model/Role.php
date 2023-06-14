<?php

declare(strict_types=1);

namespace App\Model;

enum Role: string {
    case ROLE_SUPER_ADMIN = 'Super Administrator';
    case ROLE_ADMIN = 'Administrator';

    case ROLE_CAN_SWITCH_USER = 'Impersonator';
    case ROLE_CRM_SELLER = 'Seller';
    case ROLE_CRM_PARTNER = 'Partner';
    case ROLE_CRM_BACKOFFICE = 'Backoffice';
    case ROLE_CATALOG = 'Catalog';
}
