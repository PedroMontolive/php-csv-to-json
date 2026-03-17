# php-csv-to-json

A command-line tool that reads CSV files (products, clients, or any tabular data), validates required fields line by line, and exports clean formatted JSON — built with pure PHP, no frameworks.

The goal isn't just to convert formats. It's to do it **reliably**: invalid rows are collected and reported without stopping the whole process, errors go to `stderr` separately from the output, and the result is always valid, readable JSON.

---

## Requirements

- PHP 8.1+
- Composer

---

## Installation

```bash
git clone https://github.com/your-username/php-csv-to-json.git
cd php-csv-to-json
composer install
```

---

## Usage

```bash
php run.php samples/products.csv --required=name,price,sku
```

The valid rows are written to `stdout` as JSON. Validation errors go to `stderr`.

### Redirect output to a file

```bash
php run.php samples/products.csv --required=name,price > output.json
```

### Check only errors

```bash
php run.php samples/clients.csv --required=name,email 2> errors.json
```

---

## Expected CSV format

The first row must be the header. Column names will become the JSON keys.

```csv
name,price,category,sku
Widget Pro,29.90,electronics,WGT-001
Gadget Basic,,tools,
Super Device,49.90,electronics,SDV-003
```

### Output (`stdout`)

```json
[
    {
        "name": "Widget Pro",
        "price": "29.90",
        "category": "electronics",
        "sku": "WGT-001"
    },
    {
        "name": "Super Device",
        "price": "49.90",
        "category": "electronics",
        "sku": "SDV-003"
    }
]
```

### Errors (`stderr`)

```json
[
    {
        "line": 3,
        "row": { "name": "Gadget Basic", "price": "", "category": "tools", "sku": "" },
        "missing_fields": ["price", "sku"]
    }
]
```

---

## Running tests

```bash
composer test
```

Or directly with PHPUnit:

```bash
./vendor/bin/phpunit tests/
```

---

## Project structure

```
php-csv-to-json/
├── src/
│   ├── Exception/
│   │   ├── FileNotReadableException.php
│   │   └── JsonEncodingException.php
│   ├── Parser.php        # reads CSV and maps rows to associative arrays
│   ├── Validator.php     # checks required fields per row, collects errors
│   └── Exporter.php      # encodes valid rows and errors to formatted JSON
├── samples/
│   ├── products.csv      # example input with product data
│   └── clients.csv       # example input with client data
├── tests/
│   ├── ParserTest.php
│   ├── ValidatorTest.php
│   └── ExporterTest.php
├── run.php               # CLI entry point
├── phpunit.xml
└── composer.json
```

---

## Design decisions worth noting

**Errors don't stop the process.** If row 5 is invalid, rows 1–4 and 6–N are still exported. Invalid rows are collected and reported at the end via `stderr`. This makes the tool useful in real data pipelines where partial output is better than no output.

**`stdout` for data, `stderr` for errors.** This is standard Unix convention and means the tool composes well with other CLI tools — you can pipe the JSON output without worrying about error messages mixed in.

**Parser failures are reported separately.** If a row has a different number of columns than the header (e.g. a malformed line), it is skipped by the parser before validation even runs. A `Warning:` message is written to `stderr` with the affected line numbers so nothing is silently lost.

**No dependencies beyond PHPUnit.** The parsing, validation, and export logic uses only PHP's native SPL and built-in functions. Understanding the language before reaching for a library is the whole point.

---

## License

MIT