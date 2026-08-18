<?php

use Illuminate\Support\Facades\DB;

test('uses mariadb for the testing database connection', function () {
    expect(config('database.default'))->toBe('mariadb')
        ->and(config('database.connections.mariadb.database'))->toBe('pingkit_testing')
        ->and(DB::connection()->getDriverName())->toBe('mariadb');
});
