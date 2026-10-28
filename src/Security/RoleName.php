<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Noms des rôles utilisés dans les attributs #[IsGranted], pour ne pas les
 * écrire en chaînes littérales (règle symplify RequireIsGrantedEnumRule).
 */
final class RoleName
{
    public const TECHNICAL = 'ROLE_TECHNICAL';
}
