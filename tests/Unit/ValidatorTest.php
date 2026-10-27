<?php

declare(strict_types=1);

namespace Tests\Unit;

use Framework\Translation\Translator;
use Framework\Validation\ValidationException;
use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    private function validator(array $data, array $rules): Validator
    {
        return new Validator($data, $rules, new Translator(dirname(__DIR__, 2) . '/lang', 'en'));
    }

    public function test_valid_data_returns_only_ruled_fields_trimmed(): void
    {
        $validated = $this->validator(
            ['value' => '  Hello ', 'description' => 'World', 'is_admin' => '1'],
            ['value' => 'required|string|min:2|max:55', 'description' => 'required|string'],
        )->validate();

        $this->assertSame(['value' => 'Hello', 'description' => 'World'], $validated);
    }

    public function test_messages_use_translated_attribute_names(): void
    {
        $errors = $this->validator(
            ['value' => 'a', 'description' => str_repeat('x', 300), 'email' => 'nope'],
            ['value' => 'required|min:2', 'description' => 'max:255', 'email' => 'email', 'missing' => 'required'],
        )->errors();

        $this->assertSame('The minimum length for value is 2.', $errors['value'][0]);
        $this->assertSame('The maximum length for description is 255.', $errors['description'][0]);
        $this->assertSame('This is not an email address.', $errors['email'][0]);
        $this->assertSame('The missing field is required.', $errors['missing'][0]);
    }

    public function test_optional_empty_fields_skip_other_rules(): void
    {
        $this->assertTrue($this->validator(['password' => ''], ['password' => 'nullable|min:8|confirmed'])->passes());
    }

    public function test_numeric_rules_compare_values_and_confirmed_works(): void
    {
        $validator = $this->validator(
            ['age' => '17', 'password' => 'secret123', 'password_confirmation' => 'other', 'role' => 'owner'],
            ['age' => 'integer|min:18', 'password' => 'confirmed', 'role' => 'in:admin,editor'],
        );

        $this->assertSame(['age', 'password', 'role'], array_keys($validator->errors()));
    }

    public function test_date_rule_accepts_only_real_dates(): void
    {
        $errors = $this->validator(
            ['a' => '2026-10-27', 'b' => '2026-02-30', 'c' => '27.10.2026', 'd' => '2024-02-29'],
            ['a' => 'date', 'b' => 'date', 'c' => 'date', 'd' => 'date'],
        )->errors();

        $this->assertSame(['b', 'c'], array_keys($errors));
    }

    public function test_empty_optional_fields_are_returned_as_null(): void
    {
        $validated = $this->validator(['note' => '   ', 'name' => 'x'], ['note' => 'nullable|string', 'name' => 'required'])->validate();

        $this->assertSame(['note' => null, 'name' => 'x'], $validated);
    }

    public function test_validate_throws_with_errors(): void
    {
        try {
            $this->validator([], ['value' => 'required'])->validate();
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('value', $exception->errors());
        }
    }
}
