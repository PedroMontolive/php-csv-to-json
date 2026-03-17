<?php

require __DIR__ . '/vendor/autoload.php';

use Emanuel\PhpCsvToJson\Exporter;
use Emanuel\PhpCsvToJson\Parser;
use Emanuel\PhpCsvToJson\Validator;

if ($argc < 3) {
    fwrite(STDERR, "Usage: php run.php <file.csv> --required=field1,field2\n");
    exit(1);
}

$filePath = $argv[1];

if (str_starts_with($filePath, '--')) {
    fwrite(STDERR, "Error: first argument must be the CSV file path.\n");
    exit(1);
}

$requiredFields = [];
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--required=')) {
        $requiredFields = explode(',', substr($arg, strlen('--required=')));
        break;
    }
}

if (empty($requiredFields)) {
    fwrite(STDERR, "Error: --required=field1,field2 argument is missing.\n");
    exit(1);
}

// pipeline
try {
    $parser    = new Parser($filePath);
    $validator = new Validator($requiredFields);
    $exporter  = new Exporter();

    $rows      = $parser->parse();
    $validRows = $validator->validate($rows);
    $errors    = $validator->getErrors();

    $failedLines = $parser->getFailedLines();
    if (!empty($failedLines)) {
        $lineNumbers = implode(', ', array_column($failedLines, 'line'));
        fwrite(STDERR, "Warning: lines skipped due to column count mismatch: {$lineNumbers}\n");
    }

    // Output
    echo $exporter->exportRows($validRows) . "\n";

    if (!empty($errors)) {
        fwrite(STDERR, $exporter->exportErrors($errors) . "\n");
        exit(1);
    }
} catch (\InvalidArgumentException | \RuntimeException $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
