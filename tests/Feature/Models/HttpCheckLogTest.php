<?php

use App\Models\HttpCheckLog;
use App\Models\Monitor;
use Illuminate\Support\Str;

test('an http check log generates a version 7 uuid primary key', function () {
    $log = HttpCheckLog::factory()->create();

    expect($log->id)->toBeUuid()
        ->and(Str::isUuid($log->id, version: 7))->toBeTrue();

    $this->assertModelExists($log);
});

test('an http check log belongs to a monitor', function () {
    $monitor = Monitor::factory()->create();
    $log = HttpCheckLog::factory()->for($monitor)->create();

    expect($log->monitor->is($monitor))->toBeTrue()
        ->and($monitor->httpCheckLogs)->toHaveCount(1)
        ->and($monitor->httpCheckLogs->first()->is($log))->toBeTrue();
});

test('is_successful is derived from the status code', function () {
    $successful = HttpCheckLog::factory()->create(['status_code' => 200])->refresh();
    $failed = HttpCheckLog::factory()->failed()->create()->refresh();

    expect($successful->is_successful)->toBeTrue()
        ->and($failed->is_successful)->toBeFalse()
        ->and($failed->error_message)->toBe('request failed');
});
