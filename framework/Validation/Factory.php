<?php

declare(strict_types=1);

namespace Framework\Validation;

use Framework\Database\Connection;
use Framework\Translation\Translator;

final readonly class Factory
{
    public function __construct(private Translator $translator, private Connection $db)
    {
    }

    public function make(array $data, array $rules): Validator
    {
        return new Validator($data, $rules, $this->translator, $this->db);
    }
}
