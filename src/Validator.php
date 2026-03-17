<?php

namespace Emanuel\PhpCsvToJson;

use InvalidArgumentException;

class Validator
{
    /** @var array<int, string> */
    private array $requiredFields;

    /** @var array<int, array{line: int, row: array<string, string>, missing_fields: array<int, string>}> */
    private array $errors = [];

    /**
     * @param array<int, string> $requiredFields
     * @throws InvalidArgumentException
     */
    public function __construct(array $requiredFields)
    {
        if (empty($requiredFields)) {
            throw new InvalidArgumentException('At least one required field must be provided.');
        }

        $this->requiredFields = $requiredFields;
    }

    /**
     *
     * @param array<int, array<string, string>> $rows
     * @return array<int, array<string, string>>
     */
    public function validate(array $rows): array
    {
        $this->errors = [];

        $validRows = [];

        foreach ($rows as $index => $row) {
            $missingFields = array_values(
                array_filter(
                    $this->requiredFields,
                    fn (string $field) => trim($row[$field] ?? '') === ''
                )
            );

            if (empty($missingFields)) {
                $validRows[] = $row;
                continue;
            }

            $this->errors[] = [
                'line'           => $index + 2,
                'row'            => $row,
                'missing_fields' => $missingFields,
            ];
        }

        return $validRows;
    }

    /**
     * @return array<int, array{line: int, row: array<string, string>, missing_fields: array<int, string>}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
