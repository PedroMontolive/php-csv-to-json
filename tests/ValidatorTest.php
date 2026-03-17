<?php

namespace Tests;

use Emanuel\PhpCsvToJson\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    #[Test]
    public function it_accepts_rows_with_all_required_fields_present(): void
    {
        $validator = new Validator(['name', 'price']);
        $rows      = [['name' => 'Widget', 'price' => '29.90', 'sku' => 'WGT-001']];

        $valid = $validator->validate($rows);

        $this->assertCount(1, $valid);
        $this->assertEmpty($validator->getErrors());
    }

    #[Test]
    public function it_rejects_rows_with_a_missing_required_field(): void
    {
        $validator = new Validator(['name', 'price']);
        $rows      = [['name' => 'Widget', 'price' => '', 'sku' => 'WGT-001']];

        $valid  = $validator->validate($rows);
        $errors = $validator->getErrors();

        $this->assertEmpty($valid);
        $this->assertCount(1, $errors);
        $this->assertSame(['price'], $errors[0]['missing_fields']);
    }

    #[Test]
    public function it_treats_whitespace_only_values_as_missing(): void
    {
        $validator = new Validator(['name']);
        $rows      = [['name' => '   ']];

        $validator->validate($rows);

        $this->assertCount(1, $validator->getErrors());
        $this->assertSame(['name'], $validator->getErrors()[0]['missing_fields']);
    }

    #[Test]
    public function it_reports_multiple_missing_fields_in_one_error(): void
    {
        $validator = new Validator(['name', 'price', 'sku']);
        $rows      = [['name' => '', 'price' => '', 'sku' => 'WGT-001']];

        $validator->validate($rows);
        $errors = $validator->getErrors();

        $this->assertEqualsCanonicalizing(['name', 'price'], $errors[0]['missing_fields']);
    }

    #[Test]
    public function it_assigns_correct_line_number_in_errors(): void
    {
        $validator = new Validator(['name']);
        $rows      = [
            ['name' => 'Valid'],
            ['name' => ''],
        ];

        $validator->validate($rows);
        $errors = $validator->getErrors();

        // header = line 1, first data row = line 2, second = line 3
        $this->assertSame(3, $errors[0]['line']);
    }

    #[Test]
    public function it_separates_valid_and_invalid_rows(): void
    {
        $validator = new Validator(['email']);
        $rows      = [
            ['email' => 'a@example.com'],
            ['email' => ''],
            ['email' => 'b@example.com'],
        ];

        $valid = $validator->validate($rows);

        $this->assertCount(2, $valid);
        $this->assertCount(1, $validator->getErrors());
    }

    #[Test]
    public function it_throws_when_no_required_fields_are_provided(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Validator([]);
    }
}
