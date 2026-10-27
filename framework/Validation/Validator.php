<?php

declare(strict_types=1);

namespace Framework\Validation;

use Framework\Database\Connection;
use Framework\Translation\Translator;
use InvalidArgumentException;
use LogicException;

/**
 * Rules are written as "required|string|min:2|max:55" or as an array of such strings.
 *
 * Available rules: required, nullable, string, integer, numeric, boolean, email, date (Y-m-d), min, max,
 * in, alpha_dash, confirmed, unique:table,column[,ignoreId], exists:table,column
 *
 * Empty optional fields are returned as null, so they can be stored in nullable columns directly.
 */
final class Validator
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    private bool $ran = false;

    public function __construct(
        private readonly array $data,
        private readonly array $rules,
        private readonly Translator $translator,
        private readonly ?Connection $db = null,
    ) {
    }

    public function passes(): bool
    {
        if (!$this->ran) {
            foreach ($this->rules as $field => $rules) {
                $this->validateField($field, is_string($rules) ? explode('|', $rules) : $rules);
            }

            $this->ran = true;
        }

        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        $this->passes();

        return $this->errors;
    }

    /**
     * Validated input, limited to the fields that have rules.
     *
     * @throws ValidationException
     */
    public function validate(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this->errors, $this->translator->get('validation.failed'));
        }

        $validated = [];

        foreach (array_keys($this->rules) as $field) {
            if (array_key_exists($field, $this->data)) {
                $value = is_string($this->data[$field]) ? trim($this->data[$field]) : $this->data[$field];
                $validated[$field] = $value === '' ? null : $value;
            }
        }

        return $validated;
    }

    private function validateField(string $field, array $rules): void
    {
        $value = $this->data[$field] ?? null;
        $value = is_string($value) ? trim($value) : $value;
        $isEmpty = $value === null || $value === '' || $value === [];
        $ruleNames = array_map(static fn (string $rule) => explode(':', $rule, 2)[0], $rules);

        // Optional fields that were left empty skip every other rule.
        if ($isEmpty && !in_array('required', $ruleNames, true)) {
            return;
        }

        $numeric = array_intersect($ruleNames, ['integer', 'numeric']) !== [];

        foreach ($rules as $rule) {
            [$name, $argument] = array_pad(explode(':', $rule, 2), 2, '');
            $parameters = $argument === '' ? [] : explode(',', $argument);

            if (!$this->check($name, $field, $value, $parameters, $numeric)) {
                $this->errors[$field][] = $this->message($name, $field, $parameters, $numeric);

                return;
            }
        }
    }

    private function check(string $rule, string $field, mixed $value, array $parameters, bool $numeric): bool
    {
        return match ($rule) {
            'required' => $value !== null && $value !== '' && $value !== [],
            'nullable' => true,
            'string' => is_string($value),
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'numeric' => is_numeric($value),
            'boolean' => in_array($value, [true, false, 0, 1, '0', '1', 'on', 'off'], true),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'date' => is_string($value) && self::isDate($value),
            'min' => $this->size($value, $numeric) >= (float) $parameters[0],
            'max' => $this->size($value, $numeric) <= (float) $parameters[0],
            'in' => in_array((string) $value, $parameters, true),
            'alpha_dash' => is_string($value) && preg_match('/^[\pL\pM\pN_-]+$/u', $value) === 1,
            'confirmed' => $value === ($this->data[$field . '_confirmation'] ?? null),
            'unique' => $this->countRows($parameters, $field, $value, ignore: $parameters[2] ?? null) === 0,
            'exists' => $this->countRows($parameters, $field, $value) > 0,
            default => throw new InvalidArgumentException("Unknown validation rule [$rule]."),
        };
    }

    /**
     * A real calendar date in Y-m-d format ("2026-02-30" is rejected).
     */
    public static function isDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function size(mixed $value, bool $numeric): float
    {
        return match (true) {
            $numeric => (float) $value,
            is_array($value) => count($value),
            default => mb_strlen((string) $value),
        };
    }

    private function countRows(array $parameters, string $field, mixed $value, ?string $ignore = null): int
    {
        if ($this->db === null) {
            throw new LogicException('The unique and exists rules need a database connection.');
        }

        $query = $this->db->table($parameters[0])->where($parameters[1] ?? $field, $value);

        if ($ignore !== null && $ignore !== '') {
            $query->where('id', '!=', $ignore);
        }

        return $query->count();
    }

    private function message(string $rule, string $field, array $parameters, bool $numeric): string
    {
        $key = in_array($rule, ['min', 'max'], true)
            ? "validation.$rule." . ($numeric ? 'numeric' : 'string')
            : "validation.$rule";

        $attribute = $this->translator->has("attributes.$field")
            ? $this->translator->get("attributes.$field")
            : str_replace('_', ' ', $field);

        return $this->translator->get($key, [
            'attribute' => $attribute,
            'min' => $parameters[0] ?? '',
            'max' => $parameters[0] ?? '',
            'values' => implode(', ', $parameters),
        ]);
    }
}
