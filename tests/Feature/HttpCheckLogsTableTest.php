<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('http check logs table has the expected columns and generated is_successful', function () {
    expect(Schema::hasTable('http_check_logs'))->toBeTrue();

    $columns = collect(Schema::getColumns('http_check_logs'))->keyBy('name');

    expect($columns->keys()->all())->toBe([
        'id',
        'monitor_id',
        'created_at',
        'status_code',
        'is_successful',
        'response_time_ms',
        'dns_time_ms',
        'tcp_time_ms',
        'tls_time_ms',
        'error_message',
        'response_headers',
        'request_headers',
    ]);

    expect($columns['id']['type_name'])->toBe('uuid')
        ->and($columns['id']['nullable'])->toBeFalse()
        ->and($columns['monitor_id']['nullable'])->toBeFalse()
        ->and($columns['created_at']['nullable'])->toBeFalse()
        ->and($columns['status_code']['type_name'])->toBe('smallint')
        ->and($columns['status_code']['nullable'])->toBeFalse()
        ->and($columns['is_successful']['generation']['type'])->toBe('stored')
        ->and($columns['response_time_ms']['nullable'])->toBeFalse()
        ->and($columns['dns_time_ms']['nullable'])->toBeTrue()
        ->and($columns['tcp_time_ms']['nullable'])->toBeTrue()
        ->and($columns['tls_time_ms']['nullable'])->toBeTrue()
        ->and($columns['error_message']['type_name'])->toBe('text')
        ->and($columns['error_message']['nullable'])->toBeTrue()
        ->and($columns['response_headers']['nullable'])->toBeTrue()
        ->and($columns['request_headers']['nullable'])->toBeTrue();

    $primary = collect(Schema::getIndexes('http_check_logs'))->firstWhere('primary', true);
    $foreign = collect(Schema::getForeignKeys('http_check_logs'))->firstWhere('columns', ['monitor_id']);

    expect($primary['columns'])->toBe(['id'])
        ->and($foreign['foreign_table'])->toBe('monitors')
        ->and($foreign['foreign_columns'])->toBe(['id'])
        ->and($foreign['on_delete'])->toBe('cascade')
        ->and(Schema::hasIndex('http_check_logs', ['is_successful', 'created_at']))->toBeTrue()
        ->and(Schema::hasIndex('http_check_logs', ['monitor_id', 'created_at']))->toBeTrue()
        ->and(Schema::hasIndex('http_check_logs', ['monitor_id', 'status_code']))->toBeTrue()
        ->and(Schema::hasIndex('http_check_logs', ['monitor_id', 'is_successful']))->toBeTrue();
});

test('monitor_id must reference a monitor', function () {
    insertHttpCheckLog(['monitor_id' => 999_999]);
})->throws(QueryException::class);

test('is_successful is true for 2xx status codes and false otherwise', function (int $statusCode, bool $isSuccessful) {
    insertHttpCheckLog([
        'status_code' => $statusCode,
        'error_message' => $isSuccessful ? null : 'request failed',
    ]);

    $log = DB::table('http_check_logs')->sole();

    expect((bool) $log->is_successful)->toBe($isSuccessful);
})->with([
    'ok' => [200, true],
    'created' => [201, true],
    'last 2xx' => [299, true],
    'redirect' => [301, false],
    'not found' => [404, false],
    'continue' => [100, false],
]);

test('status_code must be between 100 and 999', function (int $statusCode) {
    insertHttpCheckLog([
        'status_code' => $statusCode,
        'error_message' => 'invalid',
    ]);
})->with([
    'too low' => [99],
    'too high' => [1000],
])->throws(QueryException::class);

test('error_message is rejected for 2xx status codes', function () {
    insertHttpCheckLog([
        'status_code' => 200,
        'error_message' => 'should not be stored',
    ]);
})->throws(QueryException::class);

test('error_message can be stored for non-2xx status codes', function () {
    insertHttpCheckLog([
        'status_code' => 500,
        'error_message' => 'connection timed out',
        'dns_time_ms' => 5,
        'tcp_time_ms' => 10,
        'tls_time_ms' => 15,
        'request_headers' => json_encode(['user-agent' => 'PingKit']),
    ]);

    $log = DB::table('http_check_logs')->sole();

    expect($log->error_message)->toBe('connection timed out')
        ->and($log->dns_time_ms)->toBe(5)
        ->and($log->tcp_time_ms)->toBe(10)
        ->and($log->tls_time_ms)->toBe(15);
});

test('error_message longer than 3000 characters is rejected', function () {
    insertHttpCheckLog([
        'status_code' => 500,
        'error_message' => str_repeat('a', 3001),
    ]);
})->throws(QueryException::class);

test('error_message at most 3000 characters can be stored', function () {
    $message = str_repeat('b', 3000);

    insertHttpCheckLog([
        'status_code' => 500,
        'error_message' => $message,
    ]);

    $log = DB::table('http_check_logs')->sole();

    expect(mb_strlen((string) $log->error_message, 'UTF-8'))->toBe(3000);
});

test('response_headers longer than 5000 characters are rejected', function () {
    insertHttpCheckLog([
        'response_headers' => json_encode(['x-big' => str_repeat('a', 5000)]),
    ]);
})->throws(QueryException::class);

test('response_headers at most 5000 characters can be stored', function () {
    $prefix = '{"a":"';
    $suffix = '"}';
    $headers = $prefix.str_repeat('b', 5000 - strlen($prefix) - strlen($suffix)).$suffix;
    expect(mb_strlen($headers, 'UTF-8'))->toBe(5000);

    insertHttpCheckLog([
        'response_headers' => $headers,
    ]);

    $log = DB::table('http_check_logs')->sole();

    expect(mb_strlen((string) $log->response_headers, 'UTF-8'))->toBe(5000);
});

test('null response_headers can be stored', function () {
    insertHttpCheckLog([
        'response_headers' => null,
    ]);

    $log = DB::table('http_check_logs')->sole();

    expect($log->response_headers)->toBeNull();
});

/**
 * @param  array<string, mixed>  $overrides
 */
function insertHttpCheckLog(array $overrides = []): void
{
    if (! array_key_exists('monitor_id', $overrides)) {
        $overrides['monitor_id'] = (string) Str::uuid();

        DB::table('monitors')->insert([
            'id' => $overrides['monitor_id'],
            'user_id' => User::factory()->create()->id,
            'url_address' => 'https://example.com',
            'ip_address' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    DB::table('http_check_logs')->insert([
        'id' => (string) Str::uuid(),
        'created_at' => now(),
        'status_code' => 200,
        'response_time_ms' => 42,
        'response_headers' => json_encode(['content-type' => 'text/html']),
        ...$overrides,
    ]);
}
