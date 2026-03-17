<?php

namespace Emanuel\PhpCsvToJson;

use Emanuel\PhpCsvToJson\Exception\JsonEncodingException;

class Exporter
{
    private const JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    /**
     *
     * @param array<int, array<string, string>> $rows
     */
    public function exportRows(array $rows): string
    {
        return $this->encode($rows);
    }

    /**
     *
     * @param array<int, array{line: int, row: array<string, string>, missing_fields: array<int, string>}> $errors
     */
    public function exportErrors(array $errors): string
    {
        return $this->encode($errors);
    }

    /**
     *
     * @param array<mixed> $data
     */
    private function encode(array $data): string
    {
        $json = json_encode($data, self::JSON_FLAGS);

        if ($json === false) {
            throw new JsonEncodingException('JSON encoding failed: ' . json_last_error_msg());
        }

        return $json;
    }
}
