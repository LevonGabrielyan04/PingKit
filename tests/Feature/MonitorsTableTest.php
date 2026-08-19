<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('monitors table has the expected columns', function () {
    expect(Schema::hasTable('monitors'))->toBeTrue();

    $columns = collect(Schema::getColumns('monitors'))->keyBy('name');

    expect($columns->keys()->all())->toBe([
        'id',
        'user_id',
        'url_address',
        'ip_address',
        'request_method',
        'request_headers',
        'created_at',
        'updated_at',
    ]);

    expect($columns['id']['type_name'])->toBe('uuid')
        ->and($columns['id']['nullable'])->toBeFalse()
        ->and($columns['user_id']['type_name'])->toBe('bigint')
        ->and($columns['user_id']['nullable'])->toBeFalse()
        ->and($columns['url_address']['type_name'])->toBe('varchar')
        ->and($columns['url_address']['nullable'])->toBeTrue()
        ->and($columns['ip_address']['type_name'])->toBe('varchar')
        ->and($columns['ip_address']['nullable'])->toBeTrue()
        ->and($columns['request_method']['type_name'])->toBe('tinyint')
        ->and($columns['request_method']['nullable'])->toBeFalse()
        ->and($columns['request_method']['type'])->toContain('unsigned')
        ->and($columns['request_headers']['nullable'])->toBeTrue();

    $primary = collect(Schema::getIndexes('monitors'))->firstWhere('primary', true);
    $foreign = collect(Schema::getForeignKeys('monitors'))->firstWhere('columns', ['user_id']);

    expect($primary['columns'])->toBe(['id'])
        ->and($foreign['foreign_table'])->toBe('users')
        ->and($foreign['foreign_columns'])->toBe(['id'])
        ->and($foreign['on_delete'])->toBe('cascade')
        ->and(Schema::hasIndex('monitors', ['updated_at']))->toBeTrue();
});

test('a monitor can be stored with a url address', function () {
    $id = insertMonitor([
        'url_address' => 'https://example.com',
        'ip_address' => null,
    ]);

    $monitor = DB::table('monitors')->where('id', $id)->sole();

    expect($monitor->url_address)->toBe('https://example.com')
        ->and($monitor->ip_address)->toBeNull();
});

test('a monitor can be stored with an ip address', function () {
    $id = insertMonitor([
        'url_address' => null,
        'ip_address' => '192.0.2.1',
    ]);

    $monitor = DB::table('monitors')->where('id', $id)->sole();

    expect($monitor->url_address)->toBeNull()
        ->and($monitor->ip_address)->toBe('192.0.2.1');
});

test('a monitor cannot be stored with both url and ip address', function () {
    insertMonitor([
        'url_address' => 'https://example.com',
        'ip_address' => '192.0.2.1',
    ]);
})->throws(QueryException::class);

test('a monitor cannot be stored without url or ip address', function () {
    insertMonitor([
        'url_address' => null,
        'ip_address' => null,
    ]);
})->throws(QueryException::class);

test('user_id must reference a user', function () {
    insertMonitor(['user_id' => 999_999]);
})->throws(QueryException::class);

test('request_headers can be stored as json', function () {
    $id = insertMonitor([
        'request_headers' => json_encode(['user-agent' => 'PingKit']),
    ]);

    $monitor = DB::table('monitors')->where('id', $id)->sole();

    expect(json_decode($monitor->request_headers, true))->toBe(['user-agent' => 'PingKit']);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function insertMonitor(array $overrides = []): string
{
    $id = $overrides['id'] ?? (string) Str::uuid();

    if (! array_key_exists('user_id', $overrides)) {
        $overrides['user_id'] = User::factory()->create()->id;
    }

    DB::table('monitors')->insert([
        'id' => $id,
        'url_address' => 'https://example.com',
        'ip_address' => null,
        'created_at' => now(),
        'updated_at' => now(),
        ...$overrides,
    ]);

    return $id;
}
