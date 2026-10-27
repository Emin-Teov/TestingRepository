<?php

declare(strict_types=1);

namespace Framework\Console;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class AsCommand
{
    public function __construct(
        public string $name,
        public string $description = '',
        public string $usage = '',
    ) {
    }
}
