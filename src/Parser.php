<?php

namespace Emanuel\PhpCsvToJson;

use Emanuel\PhpCsvToJson\Exception\FileNotReadableException;
use InvalidArgumentException;
use SplFileObject;

class Parser
{
    private SplFileObject $file;
    private string $separator;

    /** @var array<int, array{line: int, row: array<int, string>}> */
    private array $failedLines = [];

    public function __construct(string $filePath, string $separator = ',')
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new FileNotReadableException("File is not readable: {$filePath}");
        }

        $this->file = new SplFileObject($filePath);

        $this->file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );

        $this->separator = $separator;
        $this->file->setCsvControl($this->separator);
    }

    /**
     *
     * @return array<int, array<string, string>>
     */
    public function parse(): array
    {
        $this->file->rewind();
        $this->failedLines = [];

        if (!$this->file->valid()) {
            return [];
        }

        // ex: ['name', 'price', 'category', 'sku']
        $headers = $this->file->current();

        if (!is_array($headers) || empty($headers)) {
            return [];
        }

        $this->file->next();

        $data = [];

        while ($this->file->valid()) {
            // ex: ['Widget Pro', '29.90', 'electronics', 'WGT-001']
            $row = $this->file->current();


            if (!is_array($row) || count($row) !== count($headers)) {
                $this->failedLines[] = [
                    'line' => (int) $this->file->key() + 1,
                    'row'  => is_array($row) ? $row : [],
                ];
                $this->file->next();
                continue;
            }

            // ex: ['name' => 'Widget Pro', 'price' => '29.90', ...]
            $data[] = array_combine($headers, $row);

            $this->file->next();
        }

        return $data;
    }

    /**
     * @return array<int, array{line: int, row: array<int, string>}>
     */
    public function getFailedLines(): array
    {
        return $this->failedLines;
    }
}
