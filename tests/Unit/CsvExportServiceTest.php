<?php

use App\Models\User;
use App\Services\CsvExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

afterEach(function (): void {
    if (isset($this->exportedCsvPath) && is_string($this->exportedCsvPath) && is_file($this->exportedCsvPath)) {
        unlink($this->exportedCsvPath);
    }

    Mockery::close();
});

/**
 * @param  Collection<int, User>  $records
 */
function mockChunkedQuery(Collection $records, int $expectedChunkSize = 1000): Builder&MockInterface
{
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('chunkById')
        ->once()
        ->with($expectedChunkSize, Mockery::type('callable'))
        ->andReturnUsing(function (int $chunkSize, callable $callback) use ($records): bool {
            $callback($records);

            return true;
        });

    return $query;
}

test('it exports selected columns to a csv file using chunked queries', function (): void {
    $first = new User([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);
    $first->id = 1;

    $second = new User([
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
    ]);
    $second->id = 2;

    $records = Collection::make([$first, $second]);

    $service = new CsvExportService;

    $this->exportedCsvPath = $service->export(
        ['id', 'name', 'email'],
        mockChunkedQuery($records, expectedChunkSize: 1),
        chunkSize: 1,
    );

    expect($this->exportedCsvPath)->toBeFile()
        ->and(file_get_contents($this->exportedCsvPath))->toBe(
            implode("\n", [
                'id,name,email',
                '1,"Ada Lovelace",ada@example.com',
                '2,"Grace Hopper",grace@example.com',
                '',
            ]),
        );
});

test('it supports custom csv headers keyed by attribute paths', function (): void {
    $user = new User([
        'name' => 'Alan Turing',
        'email' => 'alan@example.com',
    ]);
    $user->id = 42;

    $records = Collection::make([$user]);

    $service = new CsvExportService;

    $this->exportedCsvPath = $service->export(
        [
            'User ID' => 'id',
            'Full Name' => 'name',
            'Email Address' => 'email',
        ],
        mockChunkedQuery($records),
    );

    expect(file_get_contents($this->exportedCsvPath))->toBe(
        implode("\n", [
            '"User ID","Full Name","Email Address"',
            '42,"Alan Turing",alan@example.com',
            '',
        ]),
    );
});

test('it rejects empty column definitions', function (): void {
    $service = new CsvExportService;

    expect(fn () => $service->export([], Mockery::mock(Builder::class)))
        ->toThrow(InvalidArgumentException::class, 'At least one CSV column must be provided.');
});

test('it rejects invalid chunk sizes', function (): void {
    $service = new CsvExportService;

    expect(fn () => $service->export(['id'], Mockery::mock(Builder::class), chunkSize: 0))
        ->toThrow(InvalidArgumentException::class, 'Chunk size must be at least 1.');
});
