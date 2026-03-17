<?php

namespace Tests;

use Emanuel\PhpCsvToJson\Exporter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExporterTest extends TestCase
{
    private Exporter $exporter;

    protected function setUp(): void
    {
        $this->exporter = new Exporter();
    }

    #[Test]
    public function it_exports_rows_as_valid_json(): void
    {
        $rows = [['name' => 'Widget', 'price' => '29.90']];

        $json = $this->exporter->exportRows($rows);

        $this->assertJson($json);
        $this->assertSame($rows, json_decode($json, true));
    }

    #[Test]
    public function it_exports_empty_rows_as_empty_json_array(): void
    {
        $json = $this->exporter->exportRows([]);

        $this->assertSame('[]', $json);
    }

    #[Test]
    public function it_does_not_escape_unicode_characters(): void
    {
        $rows = [['name' => 'João', 'city' => 'São Paulo']];

        $json = $this->exporter->exportRows($rows);

        $this->assertStringContainsString('João', $json);
        $this->assertStringContainsString('São Paulo', $json);
    }

    #[Test]
    public function it_exports_errors_as_valid_json(): void
    {
        $errors = [[
            'line'           => 3,
            'row'            => ['name' => 'Widget', 'price' => ''],
            'missing_fields' => ['price'],
        ]];

        $json    = $this->exporter->exportErrors($errors);
        $decoded = json_decode($json, true);

        $this->assertJson($json);
        $this->assertSame(3, $decoded[0]['line']);
        $this->assertSame(['price'], $decoded[0]['missing_fields']);
    }

    #[Test]
    public function it_uses_pretty_print_format(): void
    {
        $rows = [['name' => 'Widget']];

        $json = $this->exporter->exportRows($rows);

        $this->assertStringContainsString("\n", $json);
        $this->assertStringContainsString('    ', $json);
    }
}
