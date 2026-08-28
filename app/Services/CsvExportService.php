<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

class CsvExportService
{
    /**
     * Chunk an Eloquent query and write the results to a new CSV file.
     *
     * @param  list<string>|array<string, string>  $columns  Column names, or header labels keyed by attribute paths
     * @return string Absolute path to the generated CSV file
     */
    public function export(array $columns, Builder $query, int $chunkSize = 1000): string
    {
        if ($columns === []) {
            throw new InvalidArgumentException('At least one CSV column must be provided.');
        }

        if ($chunkSize < 1) {
            throw new InvalidArgumentException('Chunk size must be at least 1.');
        }

        /** @var array<string, string> $headerToAttribute */
        $headerToAttribute = $this->normalizeColumns($columns);
        $path = $this->createTemporaryPath();

        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open CSV file for writing at [{$path}].");
        }

        try {
            fputcsv($handle, array_keys($headerToAttribute), escape: '\\');

            $query->chunkById($chunkSize, function ($records) use ($handle, $headerToAttribute): void {
                foreach ($records as $record) {
                    fputcsv($handle, $this->rowValues($record, $headerToAttribute), escape: '\\');
                }
            });
        } finally {
            fclose($handle);
        }

        return $path;
    }

    /**
     * @param  list<string>|array<string, string>  $columns
     * @return array<string, string>
     */
    private function normalizeColumns(array $columns): array
    {
        if (array_is_list($columns)) {
            /** @var array<string, string> */
            return array_combine($columns, $columns);
        }

        return $columns;
    }

    /**
     * @param  array<string, string>  $headerToAttribute
     * @return list<string>
     */
    private function rowValues(Model $record, array $headerToAttribute): array
    {
        $row = [];

        foreach ($headerToAttribute as $attribute) {
            $row[] = $this->formatValue(data_get($record, $attribute));
        }

        return $row;
    }

    /**
     * @throws JsonException
     */
    private function formatValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    private function createTemporaryPath(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'csv_export_'.Str::uuid()->toString().'.csv';
    }
}
