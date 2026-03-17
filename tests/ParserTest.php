<?php

namespace Tests;

use Emanuel\PhpCsvToJson\Parser;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    private function writeCsv(string $content): void
    {
        file_put_contents($this->tempFile, $content);
    }

    #[Test]
    public function it_parses_a_well_formed_csv(): void
    {
        $this->writeCsv("name,price\nWidget,29.90\nGadget,49.90\n");

        $rows = (new Parser($this->tempFile))->parse();

        $this->assertCount(2, $rows);
        $this->assertSame(['name' => 'Widget', 'price' => '29.90'], $rows[0]);
        $this->assertSame(['name' => 'Gadget', 'price' => '49.90'], $rows[1]);
    }

    #[Test]
    public function it_returns_empty_array_for_empty_file(): void
    {
        $this->writeCsv('');

        $rows = (new Parser($this->tempFile))->parse();

        $this->assertSame([], $rows);
    }

    #[Test]
    public function it_returns_empty_array_when_only_header_exists(): void
    {
        $this->writeCsv("name,price\n");

        $rows = (new Parser($this->tempFile))->parse();

        $this->assertSame([], $rows);
    }

    #[Test]
    public function it_throws_when_file_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Parser('/nonexistent/path/file.csv');
    }

    #[Test]
    public function it_collects_lines_with_wrong_column_count_as_failed(): void
    {
        $this->writeCsv("name,price,sku\nWidget,29.90,WGT-001\nbad line\nGadget,49.90,GDG-002\n");

        $parser = new Parser($this->tempFile);
        $rows   = $parser->parse();
        $failed = $parser->getFailedLines();

        $this->assertCount(2, $rows);
        $this->assertCount(1, $failed);
        $this->assertSame(3, $failed[0]['line']);
    }

    #[Test]
    public function it_returns_no_failed_lines_for_a_clean_csv(): void
    {
        $this->writeCsv("name,price\nWidget,29.90\n");

        $parser = new Parser($this->tempFile);
        $parser->parse();

        $this->assertSame([], $parser->getFailedLines());
    }

    #[Test]
    public function it_handles_special_characters_in_content(): void
    {
        $this->writeCsv("name,city\nJoão,São Paulo\nMüller,München\n");

        $rows = (new Parser($this->tempFile))->parse();

        $this->assertSame('João', $rows[0]['name']);
        $this->assertSame('São Paulo', $rows[0]['city']);
        $this->assertSame('Müller', $rows[1]['name']);
    }
}
