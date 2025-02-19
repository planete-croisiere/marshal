<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Inspect extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('inspect', [$this, 'inspect']),
        ];
    }

    public function inspect(mixed $value): string
    {
        // We do this because dump() filter is disabled with APP_DEBUG=false
        return print_r($value, true);
    }
}
